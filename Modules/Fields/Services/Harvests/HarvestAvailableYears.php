<?php

namespace Modules\Fields\Services\Harvests;

use Illuminate\Support\Facades\DB;

class HarvestAvailableYears
{
    protected static int $ttl = 60;

    public static function call()
    {
        return cache()->remember('harvest_available_years', self::$ttl, function () {
            return DB::table('harvests')
                ->select('year')
                ->distinct()
                ->get()
                ->map(function ($row) {
                    return ['value' => $row->year, 'text' => $row->year];
                });
        });
    }
}
