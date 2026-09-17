<?php

namespace Modules\Fields\Services\Harvests;

use Modules\Core\Services\PrimevueDatatables;
use Modules\Fields\Models\Harvest;

class ListHarvest
{
    public static function call($params = [])
    {
        $query = Harvest::with('details', 'details.quarter', 'details.quarter.field', 'farmer');

        $searchableColumns = ['year', 'batch', 'details.quarter.field.name', 'details.quarter.name', 'farmer.full_name'];

        $special_sort = false;
        $paramsCollection = collect($params);

        $perPage = $paramsCollection->get('rows', 10);
        $currentPage = $paramsCollection->get('page', 0) + 1;

        $filters = collect($paramsCollection->get('filters', []));
        $sort = $paramsCollection->get('sortField');
        $sortDirection = $paramsCollection->get('sortOrder') == 1 ? 'asc' : 'desc';
        if ($sort === 'total_weight' || $sort === 'unit_count') {
            $params['sortField'] = '';
            $params['sortOrder'] = '';
            $special_sort = true;
        }

        $datatable = new PrimevueDatatables($params, $searchableColumns);
        $harvestsQuery = $datatable->of($query)->make(true);
        $harvestsTotal = $harvestsQuery->clone()->reorder()->get();

        $quarterFilter = collect($filters->get('details.quarter_id', []));
        $quarterId = $quarterFilter->get('value');

        $detailsTotal = [];
        $details_count = 0;
        $details_sum_weight = 0;
        foreach ($harvestsTotal as $harvest) {
            $detailsTotal = $harvest->details;
            if ($quarterId) {
                $detailsTotal = $harvest->details->filter(function ($detail) use ($quarterId) {
                    return $detail->quarter_id === $quarterId;
                });
            }

            $details_count += $detailsTotal->count();
            $details_sum_weight += $harvest->weight;
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

            $field_names = $details->map(fn ($detail) => $detail->quarter?->field?->name)->unique()->join(', ');
            $quarter_names = $details->map(fn ($detail) => $detail->quarter?->name)->unique()->join(', ');

            if ($quarter_names == '') {
                if ($harvest->quarters->count() > 0) {
                    $quarter_names = $harvest->quarters->map(fn ($quarter) => $quarter->name)->unique()->join(', ');
                    $field_names = $harvest->quarters->map(fn ($quarter) => $quarter->field?->name)->unique()->join(', ');
                }
            }

            return [
                'id' => $harvest->id,
                'date' => $harvest->date,
                'year' => $harvest->year,
                'week' => $harvest->week,
                'batch' => $harvest->batch,
                'field_names' => $field_names,
                'quarter_names' => $quarter_names,
                'total_weight' => $harvest->weight,
                'unit_count' => $details->count(),
                'farmer_name' => optional($harvest->farmer)->name,
            ];
        });

        $harvestsArray = $harvests->toArray();

        if ($special_sort) {
            $data = collect($harvestsArray['data']);
            if ($sortDirection === 'asc') {
                $data = $data->sortBy($sort);
            } else {
                $data = $data->sortByDesc($sort);
            }
            $harvestsArray['data'] = $data->values();
        }

        $harvestsArray['details_count'] = $details_count;
        $harvestsArray['details_sum_weight'] = $details_sum_weight;

        return $harvestsArray;
    }

    public static function collection(array $params = []): array
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
}
