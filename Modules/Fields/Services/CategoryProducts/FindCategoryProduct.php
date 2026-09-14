<?php

namespace Modules\Fields\Services\CategoryProducts;

use Modules\Fields\Models\CategoryProduct;

class FindCategoryProduct
{
    public static function call($id)
    {
        $categoryProduct = CategoryProduct::findOrFail($id);

        return $categoryProduct;
    }
}
