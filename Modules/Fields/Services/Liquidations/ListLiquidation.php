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
}
