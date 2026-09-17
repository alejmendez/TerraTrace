<?php

namespace Modules\Fields\Services;

use Modules\Core\Services\PrimevueDatatables;
use Modules\Fields\Models\Machinery;

class MachineryService
{
    private const SEARCHABLE_COLUMNS = ['name', 'purchase_date', 'last_maintenance', 'purchase_location', 'contact'];

    public function find(string|int $id): Machinery
    {
        return Machinery::findOrFail($id);
    }

    public function create(array $data): Machinery
    {
        $machinery = new Machinery;

        $machinery->name = $data['name'];
        $machinery->purchase_date = $data['purchase_date'];
        $machinery->last_maintenance = $data['last_maintenance'];
        $machinery->purchase_location = $data['purchase_location'];
        $machinery->type = $data['type'];
        $machinery->contact = $data['contact'];
        $machinery->note = $data['note'];
        $machinery->save();

        return $machinery;
    }

    public function update(string|int $id, array $data): Machinery
    {
        $machinery = Machinery::findOrFail($id);

        $machinery->name = $data['name'];
        $machinery->purchase_date = $data['purchase_date'];
        $machinery->last_maintenance = $data['last_maintenance'];
        $machinery->purchase_location = $data['purchase_location'];
        $machinery->type = $data['type'];
        $machinery->contact = $data['contact'];
        $machinery->note = $data['note'];
        $machinery->save();

        return $machinery;
    }

    public function delete(string|int $id): void
    {
        Machinery::destroy($id);
    }

    public function list(array $params = []): mixed
    {
        $query = Machinery::query();
        $datatable = new PrimevueDatatables($params, self::SEARCHABLE_COLUMNS);

        return $datatable->of($query)->make();
    }

    public function collection(array $params = []): array
    {
        $query = Machinery::query()
            ->select('machineries.id', 'machineries.name', 'machineries.purchase_date', 'machineries.last_maintenance', 'machineries.purchase_location', 'machineries.contact');

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

    /**
     * Flat {value, text} list for cross-module consumers.
     */
    public function forSelect(): array
    {
        return Machinery::select('id as value', 'name as text')
            ->orderBy('name')
            ->get()
            ->toArray();
    }
}
