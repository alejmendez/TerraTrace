<?php

namespace Modules\Fields\Services;

use Modules\Core\Services\PrimevueDatatables;
use Modules\Fields\Models\Owner;

class OwnerService
{
    private const SEARCHABLE_COLUMNS = ['name', 'dni'];

    public function find(string|int $id): Owner
    {
        return Owner::findOrFail($id);
    }

    public function create(array $data): Owner
    {
        $owner = new Owner;
        $owner->name = $data['name'];
        $owner->dni = $data['dni'];
        $owner->save();

        return $owner;
    }

    public function update(string|int $id, array $data): Owner
    {
        $owner = Owner::findOrFail($id);

        $owner->name = $data['name'];
        $owner->dni = $data['dni'];
        $owner->save();

        return $owner;
    }

    /**
     * Idempotent upsert by DNI. Originally lived in
     * Modules\Fields\Services\Owners\CreateOrUpdateOwner (a plain
     * transversal class with a static ::call()); promoted to an
     * instance method on OwnerService so the cross-module consumer
     * (FieldService::create / update) can inject OwnerService
     * instead of calling a static helper.
     */
    public function createOrUpdate(string $dni, string $name): Owner
    {
        return Owner::updateOrCreate(['dni' => $dni], ['name' => $name]);
    }

    public function delete(string|int $id): void
    {
        Owner::destroy($id);
    }

    public function list(array $params = []): mixed
    {
        $query = Owner::query();
        $datatable = new PrimevueDatatables($params, self::SEARCHABLE_COLUMNS);

        return $datatable->of($query)->make();
    }

    public function collection(array $params = []): array
    {
        $query = Owner::query()
            ->select('owners.id', 'owners.name', 'owners.dni')
            ->withCount('fields');

        $search = trim($params['q'] ?? '');

        if ($search !== '') {
            $query->where(function ($builder) use ($search) {
                $builder
                    ->where('owners.name', 'like', "%{$search}%")
                    ->orWhere('owners.dni', 'like', "%{$search}%");
            });
        }

        $summary = [
            'owners' => (clone $query)->count(),
            'with_fields' => (clone $query)->has('fields')->count(),
        ];

        $sort = in_array($params['sort'] ?? '', ['name', 'dni'], true)
            ? $params['sort']
            : 'name';
        $direction = ($params['direction'] ?? 'asc') === 'desc' ? 'desc' : 'asc';
        $perPage = min(max((int) ($params['per_page'] ?? 12), 1), 24);
        $page = max((int) ($params['page'] ?? 1), 1);

        $paginator = $query
            ->orderBy("owners.{$sort}", $direction)
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
