<?php

namespace Modules\Fields\Services\Graphs;

use Illuminate\Support\Facades\DB;
use Modules\Fields\Models\Field;
use Modules\Fields\Models\Liquidation;
use Modules\Fields\Services\Harvests\HarvestAvailableLastYear;

class GraphDataField
{
    public static function call($id, $year, $type, $filters)
    {
        $field = Field::findOrFail($id);
        $harvest_available_last_year = HarvestAvailableLastYear::call();
        $year = $year ?? $harvest_available_last_year;

        $typeQuery = match ($type) {
            'field-on-demand-production' => self::onDemandProduction($field, $year),
            'field-sales-vs-shrinkage' => self::salesVsShrinkage($field, $year),
            'field-shrinkage-detail' => self::shrinkageDetail($field, $year),
            'field-type-of-shrinkage' => self::typeOfShrinkage($field, $year),
            'field-comparative-of-selling-price-x-kgs' => self::comparativeOfSellingPriceXKgs($field, $year),
            default => [],
        };

        if (is_array($typeQuery) || $typeQuery instanceof \Illuminate\Support\Collection) {
            return $typeQuery;
        }

        return self::applyFilter($typeQuery, $filter)->get();
    }

    protected static function onDemandProduction($field, $year)
    {
        $liquidations_sum_not_commercial = Liquidation::leftJoin('liquidation_products', 'liquidations.id', '=', 'liquidation_products.liquidation_id')
            ->leftJoin('category_products', 'liquidation_products.category_product_id', '=', 'category_products.id')
            ->selectRaw('sum(liquidation_products.weight) as weight_sum')
            ->where('field_id', $field->id)
            ->where('year', $year)
            ->where('category_products.is_commercial', false)
            ->groupBy('week')
            ->orderBy('week', 'asc')
            ->get();

        $liquidations_sum = Liquidation::select('week')
            ->selectRaw('sum(weight_with_earth) as weight_with_earth, sum(weight_with_earth) - sum(weight_washed) as weight_earth')
            ->where('field_id', $field->id)
            ->where('year', $year)
            ->groupBy('week')
            ->orderBy('week', 'asc')
            ->get();

        if (count($liquidations_sum) === 0) {
            return [];
        }

        $labels = $liquidations_sum->map(function ($l) {
            return 'Sem '.$l->week;
        })->values();

        $data_not_commercial = $liquidations_sum_not_commercial->map(function ($l) {
            return round($l->weight_sum, 2);
        })->values();

        $data_total = $liquidations_sum->map(function ($l) {
            return round($l->weight_with_earth, 2);
        })->values();

        $data_tierra = $liquidations_sum->map(function ($l) {
            return round($l->weight_earth, 2);
        })->values();

        return [
            'title' => 'Liquidacion por semana Año '.$year,
            'labels' => $labels,
            'series' => [
                [
                    'name' => 'KGS total',
                    'data' => $data_total,
                ],
                [
                    'name' => 'KGS Merma',
                    'data' => $data_not_commercial,
                ],
                [
                    'name' => 'KGS tierra',
                    'data' => $data_tierra,
                ],
            ],
        ];
    }

    protected static function salesVsShrinkage($field, $year)
    {
        // TODO: revisar
        $liquidations = Liquidation::leftJoin('liquidation_products', 'liquidations.id', '=', 'liquidation_products.liquidation_id')
            ->leftJoin('category_products', 'liquidation_products.category_product_id', '=', 'category_products.id')
            ->select('week', 'year', 'category_products.is_commercial')
            ->selectRaw('sum(liquidation_products.weight) as weight_sum')
            ->where('field_id', $field->id)
            ->where('year', $year)
            ->groupBy('week', 'year', 'category_products.is_commercial')
            ->orderBy('year', 'desc')
            ->orderBy('week', 'asc')
            ->get();

        if (count($liquidations) === 0) {
            return [];
        }

        $labels = $liquidations->map(function ($l) {
            return 'Sem '.$l->week;
        })->unique()->values();

        $data_commercial = $liquidations->filter(function ($l) {
            return $l->is_commercial;
        })->map(function ($l) {
            return round($l->weight_sum, 2);
        })->values();

        $data_not_commercial = $liquidations->filter(function ($l) {
            return ! $l->is_commercial;
        })->map(function ($l) {
            return round($l->weight_sum, 2);
        })->values();

        return [
            'title' => 'KGS. Venta vs KGS. Merma Año '.$year,
            'labels' => $labels,
            'series' => [
                [
                    'name' => 'KGS Comerciales',
                    'data' => $data_commercial,
                ],
                [
                    'name' => 'KGS Merma',
                    'data' => $data_not_commercial,
                ],
            ],
        ];
    }

    protected static function shrinkageDetail($field, $year)
    {
        $liquidations = Liquidation::leftJoin('liquidation_products', 'liquidations.id', '=', 'liquidation_products.liquidation_id')
            ->leftJoin('category_products', 'liquidation_products.category_product_id', '=', 'category_products.id')
            ->select(
                'is_commercial',
                DB::raw('sum(liquidation_products.weight) as weight_sum')
            )
            ->where('field_id', $field->id)
            ->where('year', $year)
            ->groupBy('category_products.is_commercial')
            ->get();

        if (count($liquidations) === 0) {
            return [];
        }

        $labels = [
            'KGS Comerciales',
            'KGS Merma',
            'KGS Tierra',
        ];

        $data_grouped = $liquidations->keyBy('is_commercial');

        $data_not_comercial = $data_grouped[0];
        $data_comercial = $data_grouped[1];

        $weight_comercial = round($data_comercial->weight_sum, 2);
        $weight_not_comercial = round($data_not_comercial->weight_sum, 2);

        $weight_earth_query = Liquidation::select(DB::raw('sum(weight_with_earth) - sum(weight_washed) as weight_earth'))
            ->where('field_id', $field->id)
            ->where('year', $year)
            ->first();

        $weight_earth = round($weight_earth_query->weight_earth, 2);

        $data = [
            $weight_comercial,
            $weight_not_comercial,
            $weight_earth,
        ];

        return [
            'title' => 'Cosecha Año '.$year,
            'labels' => $labels,
            'series' => $data,
        ];
    }

    protected static function typeOfShrinkage($field, $year)
    {
        $liquidations = Liquidation::leftJoin('liquidation_products', 'liquidations.id', '=', 'liquidation_products.liquidation_id')
            ->leftJoin('category_products', 'liquidation_products.category_product_id', '=', 'category_products.id')
            ->select('week', 'year', 'category_products.name')
            ->selectRaw('sum(liquidation_products.weight) as weight_sum')
            ->where('field_id', $field->id)
            ->where('year', $year)
            ->where('category_products.is_commercial', false)
            ->groupBy('week', 'year', 'category_products.name')
            ->orderBy('year', 'desc')
            ->orderBy('week', 'asc')
            ->get();

        if (count($liquidations) === 0) {
            return [];
        }

        $labels = $liquidations->map(function ($l) {
            return 'Sem '.$l->week;
        })->unique()->values();

        $detailsByCategory = $liquidations->groupBy('name');
        $types = $detailsByCategory->keys();

        $series = $detailsByCategory->map(function ($l, $categoryName) {
            return [
                'name' => $categoryName,
                'data' => $l->map(fn ($ll) => floatval($ll->weight_sum)),
            ];
        })->values();

        return [
            'title' => 'Merma por Tipo (%) Año '.$year,
            'labels' => $labels,
            'series' => $series,
        ];
    }

    protected static function comparativeOfSellingPriceXKgs($field, $year)
    {
        $liquidations = Liquidation::leftJoin('liquidation_products', 'liquidations.id', '=', 'liquidation_products.liquidation_id')
            ->leftJoin('category_products', 'liquidation_products.category_product_id', '=', 'category_products.id')
            ->select('week', 'year', 'liquidation_products.price', 'liquidation_products.weight')
            ->where('field_id', $field->id)
            ->where('year', $year)
            ->where('category_products.is_commercial', true)
            ->where('liquidation_products.price', '!=', 0)
            ->orderBy('year', 'desc')
            ->orderBy('week', 'asc')
            ->get();

        if (count($liquidations) === 0) {
            return [];
        }

        $groupedData = $liquidations->groupBy('week');

        $labels = [];
        $usd_avg_exporter = [];
        $usd_avg_field = [];

        // Recorrer cada grupo por semana
        foreach ($groupedData as $week => $items) {
            $items = collect($items)->map(function ($item) {
                $item['weight'] = floatval($item['weight']);
                $item['price'] = floatval($item['price']);

                return $item;
            });
            $labels[] = 'Sem '.$week;

            $avgPrice = $items->avg('price');
            $sumWeight = $items->sum('weight');
            $usd_avg_exporter[] = round((float) $avgPrice, 2);

            $avg_field = $items->reduce(function ($carry, $item) {
                return $carry + $item['weight'] * $item['price'];
            }, 0);
            $usd_avg_field[] = round($avg_field / $sumWeight, 2);
        }

        return [
            'title' => 'Comparativo de precio de venta x Kgs Año '.$year,
            'labels' => $labels,
            'series' => [
                [
                    'name' => 'USD promedio Exportador',
                    'data' => $usd_avg_exporter,
                ],
                [
                    'name' => 'USD promedio Campo',
                    'data' => $usd_avg_field,
                ],
            ],
        ];
    }

    protected static function applyFilter($query, $filter)
    {
        foreach ($filter as $field => $value) {
            if (is_array($value)) {
                $query->whereIn($field, $value);
            } else {
                $query->where($field, $value);
            }
        }

        return $query;
    }
}
