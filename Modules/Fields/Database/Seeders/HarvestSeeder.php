<?php

namespace Modules\Fields\Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Modules\Fields\Models\Dog;
use Modules\Fields\Models\Field;
use Modules\Fields\Models\Harvest;
use Modules\Fields\Models\HarvestDetail;
use Modules\Fields\Models\Plant;
use Modules\Fields\Models\Quarter;
use Modules\Users\Models\User;

/**
 * Seeds realistic harvest history for the dashboard's "Temporada {year}" card.
 *
 * Why this seeder matters:
 *
 * `HarvestService::lastYear()` reads `Harvest::max('year')`. Without rows
 * in the harvests table, it falls back to `date('Y')` — which is fine for
 * the label, but the dashboard's variation computation
 * (FieldsStatsProvider::harvestStatsForField) needs HarvestDetail weights
 * so the "Temporada" card can show real kgs via the liquidation join.
 *
 * Also: the Harvest model has a `saving` hook that derives `year` and
 * `week` from `date`. We rely on it — don't pre-fill those fields.
 *
 * Pattern: this module owns its own data (AGENTS.md §4). No cross-module
 * imports; we use EntityRegistry-resolvable model names through the
 * standard Fields providers.
 */
class HarvestSeeder extends Seeder
{
    /**
     * Calendar months that make up the Chilean truffle season.
     * May (start) through September (tail). Jun-Aug is the productive peak.
     */
    private const SEASON_MONTHS = [5, 6, 7, 8, 9];

    /**
     * Approximate number of harvests per field per season.
     * Each season covers ~5 months; 5 harvests means roughly one per month,
     * matching the operational cadence of a small truffery.
     */
    private const HARVESTS_PER_FIELD_PER_YEAR = 5;

    public function run(): void
    {
        // Idempotency: don't duplicate rows if the importer already loaded data.
        if (Harvest::exists()) {
            return;
        }

        $fields = Field::with('quarters', 'quarters.plants')->orderBy('id')->get();

        if ($fields->isEmpty()) {
            $this->command?->warn('HarvestSeeder: no fields with quarters found, skipping. Run FieldSeeder and QuarterSeeder first.');

            return;
        }

        $farmers = User::role(['Agricultor', 'Técnico', 'Administrador', 'Super Admin'])->get();

        if ($farmers->isEmpty()) {
            $this->command?->warn('HarvestSeeder: no users available as farmer/assistant, skipping. Run UserSeeder first.');

            return;
        }

        $dog = Dog::inRandomOrder()->first();

        $years = $this->yearsToSeed();

        DB::transaction(function () use ($fields, $farmers, $dog, $years) {
            foreach ($years as $year) {
                foreach ($fields as $field) {
                    $quarters = $field->quarters;
                    if ($quarters->isEmpty()) {
                        continue;
                    }

                    $this->seedHarvestsForFieldYear($field, $year, $quarters, $farmers, $dog);
                }
            }
        });

        $this->command?->info(sprintf(
            'HarvestSeeder: %d harvests across %d fields × %d years.',
            Harvest::count(),
            $fields->count(),
            count($years),
        ));
    }

    /**
     * Years to seed. Includes the current year so lastYear() resolves to it
     * (otherwise the dashboard would show "Temporada {previousYear}" until
     * someone manually creates a harvest for the current year).
     *
     * @return array<int, int>
     */
    private function yearsToSeed(): array
    {
        $currentYear = (int) date('Y');

        return [
            $currentYear - 2,
            $currentYear - 1,
            $currentYear,
        ];
    }

    /**
     * Create HARVESTS_PER_FIELD_PER_YEAR harvests for one field/year,
     * distributed across the season months, each linked to a subset of the
     * field's quarters and to its plants via HarvestDetail rows.
     */
    private function seedHarvestsForFieldYear(Field $field, int $year, $quarters, $farmers, ?Dog $dog): void
    {
        // Pick one harvest date per season month, randomized within the month.
        // This keeps the timeline realistic without requiring per-month
        // fixtures.
        $monthDays = collect(self::SEASON_MONTHS)
            ->map(fn (int $month) => Carbon::create($year, $month, rand(3, 27)))
            ->sortBy(fn (Carbon $d) => $d->timestamp)
            ->values();

        // Choose distinct quarters for each harvest so the harvest_quarter
        // pivot doesn't get spammed with duplicates.
        $quarterBag = $quarters->shuffle();

        $farmer = $farmers->random();
        // Prefer a *different* assistant; fall back to the same farmer when
        // only one user is in the DB (e.g. fresh demo with admin-only seed).
        $assistantCandidates = $farmers->where('id', '!=', $farmer->id);
        $assistant = $assistantCandidates->isNotEmpty()
            ? $assistantCandidates->random()
            : $farmer;

        foreach ($monthDays as $i => $date) {
            // Rotate the quarter subset across the season so each quarter
            // sees a few harvests through the year.
            $quarterSubset = $this->pickQuarterSubset($quarterBag, $i);

            $harvest = Harvest::create([
                'date' => $date->toDateString(),
                'batch' => strtoupper(chr(65 + ($i % 26))),  // A, B, C, ...
                'dog_id' => $dog?->id,
                'farmer_id' => $farmer->id,
                'assistant_id' => $assistant->id,
                'note' => null,
            ]);

            $harvest->quarters()->attach($quarterSubset->pluck('id')->all());

            // Each harvest picks plants from its assigned quarters and records
            // a weight + quality per plant. Quality is a slugified category
            // name (matches HarvestService::syncDetails() convention).
            foreach ($quarterSubset as $quarter) {
                $plants = $quarter->plants;
                if ($plants->isEmpty()) {
                    continue;
                }

                // 60-90% of the quarter's plants are harvested in this batch
                // (rotating across the season so most plants see a harvest).
                $sample = $plants->random(min($plants->count(), rand(60, 90) > 50 ? $plants->count() : (int) ceil($plants->count() * 0.7)));

                foreach ($sample as $plant) {
                    HarvestDetail::create([
                        'harvest_id' => $harvest->id,
                        'plant_id' => $plant->id,
                        'quality' => $this->randomQuality(),
                        'weight' => $this->randomPlantWeightGrams(),
                        'quarter_id' => $quarter->id,
                    ]);
                }
            }
        }
    }

    /**
     * Rotate through quarters: harvest #0 picks a sliding window, then each
     * subsequent harvest shifts the window by one. This guarantees every
     * quarter ends up with data without making all harvests identical.
     */
    private function pickQuarterSubset($quarterBag, int $iteration)
    {
        $count = $quarterBag->count();

        if ($count <= 2) {
            return $quarterBag;
        }

        $window = max(1, (int) ceil($count * 0.6));
        $start = $iteration % $count;

        return $quarterBag->slice($start, $window)->values()
            ->whenEmpty(fn ($collection) => $quarterBag);
    }

    /**
     * Realistic truffle weight per plant (grams). Typical range:
     * - First-year inoculated plants rarely produce.
     * - Mature trees (5-15y): 30-180 g per truffle.
     * - The seeder doesn't track per-plant age, so we just use a mid-range
     *   with a long tail.
     */
    private function randomPlantWeightGrams(): float
    {
        // Box-Muller-ish: pick from a triangular distribution biased low.
        $a = rand(10, 80);
        $b = rand(10, 80);

        return round(($a + $b) / 2 + rand(0, 60), 2);
    }

    /**
     * Pick a quality slug for one harvest detail. The slug matches the
     * convention in HarvestService::syncDetails (Str::slug on the category).
     *
     * @return string
     */
    private function randomQuality()
    {
        $qualities = [
            'extra-class',
            'first-class',
            'second-class',
            'small',
            'pieces',
        ];

        return $qualities[array_rand($qualities)];
    }
}
