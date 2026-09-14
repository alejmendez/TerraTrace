<?php

namespace Modules\Fields\Services\PlantTypes;

use Modules\Core\Services\PrimevueDatatables;
use Modules\Fields\Models\PlantType;

class ListPlantType
{
    public static function call($params = [])
    {
        $searchableColumns = ['name', 'slug'];

        $query = PlantType::query();

        $datatable = new PrimevueDatatables($params, $searchableColumns);
        $plantTypes = $datatable->of($query)->make();

        return $plantTypes;
    }

    public static function collection(array $params = []): array
    {
        $query = PlantType::query()->select('plant_types.id', 'plant_types.name', 'plant_types.slug')->withCount('plants');

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
