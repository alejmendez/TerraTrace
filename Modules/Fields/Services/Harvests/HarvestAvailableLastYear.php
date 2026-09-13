<?php

namespace Modules\Fields\Services\Harvests;

use Modules\Fields\Models\Field;
use Modules\Fields\Models\Harvest;

class HarvestAvailableLastYear
{
    protected static $ttl = 60 * 60 * 24; // 1 day

    public static function call()
    {
        return cache()->remember('last_years_harvest', self::$ttl, function () {
            $current_year = (int) date('Y');
            $year = Harvest::max('year');

            return $year > $current_year ? $current_year : $year;
        });
    }
}
