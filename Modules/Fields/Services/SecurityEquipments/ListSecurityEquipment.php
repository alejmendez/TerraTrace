<?php

namespace Modules\Fields\Services\SecurityEquipments;

use Modules\Core\Services\PrimevueDatatables;
use Modules\Fields\Models\SecurityEquipment;

class ListSecurityEquipment
{
    public static function call($params = [])
    {
        $searchableColumns = ['name', 'purchase_date', 'last_maintenance', 'purchase_location', 'contact'];

        $query = SecurityEquipment::query();

        $datatable = new PrimevueDatatables($params, $searchableColumns);
        $items = $datatable->of($query)->make();

        return $items;
    }

    public static function collection(array $params = []): array
    {
        $query = SecurityEquipment::query()->select('security_equipments.id', 'security_equipments.name', 'security_equipments.purchase_date', 'security_equipments.last_maintenance', 'security_equipments.purchase_location', 'security_equipments.contact');

        $search = trim($params['q'] ?? '');

        if ($search !== '') {
            $query->where(function ($builder) use ($search) {
                $builder
                    ->where('security_equipments.name', 'like', "%{$search}%")
                    ->orWhere('security_equipments.purchase_location', 'like', "%{$search}%")
                    ->orWhere('security_equipments.contact', 'like', "%{$search}%");
            });
        }

        $summary = [
            'equipments' => (clone $query)->count(),
            'with_maintenance' => (clone $query)->whereNotNull('security_equipments.last_maintenance')->count(),
        ];

        $sort = in_array($params['sort'] ?? '', ['name', 'purchase_date', 'last_maintenance', 'purchase_location', 'contact'], true)
            ? $params['sort']
            : 'name';
        $direction = ($params['direction'] ?? 'asc') === 'desc' ? 'desc' : 'asc';
        $perPage = min(max((int) ($params['per_page'] ?? 12), 1), 24);
        $page = max((int) ($params['page'] ?? 1), 1);

        $paginator = $query
            ->orderBy("security_equipments.{$sort}", $direction)
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
