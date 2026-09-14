<?php

namespace Modules\Fields\Services\Batches;

use Modules\Core\Services\PrimevueDatatables;
use Modules\Fields\Models\Batch;

class ListBatch
{
    public static function call($params = [])
    {
        $searchableColumns = ['batch_number', 'delivery_date', 'importer.name'];

        $query = Batch::query();

        $datatable = new PrimevueDatatables($params, $searchableColumns);
        $batches = $datatable->of($query)->make();

        return $batches;
    }

    public static function collection(array $params = []): array
    {
        $query = Batch::query()
            ->select('batches.id', 'batches.batch_number', 'batches.delivery_date', 'batches.importer_id', 'batches.carrier', 'batches.current_weight')
            ->with(['importer:id,name', 'harvests:id,year,week,batch']);

        $search = trim($params['q'] ?? '');

        if ($search !== '') {
            $query->where(function ($builder) use ($search) {
                $builder
                    ->where('batches.batch_number', 'like', "%{$search}%")
                    ->orWhere('batches.carrier', 'like', "%{$search}%")
                    ->orWhereHas('importer', function ($importerQuery) use ($search) {
                        $importerQuery->where('name', 'like', "%{$search}%");
                    });
            });
        }

        if (!empty($params['importer_id'])) {
            $query->where('batches.importer_id', $params['importer_id']);
        }

        $summary = [
            'batches' => (clone $query)->count(),
            'total_weight' => (clone $query)->sum('batches.current_weight'),
        ];

        $sort = in_array($params['sort'] ?? '', ['batch_number', 'delivery_date', 'current_weight'], true)
            ? $params['sort']
            : 'delivery_date';
        $direction = ($params['direction'] ?? 'desc') === 'asc' ? 'asc' : 'desc';
        $perPage = min(max((int) ($params['per_page'] ?? 12), 1), 24);
        $page = max((int) ($params['page'] ?? 1), 1);

        $paginator = $query
            ->orderBy("batches.{$sort}", $direction)
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
