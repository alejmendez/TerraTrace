<?php

namespace Modules\Fields\Services;

use Modules\Fields\Models\Field;
use Modules\Fields\Models\Liquidation;

/**
 * Cross-module "stats" provider for the Fields domain.
 *
 * The Dashboard module needs aggregated field data — current-year
 * vs last-year harvest weights, average per-plant yields, etc. —
 * without depending on Fields models, queries or per-action services.
 *
 * Each dashboard-relevant stat lives here as an instance method.
 * Dashboard injects this provider via its constructor and calls
 * only the public methods, keeping the cross-module surface small.
 *
 * If a future Fields entity needs dashboard exposure, add the
 * aggregator here (and only here) instead of reaching into Fields
 * internals from Dashboard.
 */
class FieldsStatsProvider
{
    public function __construct(private readonly HarvestService $harvests) {}

    /**
     * Locate a field by id, eager-loading the counts Dashboard needs.
     *
     * Kept here (rather than a separate "FieldsQueryProvider") because
     * it's a small operation that Dashboard legitimately needs to
     * bootstrap its view, and the eager-loads are an exact match for
     * the stats methods below.
     *
     * NOTE: previously eager-loaded `responsible` and `field` here as
     * a leftover copy-paste from QuarterService::find() — neither
     * relationship exists on `Field` (Field has `owner`, not
     * `responsible`; the `fields` table has no self-reference). The
     * bogus eager-loads threw "Call to undefined relationship" the
     * moment Dashboard booted after login.
     */
    public function findField(int|string $id)
    {
        return Field::withCount('plants')
            ->findOrFail($id);
    }

    /**
     * Harvest stats for the dashboard view of one field.
     *
     * @return array{
     *   total_weight_of_last_harvest: float,
     *   average_weight_per_plant: float,
     *   variation_between_harvests: float,
     *   years_variation: array{int, int}
     * }
     */
    public function harvestStatsForField(Field $field): array
    {
        $currentYear = (int) $this->harvests->lastYear();
        $lastYear = $currentYear - 1;

        $currentYearWeight = (float) $this->liquidationWeightSumForYear($field, $currentYear);
        $lastYearWeight = (float) $this->liquidationWeightSumForYear($field, $lastYear);

        $variation = $lastYearWeight == 0
            ? 0
            : round(($currentYearWeight * 100 / $lastYearWeight) - 100, 2);

        $averagePerPlant = $field->plants_count > 0
            ? round($currentYearWeight * 1000 / $field->plants_count, 2)
            : 0;

        return [
            'total_weight_of_last_harvest' => round($currentYearWeight, 2),
            'average_weight_per_plant' => $averagePerPlant,
            'variation_between_harvests' => $variation,
            'years_variation' => [$currentYear, $lastYear],
        ];
    }

    /**
     * Aggregated commercial-product weight for one field in one year.
     *
     * Extracted so it can be unit-tested independently of the
     * surrounding stats shape.
     */
    private function liquidationWeightSumForYear(Field $field, int $year)
    {
        return Liquidation::leftJoin('liquidation_products', 'liquidations.id', '=', 'liquidation_products.liquidation_id')
            ->leftJoin('category_products', 'liquidation_products.category_product_id', '=', 'category_products.id')
            ->selectRaw('sum(liquidation_products.weight) as weight_sum')
            ->where('field_id', $field->id)
            ->where('year', $year)
            ->where('category_products.is_commercial', true)
            ->get()
            ->first()?->weight_sum;
    }
}
