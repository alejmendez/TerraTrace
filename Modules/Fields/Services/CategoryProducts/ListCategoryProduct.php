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
        $CategoryProducts = $datatable->of($query)->make();

        return $CategoryProducts;
    }
}
