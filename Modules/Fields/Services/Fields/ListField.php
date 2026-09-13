<?php

namespace Modules\Fields\Services\Fields;

use Modules\Core\Services\PrimevueDatatables;
use Modules\Fields\Models\Field;
use Modules\Fields\Models\Plant;
use Modules\Fields\Models\Quarter;

class ListField
{
    public static function call($params = [])
    {
        $query = Field::select('id', 'name', 'location', 'size')->withCount('plants');
        $searchableColumns = ['name', 'location', 'size'];
        $datatable = new PrimevueDatatables($params, $searchableColumns);
        $fields = $datatable->of($query)->make();

        return $fields;
    }

    public static function collection(array $params = []): array
    {
        $query = Field::query()->select('fields.id', 'fields.name', 'fields.location', 'fields.size');
        $search = trim($params['q'] ?? '');

        if ($search !== '') {
            $query->where(function ($builder) use ($search) {
                $builder
                    ->where('fields.name', 'like', "%{$search}%")
                    ->orWhere('fields.location', 'like', "%{$search}%");
            });
        }

        $fieldIds = (clone $query)->select('fields.id');
        $quarterIds = Quarter::query()->whereIn('field_id', $fieldIds)->select('id');
        $summary = [
            'fields' => (clone $query)->count(),
            'area' => (float) ((clone $query)->sum('fields.size') ?? 0),
            'quarters' => Quarter::query()->whereIn('field_id', $fieldIds)->count(),
            'plants' => Plant::query()->whereIn('quarter_id', $quarterIds)->count(),
        ];

        $sort = in_array($params['sort'] ?? '', ['name', 'size', 'plants_count', 'quarters_count'], true)
            ? $params['sort']
            : 'name';
        $direction = ($params['direction'] ?? 'asc') === 'desc' ? 'desc' : 'asc';
        $perPage = min(max((int) ($params['per_page'] ?? 12), 1), 24);
        $page = max((int) ($params['page'] ?? 1), 1);

        $paginator = $query
            ->withCount(['plants', 'quarters'])
            ->orderBy($sort, $direction)
            ->paginate($perPage, ['*'], 'page', $page);

        return self::formatPaginator($paginator, $summary);
    }

    private static function formatPaginator($paginator, array $summary): array
    {
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
