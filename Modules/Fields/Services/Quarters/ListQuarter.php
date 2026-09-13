<?php

namespace Modules\Fields\Services\Quarters;

use Modules\Core\Services\PrimevueDatatables;
use Modules\Fields\Models\Plant;
use Modules\Fields\Models\Quarter;

class ListQuarter
{
    public static function call($params = [])
    {
        $query = Quarter::leftJoin('fields', 'quarters.field_id', '=', 'fields.id')
            ->select('quarters.id', 'quarters.name', 'fields.name as field_name', 'quarters.area')
            ->withCount('plants');
        $searchableColumns = ['quarters.name', 'field.name', 'quarters.area'];
        $datatable = new PrimevueDatatables($params, $searchableColumns);
        $quarters = $datatable->of($query)->make();

        return $quarters;
    }

    public static function collection(array $params = []): array
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

        if (!empty($params['field_id'])) {
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
}
