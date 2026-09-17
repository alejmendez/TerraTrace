<?php

namespace Modules\Fields\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\Fields\Models\Field;
use Modules\Fields\Models\Harvest;
use Modules\Fields\Models\Liquidation;
use Modules\Fields\Models\Plant;
use Modules\Fields\Models\Quarter;

/**
 * Assembles the JSON payloads consumed by the dashboard chart widgets
 * (field / quarter / plant scoped). Three concerns fold into one
 * class:
 *
 *  - The dispatch by `type` prefix that used to live in
 *    Modules\Fields\Services\Graphs\GraphData::call.
 *  - The three per-entity query helpers that used to live in
 *    GraphDataField / GraphDataQuarter / GraphDataPlant.
 *  - The caching and `lastYear` plumbing that used to reach into the
 *    static HarvestAvailableLastYear helper.
 *
 * The original `Str::of($type)->startsWith(...)` check in GraphData
 * would have crashed at runtime -- Stringable doesn't expose
 * startsWith the way it was written. Replaced with `Str::startsWith`
 * which is the real Laravel string helper.
 */
class GraphDataService
{
    private const WEEKS_CACHE_TTL = 300;

    public function __construct(private readonly HarvestService $harvests) {}

    /**
     * Dispatch entry point. The HTTP controller calls this with the
     * raw request values; the service figures out which per-entity
     * helper to invoke based on the `type` prefix.
     */
    public function dispatch(int|string $id, ?int $year, string $type, mixed $filters)
    {
        $filter = is_array($filters) ? $filters : (json_decode((string) $filters, true) ?: []);

        if (Str::startsWith($type, 'field-')) {
            return $this->forField(Field::findOrFail($id), $year, $type, $filter);
        }

        if (Str::startsWith($type, 'quarter-')) {
            return $this->forQuarter(Quarter::findOrFail($id), $year, $type, $filter);
        }

        if (Str::startsWith($type, 'plant-')) {
            return $this->forPlant(Plant::findOrFail($id), $year, $type, $filter);
        }

        return [];
    }

    private function forField(Field $field, ?int $year, string $type, array $filter)
    {
        $year ??= $this->harvests->lastYear();

        $result = match ($type) {
            'field-on-demand-production' => $this->fieldOnDemandProduction($field, $year),
            'field-sales-vs-shrinkage' => $this->fieldSalesVsShrinkage($field, $year),
            'field-shrinkage-detail' => $this->fieldShrinkageDetail($field, $year),
            'field-type-of-shrinkage' => $this->fieldTypeOfShrinkage($field, $year),
            'field-comparative-of-selling-price-x-kgs' => $this->fieldComparativeOfSellingPriceXKgs($field, $year),
            default => [],
        };

        if (is_array($result) || $result instanceof Collection) {
            return $result;
        }

        return $this->applyFilter($result, $filter)->get();
    }

    private function forQuarter(Quarter $quarter, ?int $year, string $type, array $filter)
    {
        $year ??= $this->harvests->lastYear();

        $result = match ($type) {
            'quarter-on-demand-production' => $this->quarterOnDemandProduction($quarter, $year),
            default => [],
        };

        if (is_array($result) || $result instanceof Collection) {
            return $result;
        }

        return $this->applyFilter($result, $filter)->get();
    }

    private function forPlant(Plant $plant, ?int $year, string $type, array $filter)
    {
        $year ??= $this->harvests->lastYear();

        $result = match ($type) {
            'plant-on-demand-production' => $this->plantOnDemandProduction($plant, $year),
            default => [],
        };

        if (is_array($result) || $result instanceof Collection) {
            return $result;
        }

        return $this->applyFilter($result, $filter)->get();
    }

    private function fieldOnDemandProduction(Field $field, int $year): array
    {
        $liquidationsSumNotCommercial = Liquidation::leftJoin('liquidation_products', 'liquidations.id', '=', 'liquidation_products.liquidation_id')
            ->leftJoin('category_products', 'liquidation_products.category_product_id', '=', 'category_products.id')
            ->selectRaw('sum(liquidation_products.weight) as weight_sum')
            ->where('field_id', $field->id)
            ->where('year', $year)
            ->where('category_products.is_commercial', false)
            ->groupBy('week')
            ->orderBy('week', 'asc')
            ->get();

        $liquidationsSum = Liquidation::select('week')
            ->selectRaw('sum(weight_with_earth) as weight_with_earth, sum(weight_with_earth) - sum(weight_washed) as weight_earth')
            ->where('field_id', $field->id)
            ->where('year', $year)
            ->groupBy('week')
            ->orderBy('week', 'asc')
            ->get();

        if (count($liquidationsSum) === 0) {
            return [];
        }

        $labels = $liquidationsSum->map(fn ($l) => 'Sem '.$l->week)->values();
        $dataNotCommercial = $liquidationsSumNotCommercial->map(fn ($l) => round($l->weight_sum, 2))->values();
        $dataTotal = $liquidationsSum->map(fn ($l) => round($l->weight_with_earth, 2))->values();
        $dataTierra = $liquidationsSum->map(fn ($l) => round($l->weight_earth, 2))->values();

        return [
            'title' => 'Liquidacion por semana Año '.$year,
            'labels' => $labels,
            'series' => [
                ['name' => 'KGS total', 'data' => $dataTotal],
                ['name' => 'KGS Merma', 'data' => $dataNotCommercial],
                ['name' => 'KGS tierra', 'data' => $dataTierra],
            ],
        ];
    }

    private function fieldSalesVsShrinkage(Field $field, int $year): array
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

        $labels = $liquidations->map(fn ($l) => 'Sem '.$l->week)->unique()->values();
        $dataCommercial = $liquidations->filter(fn ($l) => $l->is_commercial)->map(fn ($l) => round($l->weight_sum, 2))->values();
        $dataNotCommercial = $liquidations->filter(fn ($l) => ! $l->is_commercial)->map(fn ($l) => round($l->weight_sum, 2))->values();

        return [
            'title' => 'KGS. Venta vs KGS. Merma Año '.$year,
            'labels' => $labels,
            'series' => [
                ['name' => 'KGS Comerciales', 'data' => $dataCommercial],
                ['name' => 'KGS Merma', 'data' => $dataNotCommercial],
            ],
        ];
    }

    private function fieldShrinkageDetail(Field $field, int $year): array
    {
        $liquidations = Liquidation::leftJoin('liquidation_products', 'liquidations.id', '=', 'liquidation_products.liquidation_id')
            ->leftJoin('category_products', 'liquidation_products.category_product_id', '=', 'category_products.id')
            ->select('is_commercial', DB::raw('sum(liquidation_products.weight) as weight_sum'))
            ->where('field_id', $field->id)
            ->where('year', $year)
            ->groupBy('category_products.is_commercial')
            ->get();

        if (count($liquidations) === 0) {
            return [];
        }

        $labels = ['KGS Comerciales', 'KGS Merma', 'KGS Tierra'];
        $dataGrouped = $liquidations->keyBy('is_commercial');

        $weightComercial = round($dataGrouped[1]->weight_sum, 2);
        $weightNotComercial = round($dataGrouped[0]->weight_sum, 2);

        $weightEarthQuery = Liquidation::select(DB::raw('sum(weight_with_earth) - sum(weight_washed) as weight_earth'))
            ->where('field_id', $field->id)
            ->where('year', $year)
            ->first();

        $weightEarth = round($weightEarthQuery->weight_earth, 2);

        return [
            'title' => 'Cosecha Año '.$year,
            'labels' => $labels,
            'series' => [
                $weightComercial,
                $weightNotComercial,
                $weightEarth,
            ],
        ];
    }

    private function fieldTypeOfShrinkage(Field $field, int $year): array
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

        $labels = $liquidations->map(fn ($l) => 'Sem '.$l->week)->unique()->values();
        $detailsByCategory = $liquidations->groupBy('name');
        $series = $detailsByCategory->map(fn ($items, $categoryName) => [
            'name' => $categoryName,
            'data' => $items->map(fn ($l) => floatval($l->weight_sum))->values(),
        ])->values();

        return [
            'title' => 'Merma por Tipo (%) Año '.$year,
            'labels' => $labels,
            'series' => $series,
        ];
    }

    private function fieldComparativeOfSellingPriceXKgs(Field $field, int $year): array
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
        $usdAvgExporter = [];
        $usdAvgField = [];

        foreach ($groupedData as $week => $items) {
            $items = collect($items)->map(function ($item) {
                $item['weight'] = floatval($item['weight']);
                $item['price'] = floatval($item['price']);

                return $item;
            });
            $labels[] = 'Sem '.$week;

            $avgPrice = $items->avg('price');
            $sumWeight = $items->sum('weight');
            $usdAvgExporter[] = round((float) $avgPrice, 2);

            $avgField = $items->reduce(fn ($carry, $item) => $carry + $item['weight'] * $item['price'], 0);
            $usdAvgField[] = round($avgField / $sumWeight, 2);
        }

        return [
            'title' => 'Comparativo de precio de venta x Kgs Año '.$year,
            'labels' => $labels,
            'series' => [
                ['name' => 'USD promedio Exportador', 'data' => $usdAvgExporter],
                ['name' => 'USD promedio Campo', 'data' => $usdAvgField],
            ],
        ];
    }

    private function quarterOnDemandProduction(Quarter $quarter, int $year): array
    {
        $weeks = $this->getWeeksFromYear($year);

        $harvests = Harvest::leftJoin('harvest_quarter', 'harvests.id', '=', 'harvest_quarter.harvest_id')
            ->select(DB::raw('sum(harvests.weight) as weight_sum'), 'quarter_id', 'week', 'year')
            ->where('year', $year)
            ->groupBy('harvest_quarter.quarter_id', 'week', 'year')
            ->get();

        $labels = $weeks->toArray();
        $quarters = Quarter::where('field_id', $quarter->field_id)->get();

        $series = $quarters->map(function ($q) use ($weeks, $harvests) {
            return [
                'name' => $q->name,
                'data' => $weeks->map(function ($week) use ($q, $harvests) {
                    $harvest = $harvests->where('quarter_id', $q->id)->firstWhere('week', Str::after($week, 'Sem '));

                    return $harvest ? $harvest->weight_sum : 0;
                })->values(),
            ];
        });

        return [
            'title' => 'Cosecha por semana Año '.$year,
            'labels' => $labels,
            'series' => $series,
        ];
    }

    private function plantOnDemandProduction(Plant $plant, int $year): array
    {
        $weeks = $this->getWeeksFromYear($year);

        $harvests = Harvest::leftJoin('harvest_details', 'harvests.id', '=', 'harvest_details.harvest_id')
            ->select(DB::raw('sum(harvest_details.weight) as weight_sum'), 'week', 'year')
            ->where('plant_id', $plant->id)
            ->where('year', $year)
            ->groupBy('week', 'year')
            ->get();

        $labels = $weeks->toArray();

        $dataTotal = $weeks->map(function ($week) use ($harvests) {
            $harvest = $harvests->firstWhere('week', Str::after($week, 'Sem '));

            return $harvest ? $harvest->weight_sum : 0;
        });

        return [
            'title' => 'Cosecha por semana Año '.$year,
            'labels' => $labels,
            'series' => [
                ['name' => 'Grs', 'data' => $dataTotal],
            ],
        ];
    }

    private function getWeeksFromYear(int $year)
    {
        return cache()->remember('harvest_weeks_'.$year, self::WEEKS_CACHE_TTL, function () use ($year) {
            return Harvest::where('year', $year)
                ->select('week')
                ->distinct()
                ->orderBy('week')
                ->pluck('week')
                ->map(fn ($week) => 'Sem '.$week);
        });
    }

    private function applyFilter($query, array $filter)
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
