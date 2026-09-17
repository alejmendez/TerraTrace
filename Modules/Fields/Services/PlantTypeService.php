<?php

namespace Modules\Fields\Services;

use Illuminate\Support\Str;
use Modules\Core\Services\PrimevueDatatables;
use Modules\Fields\Models\PlantType;

class PlantTypeService
{
    private const SEARCHABLE_COLUMNS = ['name', 'slug'];

    public function find(string|int $id): PlantType
    {
        return PlantType::findOrFail($id);
    }

    public function create(array $data): PlantType
    {
        $slug = Str::slug($data['name']);

        // Idempotent on slug -- create-or-reuse so duplicate POSTs
        // with the same name don't 500. Mirrors the original CreatePlantType.
        $type = PlantType::where('slug', $slug)->first();
        if (! $type) {
            $type = new PlantType;
            $type->name = $data['name'];
            $type->slug = $slug;
            $type->save();
        }

        return $type;
    }

    public function update(string|int $id, array $data): PlantType
    {
        $type = PlantType::findOrFail($id);
        $type->name = $data['name'];
        $type->slug = Str::slug($data['name']);
        $type->save();

        return $type;
    }

    public function delete(string|int $id): void
    {
        PlantType::destroy($id);
    }

    public function list(array $params = []): mixed
    {
        $query = PlantType::query();

        $datatable = new PrimevueDatatables($params, self::SEARCHABLE_COLUMNS);

        return $datatable->of($query)->make();
    }

    public function collection(array $params = []): array
    {
        $query = PlantType::query()
            ->select('plant_types.id', 'plant_types.name', 'plant_types.slug')
            ->withCount('plants');

        $search = trim($params['q'] ?? '');

        if ($search !== '') {
            $query->where(function ($builder) use ($search) {
                $builder
                    ->where('plant_types.name', 'like', "%{$search}%")
                    ->orWhere('plant_types.slug', 'like', "%{$search}%");
            });
        }

        $summary = [
            'plant_types' => (clone $query)->count(),
            'with_plants' => (clone $query)->has('plants')->count(),
        ];

        $sort = in_array($params['sort'] ?? '', ['name', 'slug'], true)
            ? $params['sort']
            : 'name';
        $direction = ($params['direction'] ?? 'asc') === 'desc' ? 'desc' : 'asc';
        $perPage = min(max((int) ($params['per_page'] ?? 12), 1), 24);
        $page = max((int) ($params['page'] ?? 1), 1);

        $paginator = $query
            ->orderBy("plant_types.{$sort}", $direction)
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
