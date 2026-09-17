<?php

namespace Modules\Fields\Services\Liquidations;

use Modules\Core\Services\PrimevueDatatables;
use Modules\Fields\Models\Liquidation;

class ListLiquidation
{
    public static function call($params = [])
    {
        $searchableColumns = ['liquidation_number', 'delivery_date', 'importer.name'];

        $query = Liquidation::select(
            'liquidations.*',
            \DB::raw('SUM(CASE WHEN category_products.is_commercial = true THEN liquidation_products.weight ELSE 0 END) as total_commercial'),
            \DB::raw('SUM(CASE WHEN category_products.is_commercial = false THEN liquidation_products.weight ELSE 0 END) as total_not_commercial')
        )
            ->leftJoin('liquidation_products', 'liquidations.id', '=', 'liquidation_products.liquidation_id')
            ->leftJoin('category_products', 'liquidation_products.category_product_id', '=', 'category_products.id')
            ->groupBy('liquidations.id');

        $datatable = new PrimevueDatatables($params, $searchableColumns);
        $liquidations = $datatable->of($query)->make();

        return $liquidations;
    }

    public static function collection(array $params = []): array
    {
        $query = Liquidation::query()
            ->select('liquidations.id', 'liquidations.liquidation_number', 'liquidations.delivery_date', 'liquidations.importer_id', 'liquidations.created_at')
            ->with(['importer:id,name'])
            ->withSum([
                'products as total_commercial' => function ($query) {
                    $query->whereHas('categoryProduct', fn ($q) => $q->where('is_commercial', true));
                },
            ], 'weight')
            ->withSum([
                'products as total_not_commercial' => function ($query) {
                    $query->whereHas('categoryProduct', fn ($q) => $q->where('is_commercial', false));
                },
            ], 'weight');

        $search = trim($params['q'] ?? '');

        if ($search !== '') {
            $query->where(function ($builder) use ($search) {
                $builder
                    ->where('liquidations.liquidation_number', 'like', "%{$search}%")
                    ->orWhereHas('importer', function ($importerQuery) use ($search) {
                        $importerQuery->where('name', 'like', "%{$search}%");
                    });
            });
        }

        if (! empty($params['importer_id'])) {
            $query->where('liquidations.importer_id', $params['importer_id']);
        }

        if (! empty($params['year'])) {
            $query->whereYear('liquidations.delivery_date', $params['year']);
        }

        $summary = [
            'liquidations' => (clone $query)->count(),
            'total_commercial' => (clone $query)->get()->sum('total_commercial'),
            'total_not_commercial' => (clone $query)->get()->sum('total_not_commercial'),
        ];

        $sort = in_array($params['sort'] ?? '', ['liquidation_number', 'delivery_date'], true)
            ? $params['sort']
            : 'delivery_date';
        $direction = ($params['direction'] ?? 'desc') === 'asc' ? 'asc' : 'desc';
        $perPage = min(max((int) ($params['per_page'] ?? 12), 1), 24);
        $page = max((int) ($params['page'] ?? 1), 1);

        $paginator = $query
            ->orderBy("liquidations.{$sort}", $direction)
            ->paginate($perPage, ['*'], 'page', $page);

        $items = collect($paginator->items())->map(function ($liquidation) {
            $liquidation->total_commercial = (float) ($liquidation->total_commercial ?? 0);
            $liquidation->total_not_commercial = (float) ($liquidation->total_not_commercial ?? 0);

            return $liquidation;
        })->all();

        return [
            'items' => $items,
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
