<?php

namespace Modules\Fields\Services\HarvestDetails;

use Illuminate\Support\Str;
use Modules\Fields\Models\Harvest;
use Modules\Fields\Models\HarvestDetail;
use Modules\Fields\Services\Plants\FindPlantByCode;

class CreateHarvestDetails
{
    public static function call($data): HarvestDetail
    {
        $plant = FindPlantByCode::call($data['plant_code']);

        $harvest = new HarvestDetail;
        $harvest->harvest_id = Harvest::latest()->first()->id;
        $harvest->quarter_id = $plant->quarter_id;
        $harvest->plant_id = $plant->id;
        $harvest->quality = Str::slug($data['quality']['value'] ?? '');
        $harvest->weight = $data['weight'];
        $harvest->save();

        return $harvest;
    }
}
