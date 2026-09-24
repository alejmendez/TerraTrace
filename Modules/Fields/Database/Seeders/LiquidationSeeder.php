<?php

namespace Modules\Fields\Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Modules\Fields\Models\CategoryProduct;
use Modules\Fields\Models\Field;
use Modules\Fields\Models\Liquidation;
use Modules\Fields\Models\LiquidationProduct;

/**
 * Seeds commercial-weight liquidations for the dashboard KPIs.
 *
 * Why this seeder exists:
 *
 * The dashboard's "Temporada {year}" and "Variación de cosecha" cards are
 * computed by FieldsStatsProvider::harvestStatsForField(), which sums
 * `liquidation_products.weight` filtered by `category_products.is_commercial = true`
 * for the latest year and the previous year. Without liquidations both
 * cards render zeros — even if harvests and plants are present.
 *
 * Pattern: each module is the owner of its own data (AGENTS.md §4). We
 * seed the tables this module owns (`liquidations`, `liquidation_products`)
 * and rely on the existing foreign keys to `fields` and `category_products`
 * to keep the graph consistent.
 *
 * Numbers are tuned to look like a small Chilean truffery in the Maule /
 * O'Higgins region (~5 fields × 100 kgs/año) with realistic year-over-year
 * variation so the "Variación" card shows a non-zero, non-trivial %.
 */
class LiquidationSeeder extends Seeder
{
    /**
     * Commercial categories (is_commercial = true). The 9 listed here
     * are the ones from CategoryProductSeeder; the seeder only uses
     * the commercial subset so it matches the provider's filter.
     *
     * @var array<int, string>
     */
    private const COMMERCIAL_CATEGORIES = [
        'EXTRA CLASS',
        'FIRST CLASS',
        'BIG TRUFFLES (>150 gr)',
        'SECOND CLASS',
        'SMALL',
        'EXTRA SMALL (9-19 gr)',
        'Mini (menor que 9 gr)',
        'PIECES',
        'Industria',
    ];

    /**
     * Per-field yearly commercial weight targets (kgs) for each year.
     *
     * Indexed by FIELD-ORDER (the field's position in the alphabetical
     * sort that Dashboard::forSelect() applies). The dashboard always
     * picks the alphabetically-first field, so we make sure at least that
     * field has rich data — but for consistency we also seed the others.
     *
     * Format: [year => kgs target]. Values are picked per field so the
     * "Variación" card shows a real, plausible delta between 2025 and 2026.
     */
    private const FIELD_YEAR_WEIGHT_TARGETS = [
        // year => kgs (commercial, summed across all liquidations for the field/year)
        2024 => 70,
        2025 => 65,
        2026 => 78,
    ];

    /**
     * Rough share (0-1) of each commercial category in a "normal" liquidation.
     * Truffles in Chile skew heavily toward first class and second class;
     * extra class and big truffles are rarer. The seeder picks a category
     * per product using these weights.
     */
    private const CATEGORY_SHARE = [
        'EXTRA CLASS' => 0.05,
        'FIRST CLASS' => 0.20,
        'BIG TRUFFLES (>150 gr)' => 0.08,
        'SECOND CLASS' => 0.25,
        'SMALL' => 0.15,
        'EXTRA SMALL (9-19 gr)' => 0.10,
        'Mini (menor que 9 gr)' => 0.05,
        'PIECES' => 0.07,
        'Industria' => 0.05,
    ];

    public function run(): void
    {
        // Idempotency: if liquidations already exist, this seeder is a no-op.
        // Real datasets may have been loaded via the importer UI; do not
        // overwrite them.
        if (Liquidation::exists()) {
            return;
        }

        $fields = Field::orderBy('id')->get();

        if ($fields->isEmpty()) {
            $this->command?->warn('LiquidationSeeder: no fields found, skipping.');

            return;
        }

        $categoryProducts = CategoryProduct::whereIn('name', self::COMMERCIAL_CATEGORIES)
            ->get()
            ->keyBy('name');

        if ($categoryProducts->isEmpty()) {
            $this->command?->warn('LiquidationSeeder: no commercial CategoryProduct rows found, skipping. Run CategoryProductSeeder first.');

            return;
        }

        DB::transaction(function () use ($fields, $categoryProducts) {
            foreach ($fields as $field) {
                foreach (self::FIELD_YEAR_WEIGHT_TARGETS as $year => $targetKgs) {
                    $this->seedLiquidationsForFieldYear($field, $year, $targetKgs, $categoryProducts);
                }
            }
        });

        $this->command?->info(sprintf(
            'LiquidationSeeder: %d liquidations across %d fields × %d years.',
            Liquidation::count(),
            $fields->count(),
            count(self::FIELD_YEAR_WEIGHT_TARGETS),
        ));
    }

    /**
     * Seed 3-4 liquidations for one field/year, distributing the target
     * commercial weight across them and across commercial categories.
     */
    private function seedLiquidationsForFieldYear(Field $field, int $year, float $targetKgs, $categoryProducts): void
    {
        // Season: 3 liquidation windows per year (Jun, Jul, Aug — peak Chilean
        // truffle season). Pick a real, sequential pair of dates per year.
        $deliveryDates = $this->deliveryDatesForYear($year);

        // Split the target across the 3 windows: 25% / 40% / 35% (July-heavy).
        $shares = [0.25, 0.40, 0.35];

        foreach ($deliveryDates as $i => $date) {
            $liquidation = Liquidation::create([
                'date' => $date->copy()->subDays(rand(3, 7)),  // reception ~a week before delivery
                'delivery_date' => $date->toDateString(),
                'reception_date' => $date->copy()->subDays(rand(1, 3)),
                'weight_with_earth' => round($targetKgs * $shares[$i] * 1.18, 2),  // ~18% earth weight overhead
                'weight_washed' => round($targetKgs * $shares[$i], 2),
                'dollar_value' => rand(450, 720),  // USD/CLP-ish placeholder
                'field_id' => $field->id,
                'importer_id' => null,
                'liquidation_number' => 0,  // placeholder; backfilled to id below (column is NOT NULL)
            ]);

            // `liquidation_number` mirrors `id` for new rows — the migration
            // backfilled it that way. Update after the initial insert so the
            // generated id is available.
            $liquidation->liquidation_number = $liquidation->id;
            $liquidation->save();

            // 4-7 products per liquidation, distributed by category share.
            $productCount = rand(4, 7);
            $productShares = $this->splitShares($productCount);

            foreach ($productShares as $categoryName => $share) {
                $category = $categoryProducts->get($categoryName);
                if (! $category) {
                    continue;
                }

                $productWeight = round($liquidation->weight_washed * $share, 2);

                LiquidationProduct::create([
                    'liquidation_id' => $liquidation->id,
                    'category_product_id' => $category->id,
                    'weight' => $productWeight,
                    'price' => $this->priceForCategory($categoryName),
                ]);
            }
        }
    }

    /**
     * Three delivery dates (one per month) during the Chilean truffle
     * season for the given calendar year.
     */
    private function deliveryDatesForYear(int $year): array
    {
        return [
            Carbon::create($year, 6, rand(8, 18)),   // early June
            Carbon::create($year, 7, rand(8, 22)),   // mid-July (peak)
            Carbon::create($year, 8, rand(5, 25)),   // late August
        ];
    }

    /**
     * Distribute the 9 commercial categories into N slots using CATEGORY_SHARE.
     * The output is a map [categoryName => share] where shares sum to 1.
     */
    private function splitShares(int $count): array
    {
        // Bucket categories by their share; with N slots, each slot gets a
        // random category with probability proportional to its share.
        $categories = array_keys(self::CATEGORY_SHARE);
        $weights = array_values(self::CATEGORY_SHARE);

        $picked = [];
        for ($i = 0; $i < $count; $i++) {
            $picked[] = $this->weightedPick($categories, $weights);
        }

        $unique = array_count_values($picked);
        $perCategoryShare = 1 / $count;  // equal weight per product slot

        $shares = [];
        foreach ($unique as $categoryName => $occurrences) {
            $shares[$categoryName] = $occurrences * $perCategoryShare;
        }

        return $shares;
    }

    /**
     * Pick one element from $items using $weights as relative probabilities.
     */
    private function weightedPick(array $items, array $weights): string
    {
        $total = array_sum($weights);
        $rand = mt_rand() / mt_getrandmax() * $total;
        $acc = 0;
        foreach ($items as $i => $item) {
            $acc += $weights[$i];
            if ($rand <= $acc) {
                return $item;
            }
        }

        return $items[count($items) - 1];
    }

    /**
     * Approximate USD-equivalent price per kg for each category.
     * These are illustrative; real prices vary by season and importer.
     */
    private function priceForCategory(string $name): float
    {
        return match ($name) {
            'EXTRA CLASS' => rand(900, 1300),
            'FIRST CLASS' => rand(700, 950),
            'BIG TRUFFLES (>150 gr)' => rand(1100, 1600),
            'SECOND CLASS' => rand(450, 650),
            'SMALL' => rand(350, 500),
            'EXTRA SMALL (9-19 gr)' => rand(250, 380),
            'Mini (menor que 9 gr)' => rand(150, 250),
            'PIECES' => rand(200, 320),
            'Industria' => rand(80, 160),
            default => 400,
        };
    }
}
