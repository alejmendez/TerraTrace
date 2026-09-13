<?php

namespace Modules\Fields\Services\Liquidations;

use Illuminate\Support\Facades\DB;

class LiquidationAvailableYears
{
    protected static int $ttl = 60;

    public static function call()
    {
        return cache()->remember('liquidation_available_years', self::$ttl, function () {
            return DB::table('liquidations')
                ->select('year')
                ->distinct()
                ->get()
                ->map(function ($row) {
                    return ['value' => $row->year, 'text' => $row->year];
                });
        });
    }
}
