<?php

namespace Modules\Fields\Services\Machineries;

use Modules\Core\Services\PrimevueDatatables;
use Modules\Fields\Models\Machinery;

class ListMachinery
{
    public static function call($params = [])
    {
        $searchableColumns = ['name', 'purchase_date', 'last_maintenance', 'purchase_location', 'contact'];

        $query = Machinery::query();

        $datatable = new PrimevueDatatables($params, $searchableColumns);
        $items = $datatable->of($query)->make();

        return $items;
    }

    public static function collection(array $params = []): array
    {
        $query = Machinery::query()->select('machineries.id', 'machineries.name', 'machineries.purchase_date', 'machineries.last_maintenance', 'machineries.purchase_location', 'machineries.contact');

        $search = trim($params['q'] ?? '');

        if ($search !== '') {
            $query->where(function ($builder) use ($search) {
                $builder
                    ->where('machineries.name', 'like', "%{$search}%")
                    ->orWhere('machineries.purchase_location', 'like', "%{$search}%")
                    ->orWhere('machineries.contact', 'like', "%{$search}%");
            });
        }

        $summary = [
            'machineries' => (clone $query)->count(),
            'with_maintenance' => (clone $query)->whereNotNull('machineries.last_maintenance')->count(),
        ];

        $sort = in_array($params['sort'] ?? '', ['name', 'purchase_date', 'last_maintenance', 'purchase_location', 'contact'], true)
            ? $params['sort']
            : 'name';
        $direction = ($params['direction'] ?? 'asc') === 'desc' ? 'desc' : 'asc';
        $perPage = min(max((int) ($params['per_page'] ?? 12), 1), 24);
        $page = max((int) ($params['page'] ?? 1), 1);

        $paginator = $query
            ->orderBy("machineries.{$sort}", $direction)
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
