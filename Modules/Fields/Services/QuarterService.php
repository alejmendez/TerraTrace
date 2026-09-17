<?php

namespace Modules\Fields\Services;

use Illuminate\Support\Facades\DB;
use Modules\Core\Services\PrimevueDatatables;
use Modules\Fields\Models\Harvest;
use Modules\Fields\Models\HarvestDetail;
use Modules\Fields\Models\Plant;
use Modules\Fields\Models\Quarter;

class QuarterService
{
    private const SEARCHABLE_COLUMNS = ['quarters.name', 'field.name', 'quarters.area'];

    public function find(string|int $id): Quarter
    {
        return Quarter::with('responsible', 'field')
            ->withCount('plants')
            ->findOrFail($id);
    }

    public function create(array $data): Quarter
    {
        $quarter = new Quarter;

        $quarter->name = $data['name'];
        $quarter->area = $data['area'];
        $quarter->field_id = $data['field_id']['value'];
        $quarter->responsible_id = $data['responsible_id']['value'];
        $quarter->blueprint = $data['blueprint'];
        $quarter->save();

        return $quarter;
    }

    public function update(string|int $id, array $data): Quarter
    {
        $quarter = Quarter::findOrFail($id);

        $quarter->name = $data['name'];
        $quarter->area = $data['area'];
        $quarter->field_id = $data['field_id']['value'];
        $quarter->responsible_id = $data['responsible_id']['value'];

        if (! empty($data['blueprint'])) {
            $quarter->blueprint = $data['blueprint'];
        }

        if (($data['blueprintRemove'] ?? null) === '1') {
            $quarter->blueprint = null;
        }

        $quarter->save();

        return $quarter;
    }

    public function delete(string|int $id): void
    {
        Quarter::destroy($id);
    }

    public function list(array $params = []): mixed
    {
        $query = Quarter::leftJoin('fields', 'quarters.field_id', '=', 'fields.id')
            ->select('quarters.id', 'quarters.name', 'fields.name as field_name', 'quarters.area')
            ->withCount('plants');

        $datatable = new PrimevueDatatables($params, self::SEARCHABLE_COLUMNS);

        return $datatable->of($query)->make();
    }

    public function collection(array $params = []): array
    {
        $query = Quarter::query()
            ->select('quarters.id', 'quarters.name', 'quarters.field_id', 'quarters.area')
            ->with(['field:id,name'])
            ->withCount('plants');

        $search = trim($params['q'] ?? '');

        if ($search !== '') {
            $query->where(function ($builder) use ($search) {
                $builder
                    ->where('quarters.name', 'like', "%{$search}%")
                    ->orWhereHas('field', function ($fieldQuery) use ($search) {
                        $fieldQuery->where('name', 'like', "%{$search}%");
                    });
            });
        }

        if (! empty($params['field_id'])) {
            $query->where('quarters.field_id', $params['field_id']);
        }

        $summaryQuery = clone $query;
        $quarterIds = (clone $summaryQuery)->select('quarters.id');
        $summary = [
            'quarters' => (clone $summaryQuery)->count(),
            'fields' => (clone $summaryQuery)->distinct('quarters.field_id')->count('quarters.field_id'),
            'area' => (float) ((clone $summaryQuery)->sum('quarters.area') ?? 0),
            'plants' => Plant::query()->whereIn('quarter_id', $quarterIds)->count(),
        ];

        $sort = in_array($params['sort'] ?? '', ['name', 'area', 'plants_count'], true)
            ? $params['sort']
            : 'name';
        $direction = ($params['direction'] ?? 'asc') === 'desc' ? 'desc' : 'asc';
        $perPage = min(max((int) ($params['per_page'] ?? 12), 1), 24);
        $page = max((int) ($params['page'] ?? 1), 1);

        $paginator = $query
            ->orderBy($sort, $direction)
            ->paginate($perPage, ['*'], 'page', $page);

        return [
            'items' => $paginator->items(),
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
     * Compose the per-quarter plant dataset used by the Show view's
     * tree map: harvests for the quarter, plants grouped by id, and
     * per-plant scaleByWeight / scaleByQuantity ratios so the UI can
     * size the cell rectangles.
     *
     * Originally lived in ListQuarterPlants::call. Kept as a separate
     * method (rather than absorbed into collection) because the
     * payload shape is bespoke for that single screen.
     */
    public function plantsWithHarvests(string $id): array
    {
        $harvests = Harvest::whereHas('quarters', function ($q) use ($id) {
            $q->where('quarters.id', $id);
        })
            ->get()
            ->map(fn ($a) => $a->only(['id', 'year', 'week', 'date', 'batch']));

        $harvestDetails = HarvestDetail::with('harvest')->where('quarter_id', $id)->get();

        $maxWeight = (float) HarvestDetail::where('quarter_id', $id)
            ->groupBy('plant_id')
            ->select(DB::raw('sum(weight) as weight'))
            ->get()
            ->max('weight');

        $maxQuantityByPlant = (float) HarvestDetail::where('quarter_id', $id)
            ->groupBy('plant_id')
            ->select(DB::raw('count(*) as count'))
            ->get()
            ->max('count');

        $harvestDetailsByPlantId = $harvestDetails->groupBy('plant_id');

        $plants = Plant::select('id', 'code', 'row', 'position')
            ->where('quarter_id', $id)
            ->orderBy('id')
            ->get()
            ->map(function ($plant) use ($harvestDetailsByPlantId, $maxWeight, $maxQuantityByPlant) {
                $plant->scaleByWeight = 0;
                $plant->scaleByQuantity = 0;

                $detail = $harvestDetailsByPlantId->get($plant->id);
                $plant->data = $detail;

                if ($detail) {
                    $quantityPlant = $detail->count();
                    $plant->scaleByWeight = round($detail->sum('weight') * 100 / $maxWeight, 2);
                    $plant->scaleByQuantity = round($quantityPlant * 100 / $maxQuantityByPlant, 2);

                    $plant->data = $detail->map(fn ($a) => $a->only(['id', 'harvest_id', 'quality', 'weight']))
                        ->map(function ($a) use ($maxWeight, $maxQuantityByPlant, $quantityPlant) {
                            $a['weight'] = floatval($a['weight']);
                            $a['scaleByWeight'] = round($a['weight'] * 100 / $maxWeight, 2);
                            $a['scaleByQuantity'] = round($quantityPlant * 100 / $maxQuantityByPlant, 2);

                            return $a;
                        })
                        ->groupBy('harvest_id');
                }

                return $plant;
            });

        return [
            'harvests' => $harvests,
            'plants' => $plants,
        ];
    }

    /**
     * Bulk update plant positions inside a quarter. Each entry is a
     * [code, x, y] tuple; the chunked transaction keeps the working
     * set under 500 rows per commit so very large quarters don't
     * blow the connection's max_allowed_packet on MySQL or lock the
     * quarters table for too long.
     */
    public function updatePlantPositions(string $quarterId, array $data): void
    {
        $batchSize = 500;

        collect($data)->chunk($batchSize)->each(function ($chunk) use ($quarterId) {
            DB::transaction(function () use ($chunk, $quarterId) {
                foreach ($chunk as $plantData) {
                    [$code, $x, $y] = $plantData;
                    Plant::where('code', $code)
                        ->where('quarter_id', $quarterId)
                        ->update(['position' => "$x,$y"]);
                }
            });
        });
    }

    /**
     * Flat {value, text} list for cross-module consumers. Used by
     * Tasks, Harvests, etc. via constructor injection instead of the
     * EntityRegistry dispatcher.
     */
    public function forSelect(): array
    {
        return Quarter::select('id as value', 'name as text')
            ->orderBy('name')
            ->get()
            ->toArray();
    }

    /**
     * Filtered variant for the Tasks create/edit form: list quarters
     * belonging to the given field so the user only sees relevant
     * options. Mirrors what EntityRegistry::call('quarter',
     * ['field_id' => X]) used to do lazily.
     */
    public function byField(int $fieldId): array
    {
        return Quarter::select('id as value', 'name as text')
            ->where('field_id', $fieldId)
            ->orderBy('name')
            ->get()
            ->toArray();
    }
}
