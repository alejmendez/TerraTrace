<?php

namespace Modules\Fields\Services\CategoryProducts;

use Modules\Fields\Models\CategoryProduct;

class CreateCategoryProduct
{
    public static function call($data): CategoryProduct
    {
        $CategoryProduct = new CategoryProduct;
        $CategoryProduct->name = $data['name'];
        $CategoryProduct->is_commercial = $data['is_commercial'];

        $CategoryProduct->save();

        return $CategoryProduct;
    }
}
