<?php

namespace Modules\Fields\Services;

use Modules\Core\Services\PrimevueDatatables;
use Modules\Fields\Models\SecurityEquipment;

class SecurityEquipmentService
{
    private const SEARCHABLE_COLUMNS = ['name', 'purchase_date', 'last_maintenance', 'purchase_location', 'contact'];

    public function find(string|int $id): SecurityEquipment
    {
        return SecurityEquipment::findOrFail($id);
    }

    public function create(array $data): SecurityEquipment
    {
        $security_equipment = new SecurityEquipment;

        $security_equipment->name = $data['name'];
        $security_equipment->purchase_date = $data['purchase_date'];
        $security_equipment->last_maintenance = $data['last_maintenance'];
        $security_equipment->purchase_location = $data['purchase_location'];
        $security_equipment->type = $data['type'];
        $security_equipment->contact = $data['contact'];
        $security_equipment->note = $data['note'];
        $security_equipment->save();

        return $security_equipment;
    }

    public function update(string|int $id, array $data): SecurityEquipment
    {
        $security_equipment = SecurityEquipment::findOrFail($id);

        $security_equipment->name = $data['name'];
        $security_equipment->purchase_date = $data['purchase_date'];
        $security_equipment->last_maintenance = $data['last_maintenance'];
        $security_equipment->purchase_location = $data['purchase_location'];
        $security_equipment->type = $data['type'];
        $security_equipment->contact = $data['contact'];
        $security_equipment->note = $data['note'];
        $security_equipment->save();

        return $security_equipment;
    }

    public function delete(string|int $id): void
    {
        SecurityEquipment::destroy($id);
    }

    public function list(array $params = []): mixed
    {
        $query = SecurityEquipment::query();
        $datatable = new PrimevueDatatables($params, self::SEARCHABLE_COLUMNS);

        return $datatable->of($query)->make();
    }

    public function collection(array $params = []): array
    {
        $query = SecurityEquipment::query()
            ->select('security_equipments.id', 'security_equipments.name', 'security_equipments.purchase_date', 'security_equipments.last_maintenance', 'security_equipments.purchase_location', 'security_equipments.contact');

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

    /**
     * Flat {value, text} list for cross-module consumers.
     */
    public function forSelect(): array
    {
        return SecurityEquipment::select('id as value', 'name as text')
            ->orderBy('name')
            ->get()
            ->toArray();
    }
}
