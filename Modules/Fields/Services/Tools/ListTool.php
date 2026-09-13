<?php

namespace Modules\Fields\Services\Tools;

use Modules\Core\Services\PrimevueDatatables;
use Modules\Fields\Models\Tool;

class ListTool
{
    public static function call($params = [])
    {
        $searchableColumns = ['name', 'purchase_date', 'last_maintenance', 'purchase_location', 'contact'];

        $query = Tool::query();

        $datatable = new PrimevueDatatables($params, $searchableColumns);
        $tools = $datatable->of($query)->make();

        return $tools;
    }

    public static function collection(array $params = []): array
    {
        $query = Tool::query()->select('tools.id', 'tools.name', 'tools.purchase_date', 'tools.last_maintenance', 'tools.purchase_location', 'tools.contact');

        $search = trim($params['q'] ?? '');

        if ($search !== '') {
            $query->where(function ($builder) use ($search) {
                $builder
                    ->where('tools.name', 'like', "%{$search}%")
                    ->orWhere('tools.purchase_location', 'like', "%{$search}%")
                    ->orWhere('tools.contact', 'like', "%{$search}%");
            });
        }

        $summary = [
            'tools' => (clone $query)->count(),
            'with_maintenance' => (clone $query)->whereNotNull('tools.last_maintenance')->count(),
        ];

        $sort = in_array($params['sort'] ?? '', ['name', 'purchase_date', 'last_maintenance', 'purchase_location', 'contact'], true)
            ? $params['sort']
            : 'name';
        $direction = ($params['direction'] ?? 'asc') === 'desc' ? 'desc' : 'asc';
        $perPage = min(max((int) ($params['per_page'] ?? 12), 1), 24);
        $page = max((int) ($params['page'] ?? 1), 1);

        $paginator = $query
            ->orderBy("tools.{$sort}", $direction)
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
