<?php

namespace Modules\Fields\Services\CategoryProducts;

use Modules\Core\Services\PrimevueDatatables;
use Modules\Fields\Models\CategoryProduct;

class ListCategoryProduct
{
    public static function call($params = [])
    {
        $searchableColumns = ['name', 'is_commercial'];

        $query = CategoryProduct::query();

        $datatable = new PrimevueDatatables($params, $searchableColumns);
        $categoryProducts = $datatable->of($query)->make();

        return $categoryProducts;
    }

    public static function collection(array $params = []): array
    {
        $query = CategoryProduct::query()->select('category_products.id', 'category_products.name', 'category_products.is_commercial')->withCount('liquidationProducts');

        $search = trim($params['q'] ?? '');

        if ($search !== '') {
            $query->where(function ($builder) use ($search) {
                $builder->where('category_products.name', 'like', "%{$search}%");
            });
        }

        if (isset($params['is_commercial']) && $params['is_commercial'] !== '') {
            $query->where('category_products.is_commercial', filter_var($params['is_commercial'], FILTER_VALIDATE_BOOLEAN));
        }

        $summary = [
            'categories' => (clone $query)->count(),
            'commercial' => (clone $query)->where('category_products.is_commercial', true)->count(),
            'non_commercial' => (clone $query)->where('category_products.is_commercial', false)->count(),
        ];

        $sort = in_array($params['sort'] ?? '', ['name', 'is_commercial'], true)
            ? $params['sort']
            : 'name';
        $direction = ($params['direction'] ?? 'asc') === 'desc' ? 'desc' : 'asc';
        $perPage = min(max((int) ($params['per_page'] ?? 12), 1), 24);
        $page = max((int) ($params['page'] ?? 1), 1);

        $paginator = $query
            ->orderBy("category_products.{$sort}", $direction)
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
