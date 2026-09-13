<?php

namespace Modules\Fields\Services\PlantTypes;

use Illuminate\Support\Str;
use Modules\Fields\Models\PlantType;

class CreatePlantType
{
    public static function call($data): PlantType
    {
        $slug = Str::slug($data['name']);
        $type = PlantType::where('slug', $slug)->first();
        if (! $type) {
            $type = new PlantType;
            $type->name = $data['name'];
            $type->slug = $slug;
            $type->save();
        }

        return $type;
    }
}
