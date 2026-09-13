<?php

namespace Modules\Fields\Services\CategoryProducts;

use Modules\Fields\Models\CategoryProduct;

class UpdateCategoryProduct
{
    public static function call($id, $data): CategoryProduct
    {
        $CategoryProduct = CategoryProduct::findOrFail($id);

        $CategoryProduct->name = $data['name'];
        $CategoryProduct->is_commercial = $data['is_commercial'];
        $CategoryProduct->save();

        return $CategoryProduct;
    }
}
