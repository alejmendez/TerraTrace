<?php

namespace Modules\Fields\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LiquidationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'date' => $this->date,
            'week' => $this->week,
            'year' => $this->year,
            'delivery_date' => $this->delivery_date,
            'reception_date' => $this->reception_date,
            'weight_with_earth' => $this->weight_with_earth,
            'weight_washed' => $this->weight_washed,
            'dollar_value' => $this->dollar_value,
            'field_id' => [
                'value' => optional($this->field)->id,
                'text' => optional($this->field)->name,
            ],
            'importer_id' => [
                'value' => optional($this->importer)->id,
                'text' => optional($this->importer)->name,
            ],
            'products' => $this->liquidationProducts->map(function ($liquidationProduct, $key) {
                return [
                    'id' => $liquidationProduct->id,
                    'key' => $liquidationProduct->category_product_id,
                    'category_product_id' => $liquidationProduct->category_product_id,
                    'name' => optional($liquidationProduct->categoryProduct)->name,
                    'price' => $liquidationProduct->price,
                    'weight' => $liquidationProduct->weight,
                ];
            }),
        ];
    }
}
