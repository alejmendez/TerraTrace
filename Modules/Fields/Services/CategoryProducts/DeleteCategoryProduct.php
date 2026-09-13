<?php

namespace Modules\Fields\Services\CategoryProducts;

use Modules\Fields\Models\CategoryProduct;

class DeleteCategoryProduct
{
    public static function call($id): void
    {
        CategoryProduct::destroy($id);
    }
}
