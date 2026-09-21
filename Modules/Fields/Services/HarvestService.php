<?php

namespace Modules\Fields\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\Core\Services\PrimevueDatatables;
use Modules\Fields\Models\Harvest;
use Modules\Fields\Models\HarvestDetail;

class HarvestService
{
    private const SEARCHABLE_COLUMNS = ['year', 'batch', 'details.quarter.field.name', 'details.quarter.name', 'farmer.full_name'];

    /** Cache TTL for the available-years / available-weeks queries. */
    private const AVAILABLE_CACHE_TTL = 60;

    /** Cache TTL for the last-year computation (changes only on a new harvest). */
    private const LAST_YEAR_CACHE_TTL = 60 * 60 * 24;

    public function __construct(private readonly PlantService $plants) {}

    public function find(string|int $id): Harvest
    {
        return Harvest::findOrFail($id);
    }

    public function create(array $data): Harvest
    {
        $harvest = new Harvest;

        $harvest->date = $data['date'];
        $harvest->batch = strtoupper($data['batch']);
        $harvest->dog_id = $data['dog_id']['value'];
        $harvest->farmer_id = $data['farmer_id']['value'];
        $harvest->assistant_id = $data['assistant_id']['value'];
        $harvest->weight = $data['weight'];
        $harvest->note = $data['note'];
        $harvest->save();

        $quarterIds = collect($data['quarter_ids'] ?? [])->map(fn ($option) => $option['value'])->all();
        $harvest->quarters()->attach($quarterIds);

        return $harvest;
    }

    public function update(string|int $id, array $data): Harvest
    {
        $harvest = Harvest::findOrFail($id);

        $harvest->date = $data['date'];
        $harvest->batch = strtoupper($data['batch']);
        $harvest->dog_id = $data['dog_id']['value'];
        $harvest->farmer_id = $data['farmer_id']['value'];
        $harvest->assistant_id = $data['assistant_id']['value'];
        $harvest->weight = $data['weight'];
        $harvest->note = $data['note'];
        $harvest->save();

        $quarterIds = collect($data['quarter_ids'] ?? [])->map(fn ($option) => $option['value'])->all();
        $harvest->quarters()->sync($quarterIds);

        $this->syncDetails($harvest, $data['details'] ?? []);

        return $harvest;
    }

    public function delete(string|int $id): void
    {
        Harvest::destroy($id);
    }

    /**
     * Idempotent sync of the harvest's details. Any existing detail
     * not present in the incoming payload is destroyed; details with
     * matching ids are updated, others are created. Plants are
     * resolved by code through PlantService (the cross-module
     * collaborator injected above). Details whose plant_code does
     * not resolve to a real plant are silently skipped, matching
     * the original UpdateHarvest behaviour.
     */
    private function syncDetails(Harvest $harvest, array $details): void
    {
        $details = collect($details);

        $existingIds = $harvest->details()->pluck('id');
        $idsToDestroy = $existingIds->filter(fn ($id) => ! $details->firstWhere('id', $id))->toArray();

        HarvestDetail::destroy($idsToDestroy);

        foreach ($details as $detail) {
            $plant = $this->plants->findByCode($detail['plant_code']);
            if (! $plant) {
                continue;
            }

            $harvestDetail = ! empty($detail['id'])
                ? HarvestDetail::find($detail['id']) ?? new HarvestDetail
                : new HarvestDetail;

            $harvestDetail->harvest_id = $harvest->id;
            $harvestDetail->plant_id = $plant->id;
            $harvestDetail->quality = isset($detail['quality']['value'])
                ? Str::slug($detail['quality']['value'])
                : '';
            $harvestDetail->weight = $detail['weight'];
            $harvestDetail->save();
        }
    }

    /**
     * Available-years / -weeks caches (1 minute TTL) and the
     * last-year computation (1 day TTL). The three originals lived
     * in their own static classes with three cache keys; here they
     * share the service so all three can be invalidated in one
     * place if we ever need to add a write hook.
     */
    public function availableYears(): array
    {
        return cache()->remember('harvest_available_years', self::AVAILABLE_CACHE_TTL, function () {
            return DB::table('harvests')
                ->select('year')
                ->distinct()
                ->get()
                ->map(fn ($row) => ['value' => $row->year, 'text' => $row->year])
                ->all();
        });
    }

    public function availableWeeks(): array
    {
        return cache()->remember('harvest_available_weeks', self::AVAILABLE_CACHE_TTL, function () {
            return DB::table('harvests')
                ->select('week')
                ->distinct()
                ->get()
                ->map(fn ($row) => ['value' => $row->week, 'text' => 'Semana '.$row->week])
                ->all();
        });
    }

    public function lastYear(): int
    {
        return cache()->remember('last_years_harvest', self::LAST_YEAR_CACHE_TTL, function () {
            $currentYear = (int) date('Y');
            $year = Harvest::max('year');

            // No harvests yet (e.g. fresh DB). Fall back to the current year
            // so consumers get a sensible default instead of a TypeError.
            if ($year === null) {
                return $currentYear;
            }

            return $year > $currentYear ? $currentYear : $year;
        });
    }

    public function list(array $params = []): mixed
    {
        $query = Harvest::with('details', 'details.quarter', 'details.quarter.field', 'farmer');

        $specialSort = false;
        $paramsCollection = collect($params);

        $perPage = $paramsCollection->get('rows', 10);
        $currentPage = $paramsCollection->get('page', 0) + 1;

        $filters = collect($paramsCollection->get('filters', []));
        $sort = $paramsCollection->get('sortField');
        $sortDirection = $paramsCollection->get('sortOrder') == 1 ? 'asc' : 'desc';
        if ($sort === 'total_weight' || $sort === 'unit_count') {
            $params['sortField'] = '';
            $params['sortOrder'] = '';
            $specialSort = true;
        }

        $datatable = new PrimevueDatatables($params, self::SEARCHABLE_COLUMNS);
        $harvestsQuery = $datatable->of($query)->make(true);
        $harvestsTotal = $harvestsQuery->clone()->reorder()->get();

        $quarterFilter = collect($filters->get('details.quarter_id', []));
        $quarterId = $quarterFilter->get('value');

        $detailsCount = 0;
        $detailsSumWeight = 0;
        foreach ($harvestsTotal as $harvest) {
            $detailsTotal = $harvest->details;
            if ($quarterId) {
                $detailsTotal = $harvest->details->filter(fn ($detail) => $detail->quarter_id === $quarterId);
            }

            $detailsCount += $detailsTotal->count();
            $detailsSumWeight += $harvest->weight;
        }

        $harvests = $harvestsQuery->paginate($perPage, page: $currentPage);
        $harvestsCollection = $harvests->getCollection();

        $harvestsCollection->transform(function ($harvest) use ($filters) {
            $quarterFilter = collect($filters->get('details.quarter_id', []));
            $quarterId = $quarterFilter->get('value');

            $details = $harvest->details;
            if ($quarterId) {
                $details = $details->where('quarter_id', $quarterId);
            }

            $fieldNames = $details->map(fn ($detail) => $detail->quarter?->field?->name)->unique()->join(', ');
            $quarterNames = $details->map(fn ($detail) => $detail->quarter?->name)->unique()->join(', ');

            if ($quarterNames == '') {
                if ($harvest->quarters->count() > 0) {
                    $quarterNames = $harvest->quarters->map(fn ($quarter) => $quarter->name)->unique()->join(', ');
                    $fieldNames = $harvest->quarters->map(fn ($quarter) => $quarter->field?->name)->unique()->join(', ');
                }
            }

            return [
                'id' => $harvest->id,
                'date' => $harvest->date,
                'year' => $harvest->year,
                'week' => $harvest->week,
                'batch' => $harvest->batch,
                'field_names' => $fieldNames,
                'quarter_names' => $quarterNames,
                'total_weight' => $harvest->weight,
                'unit_count' => $details->count(),
                'farmer_name' => optional($harvest->farmer)->name,
            ];
        });

        $harvestsArray = $harvests->toArray();

        if ($specialSort) {
            $data = collect($harvestsArray['data']);
            if ($sortDirection === 'asc') {
                $data = $data->sortBy($sort);
            } else {
                $data = $data->sortByDesc($sort);
            }
            $harvestsArray['data'] = $data->values();
        }

        $harvestsArray['details_count'] = $detailsCount;
        $harvestsArray['details_sum_weight'] = $detailsSumWeight;

        return $harvestsArray;
    }

    public function collection(array $params = []): array
    {
        $query = Harvest::query()
            ->select('harvests.id', 'harvests.date', 'harvests.year', 'harvests.week', 'harvests.batch', 'harvests.weight', 'harvests.farmer_id')
            ->with(['farmer:id,name,full_name', 'details:id,harvest_id,quarter_id,weight', 'details.quarter:id,name,field_id', 'details.quarter.field:id,name']);

        $search = trim($params['q'] ?? '');

        if ($search !== '') {
            $query->where(function ($builder) use ($search) {
                $builder
                    ->where('harvests.batch', 'like', "%{$search}%")
                    ->orWhere('harvests.year', 'like', "%{$search}%")
                    ->orWhere('harvests.week', 'like', "%{$search}%")
                    ->orWhereHas('farmer', function ($farmerQuery) use ($search) {
                        $farmerQuery->where('full_name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('details.quarter.field', function ($fieldQuery) use ($search) {
                        $fieldQuery->where('name', 'like', "%{$search}%");
                    });
            });
        }

        if (! empty($params['year'])) {
            $query->where('harvests.year', $params['year']);
        }

        if (! empty($params['week'])) {
            $query->where('harvests.week', $params['week']);
        }

        if (! empty($params['field_id'])) {
            $query->whereHas('details.quarter', function ($quarterQuery) use ($params) {
                $quarterQuery->where('field_id', $params['field_id']);
            });
        }

        if (! empty($params['quarter_id'])) {
            $query->whereHas('details', function ($detailQuery) use ($params) {
                $detailQuery->where('quarter_id', $params['quarter_id']);
            });
        }

        if (! empty($params['farmer_id'])) {
            $query->where('harvests.farmer_id', $params['farmer_id']);
        }

        $summary = [
            'harvests' => (clone $query)->count(),
            'total_weight' => (clone $query)->sum('harvests.weight'),
        ];

        $sort = in_array($params['sort'] ?? '', ['year', 'week', 'batch', 'date', 'weight', 'updated_at'], true)
            ? $params['sort']
            : 'date';
        $direction = ($params['direction'] ?? 'desc') === 'asc' ? 'asc' : 'desc';
        $perPage = min(max((int) ($params['per_page'] ?? 12), 1), 24);
        $page = max((int) ($params['page'] ?? 1), 1);

        $paginator = $query
            ->orderBy("harvests.{$sort}", $direction)
            ->paginate($perPage, ['*'], 'page', $page);

        $items = collect($paginator->items())->map(function ($harvest) {
            $fieldNames = $harvest->details->map(fn ($detail) => $detail->quarter?->field?->name)->filter()->unique()->values()->all();
            $quarterNames = $harvest->details->map(fn ($detail) => $detail->quarter?->name)->filter()->unique()->values()->all();

            return [
                'id' => $harvest->id,
                'date' => $harvest->date,
                'year' => $harvest->year,
                'week' => $harvest->week,
                'batch' => $harvest->batch,
                'field_names' => $fieldNames,
                'quarter_names' => $quarterNames,
                'total_weight' => (float) $harvest->weight,
                'unit_count' => $harvest->details->count(),
                'farmer_name' => $harvest->farmer?->name,
            ];
        })->all();

        return [
            'items' => $items,
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'from' => $paginator->firstItem() ?? 0,
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'to' => $paginator->lastItem() ?? 0,
                'total' => $paginator->total(),
            ],
            'summary' => $summary,
        ];
    }

    /**
     * Flat list for the bulk-load harvest selector. Same shape as
     * the static EntityRegistry::register('harvest', ...) had:
     * the displayed text is the i18n-rendered "week N · batch X"
     * so the picker can show the human label without further work.
     */
    public function forSelect(): array
    {
        return Harvest::select('id', 'week', 'batch', 'year')
            ->orderBy('date')
            ->get()
            ->map(fn ($harvest) => [
                'value' => $harvest->id,
                'year' => $harvest->year,
                'text' => __('harvest.form.batch.renderText', ['week' => $harvest->week, 'batch' => $harvest->batch]),
            ])
            ->all();
    }

    /**
     * Year-grouped harvest multiselect payload. Replaces the static
     * EntityRegistry::register('harvest_multiselect', ...) closure
     * that the SelectsController HTTP endpoint used to call lazily.
     */
    public function harvestMultiselect(): array
    {
        return Harvest::select('year')
            ->distinct()
            ->orderBy('year', 'desc')
            ->get()
            ->map(function ($h) {
                return [
                    'items' => Harvest::select('id', 'week', 'batch')
                        ->where('year', $h->year)
                        ->orderBy('date', 'desc')
                        ->get()
                        ->map(fn ($harvest) => [
                            'value' => $harvest->id,
                            'label' => __('harvest.form.batch.renderText', ['week' => $harvest->week, 'batch' => $harvest->batch]),
                        ])->values(),
                    'label' => $h->year,
                ];
            })
            ->values()
            ->all();
    }
}
