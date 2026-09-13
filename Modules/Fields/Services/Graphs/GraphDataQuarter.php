<?php

namespace Modules\Fields\Services\Graphs;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\Fields\Models\Harvest;
use Modules\Fields\Models\Quarter;
use Modules\Fields\Services\Harvests\HarvestAvailableLastYear;

class GraphDataQuarter
{
    protected static int $dataTtl = 300;

    public static function call($id, $year, $type, $filters)
    {
        $quarter = Quarter::findOrFail($id);
        $harvest_available_last_year = HarvestAvailableLastYear::call();
        $year = $year ?? $harvest_available_last_year;

        $typeQuery = match ($type) {
            'quarter-on-demand-production' => self::onDemandProduction($quarter, $year),
            default => [],
        };

        if (is_array($typeQuery) || $typeQuery instanceof \Illuminate\Support\Collection) {
            return $typeQuery;
        }

        return self::applyFilter($typeQuery, $filter)->get();
    }

    protected static function onDemandProduction($quarter, $year)
    {
        $weeks = self::getWeeksFromYear($year);

        $harvests = Harvest::leftJoin('harvest_quarter', 'harvests.id', '=', 'harvest_quarter.harvest_id')
            ->select(DB::raw('sum(harvests.weight) as weight_sum'), 'quarter_id', 'week', 'year')
            // ->where('quarter_id', $quarter->id)
            ->where('year', $year)
            ->groupBy('harvest_quarter.quarter_id', 'week', 'year')
            ->get();

        $labels = $weeks->toArray();
        $quarters = Quarter::where('field_id', $quarter->field_id)->get();

        $series = $quarters->map(function ($quarter) use ($weeks, $harvests) {
            return [
                'name' => $quarter->name,
                'data' => $weeks->map(function ($week) use ($quarter, $harvests) {
                    $harvest = $harvests->where('quarter_id', $quarter->id)->firstWhere('week', Str::after($week, 'Sem '));

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
