<?php

namespace Modules\Fields\Services\Plants;

use Modules\Fields\Models\PlantDetail;

class CreatePlantNote
{
    public static function call($data): PlantDetail
    {
        PlantDetail::where('plant_id', $data['plant_id'])->where('type', 'note')->update(['is_active' => false]);

        $plantDetail = new PlantDetail;
        $plantDetail->plant_id = $data['plant_id'];
        $plantDetail->type = 'note';
        $plantDetail->value = $data['note'];
        $plantDetail->is_active = true;

        $plantDetail->save();

        return $plantDetail;
    }
}
