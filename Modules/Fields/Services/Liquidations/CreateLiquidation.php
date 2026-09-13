<?php

namespace Modules\Fields\Services\Liquidations;

use Modules\Fields\Models\Liquidation;
use Modules\Fields\Models\LiquidationProduct;

class CreateLiquidation
{
    public static function call($data): Liquidation
    {

        $liquidation = new Liquidation;
        $liquidation->date = $data['date'];
        $liquidation->field_id = $data['field_id']['value'];
        $liquidation->delivery_date = $data['delivery_date'];
        $liquidation->reception_date = $data['reception_date'];
        $liquidation->weight_with_earth = $data['weight_with_earth'];
        $liquidation->weight_washed = $data['weight_washed'];
        $liquidation->dollar_value = $data['dollar_value'];
        $liquidation->importer_id = $data['importer_id']['value'];
        $liquidation->save();

        foreach ($data['products'] as $productData) {
            $liquidationProduct = new LiquidationProduct;
            $liquidationProduct->liquidation_id = $liquidation->id;
            $liquidationProduct->category_product_id = $productData['category_product_id'];
            $liquidationProduct->weight = $productData['weight'];
            $liquidationProduct->price = $productData['price'];
            $liquidationProduct->save();
        }

        return $liquidation;
    }
}
