<?php

namespace Modules\Fields\Services\Graphs;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\Fields\Models\Harvest;
use Modules\Fields\Models\Plant;
use Modules\Fields\Services\Harvests\HarvestAvailableLastYear;

class GraphDataPlant
{
    protected static int $dataTtl = 300;

    public static function call($id, $year, $type, $filters)
    {
        $plant = Plant::findOrFail($id);
        $harvest_available_last_year = HarvestAvailableLastYear::call();
        $year = $year ?? $harvest_available_last_year;

        $typeQuery = match ($type) {
            'plant-on-demand-production' => self::onDemandProduction($plant, $year),
            default => [],
        };

        if (is_array($typeQuery) || $typeQuery instanceof \Illuminate\Support\Collection) {
            return $typeQuery;
        }

        return self::applyFilter($typeQuery, $filter)->get();
    }

    protected static function onDemandProduction($plant, $year)
    {
        $weeks = self::getWeeksFromYear($year);

        $harvests = Harvest::leftJoin('harvest_details', 'harvests.id', '=', 'harvest_details.harvest_id')
            ->select(DB::raw('sum(harvest_details.weight) as weight_sum'), 'week', 'year')
            ->where('plant_id', $plant->id)
            ->where('year', $year)
            ->groupBy('week', 'year')
            ->get();

        $labels = $weeks->toArray();

        $data_total = $weeks->map(function ($week) use ($harvests) {
            $harvest = $harvests->firstWhere('week', Str::after($week, 'Sem '));

            return $harvest ? $harvest->weight_sum : 0;
        });

        return [
            'title' => 'Cosecha por semana Año '.$year,
            'labels' => $labels,
            'series' => [
                [
                    'name' => 'Grs',
                    'data' => $data_total,
                ],
            ],
        ];
    }

    protected static function getWeeksFromYear($year)
    {
        return cache()->remember('harvest_weeks_'.$year, self::$dataTtl, function () use ($year) {
            return Harvest::where('year', $year)
                ->select('week')
                ->distinct()
                ->orderBy('week')
                ->pluck('week')
                ->map(function ($week) {
                    return 'Sem '.$week;
                });
        });

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
