<?php

namespace Modules\Fields\Services\Harvests;

use Illuminate\Support\Facades\DB;

class HarvestAvailableWeeks
{
    protected static int $ttl = 60;

    public static function call()
    {
        return cache()->remember('harvest_available_weeks', self::$ttl, function () {
            return DB::table('harvests')
                ->select('week')
                ->distinct()
                ->get()
                ->map(function ($row) {
                    return ['value' => $row->week, 'text' => 'Semana '.$row->week];
                });
        });
    }
}
