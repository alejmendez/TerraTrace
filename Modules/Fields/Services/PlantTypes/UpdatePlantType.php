<?php

namespace Modules\Fields\Services\PlantTypes;

use Illuminate\Support\Str;
use Modules\Fields\Models\PlantType;

class UpdatePlantType
{
    public static function call($id, $data): PlantType
    {
        $slug = Str::slug($data['name']);

        $type = PlantType::findOrFail($id);
        $type->name = $data['name'];
        $type->slug = $slug;
        $type->save();

        return $type;
    }
}
