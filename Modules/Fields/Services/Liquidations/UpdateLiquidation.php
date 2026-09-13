<?php

namespace Modules\Fields\Services\Liquidations;

use Modules\Fields\Models\Liquidation;
use Modules\Fields\Models\LiquidationProduct;

class UpdateLiquidation
{
    public static function call($id, $data): Liquidation
    {
        $liquidation = Liquidation::findOrFail($id);
        $liquidation->date = $data['date'];
        $liquidation->field_id = $data['field_id']['value'];
        $liquidation->delivery_date = $data['delivery_date'];
        $liquidation->reception_date = $data['reception_date'];
        $liquidation->weight_with_earth = $data['weight_with_earth'];
        $liquidation->weight_washed = $data['weight_washed'];
        $liquidation->dollar_value = $data['dollar_value'];
        $liquidation->importer_id = $data['importer_id']['value'];
        $liquidation->save();

        $existingProductIds = [];

        foreach ($data['products'] as $productData) {
            if (isset($productData['id'])) {
                $liquidationProduct = LiquidationProduct::find($productData['id']);
                if ($liquidationProduct) {
                    $liquidationProduct->category_product_id = $productData['category_product_id'];
                    $liquidationProduct->weight = $productData['weight'];
                    $liquidationProduct->price = $productData['price'];
                    $liquidationProduct->save();

                    $existingProductIds[] = $liquidationProduct->id;
                }
            } else {
                $newProduct = new LiquidationProduct;
                $newProduct->liquidation_id = $liquidation->id;
                $newProduct->category_product_id = $productData['category_product_id'];
                $newProduct->weight = $productData['weight'];
                $newProduct->price = $productData['price'];
                $newProduct->save();

                $existingProductIds[] = $newProduct->id;
            }
        }

        LiquidationProduct::where('liquidation_id', $liquidation->id)
            ->whereNotIn('id', $existingProductIds)
            ->delete();

        return $liquidation;
    }
}
