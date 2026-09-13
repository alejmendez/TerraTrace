<?php

namespace Modules\Fields\Services\Plants;

use Modules\Fields\Models\Plant;

class CreatePlant
{
    public static function call($data): Plant
    {
        $plant = new Plant;

        $plant->quarter_id = $data['quarter_id']['value'];
        $plant->code = $data['code'];
        $plant->row = $data['row'];
        $plant->plant_type_id = $data['plant_type_id']['value'];
        $plant->age = 0;
        $plant->planned_at = $data['planned_at'];
        $plant->nursery_origin = $data['nursery_origin'];

        $plant->save();

        return $plant;
    }
}
