<?php

namespace Modules\Fields\Services\Plants;

use Modules\Fields\Models\Plant;

class FindPlant
{
    public static function call($id)
    {
        $plant = Plant::findOrFail($id);

        return $plant;
    }
}
