<?php

namespace Modules\Fields\Services\PlantTypes;

use Modules\Fields\Models\PlantType;

class FindPlantType
{
    public static function call($id)
    {
        $type = PlantType::findOrFail($id);

        return $type;
    }
}
