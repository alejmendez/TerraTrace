<?php

namespace Modules\Fields\Services\Importers;

use Modules\Core\Services\PrimevueDatatables;
use Modules\Fields\Models\Importer;

class ListImporter
{
    public static function call($params = [])
    {
        $searchableColumns = ['name', 'slug'];

        $query = Importer::query();

        $datatable = new PrimevueDatatables($params, $searchableColumns);
        $importers = $datatable->of($query)->make();

        return $importers;
    }

    public static function collection(array $params = []): array
    {
        $query = Importer::query()->select('importers.id', 'importers.name', 'importers.slug')->withCount(['batches', 'liquidations']);

        $search = trim($params['q'] ?? '');

        if ($search !== '') {
            $query->where(function ($builder) use ($search) {
                $builder
                    ->where('importers.name', 'like', "%{$search}%")
                    ->orWhere('importers.slug', 'like', "%{$search}%");
            });
        }

        $summary = [
            'importers' => (clone $query)->count(),
            'with_batches' => (clone $query)->has('batches')->count(),
        ];

        $sort = in_array($params['sort'] ?? '', ['name', 'slug'], true)
            ? $params['sort']
            : 'name';
        $direction = ($params['direction'] ?? 'asc') === 'desc' ? 'desc' : 'asc';
        $perPage = min(max((int) ($params['per_page'] ?? 12), 1), 24);
        $page = max((int) ($params['page'] ?? 1), 1);

        $paginator = $query
            ->orderBy("importers.{$sort}", $direction)
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
