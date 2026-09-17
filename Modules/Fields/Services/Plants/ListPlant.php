<?php

namespace Modules\Fields\Services\Plants;

use Modules\Core\Services\PrimevueDatatables;
use Modules\Fields\Models\Plant;

class ListPlant
{
    public static function call($params = [])
    {
        $searchableColumns = ['plants.code', 'quarter.name', 'quarter.field.name', 'plant_type.name', 'plants.age', 'quarter.responsible.full_name'];

        $query = Plant::query();

        $datatable = new PrimevueDatatables($params, $searchableColumns);
        $plants = $datatable->of($query)->make();

        return $plants;
    }

    public static function collection(array $params = []): array
    {
        $query = Plant::query()
            ->select('plants.id', 'plants.code', 'plants.quarter_id', 'plants.plant_type_id', 'plants.age', 'plants.planned_at', 'plants.row')
            ->with([
                'plant_type:id,name',
                'quarter:id,name,field_id,responsible_id',
                'quarter.field:id,name',
                'quarter.responsible:id,full_name',
            ]);
        $search = trim($params['q'] ?? '');

        if ($search !== '') {
            $query->where(function ($builder) use ($search) {
                $builder
                    ->where('plants.code', 'like', "%{$search}%")
                    ->orWhereHas('quarter', function ($quarterQuery) use ($search) {
                        $quarterQuery
                            ->where('name', 'like', "%{$search}%")
                            ->orWhereHas('field', function ($fieldQuery) use ($search) {
                                $fieldQuery->where('name', 'like', "%{$search}%");
                            });
                    });
            });
        }

        if (! empty($params['field_id'])) {
            $query->whereHas('quarter', function ($quarterQuery) use ($params) {
                $quarterQuery->where('field_id', $params['field_id']);
            });
        }

        if (! empty($params['quarter_id'])) {
            $query->where('plants.quarter_id', $params['quarter_id']);
        }

        if (! empty($params['plant_type_id'])) {
            $query->where('plants.plant_type_id', $params['plant_type_id']);
        }

        if (! empty($params['responsible_id'])) {
            $query->whereHas('quarter', function ($quarterQuery) use ($params) {
                $quarterQuery->where('responsible_id', $params['responsible_id']);
            });
        }

        $summaryQuery = clone $query;
        $summary = [
            'plants' => (clone $summaryQuery)->count(),
            'quarters' => (clone $summaryQuery)->distinct('plants.quarter_id')->count('plants.quarter_id'),
            'fields' => (clone $summaryQuery)
                ->join('quarters as summary_quarters', 'plants.quarter_id', '=', 'summary_quarters.id')
                ->distinct('summary_quarters.field_id')
                ->count('summary_quarters.field_id'),
            'types' => (clone $summaryQuery)->distinct('plants.plant_type_id')->count('plants.plant_type_id'),
        ];

        $sort = in_array($params['sort'] ?? '', ['code', 'age', 'planned_at'], true)
            ? $params['sort']
            : 'code';
        $direction = ($params['direction'] ?? 'asc') === 'desc' ? 'desc' : 'asc';
        $perPage = min(max((int) ($params['per_page'] ?? 50), 1), 100);
        $page = max((int) ($params['page'] ?? 1), 1);

        $paginator = $query
            ->orderBy("plants.{$sort}", $direction)
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
}
