<?php

namespace Modules\Fields\Services\PlantTypes;

use Modules\Fields\Models\PlantType;

class DeletePlantType
{
    public static function call($id): void
    {
        PlantType::destroy($id);
    }
}
