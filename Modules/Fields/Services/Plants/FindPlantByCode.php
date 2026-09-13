<?php

namespace Modules\Fields\Services\Plants;

use Modules\Fields\Models\Plant;

class FindPlantByCode
{
    public static function call($code)
    {
        return Plant::firstWhere('code', trim(strtoupper($code)));
    }
}
