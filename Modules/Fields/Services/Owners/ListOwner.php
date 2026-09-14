<?php

namespace Modules\Fields\Services\Owners;

use Modules\Core\Services\PrimevueDatatables;
use Modules\Fields\Models\Owner;

class ListOwner
{
    public static function call($params = [])
    {
        $searchableColumns = ['name', 'dni'];

        $query = Owner::query();

        $datatable = new PrimevueDatatables($params, $searchableColumns);
        $owners = $datatable->of($query)->make();

        return $owners;
    }

    public static function collection(array $params = []): array
    {
        $query = Owner::query()->select('owners.id', 'owners.name', 'owners.dni')->withCount('fields');

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
