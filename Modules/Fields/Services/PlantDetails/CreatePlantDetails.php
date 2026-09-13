<?php

namespace Modules\Fields\Services\PlantDetails;

use Modules\Fields\Models\PlantDetail;
use Modules\Fields\Services\Plants\FindPlantByCode;

class CreatePlantDetails
{
    public static function call(array $data)
    {
        $plant = FindPlantByCode::call($data['plant_code']);

        if (! $plant) {
            throw new \Exception('Planta no encontrada con el código proporcionado.');
        }

        $activeDetails = $plant->activeDetails()->get()->keyBy('type');

        $detailsToProcess = [
            'height',
            'crown_diameter',
            'invasion_radius',
            'trunk_diameter',
            'root_diameter',
            'foliage_sanitation',
            'foliage_sanitation_photo',
            'trunk_sanitation',
            'trunk_sanitation_photo',
            'soil_sanitation',
            'soil_sanitation_photo',
            'irrigation_system',
            // 'note',
        ];

        $detailsToCreate = [];
        $typesToDeactivate = [];

        foreach ($detailsToProcess as $type) {
            if (! isset($data[$type]) || is_null($data[$type])) {
                continue;
            }

            if (isset($activeDetails[$type])) {
                $typesToDeactivate[] = $activeDetails[$type]->id;
            }

            $detailsToCreate[] = [
                'plant_id' => $plant->id,
                'type' => $type,
                'value' => $data[$type],
                'note' => $data['notes'][$type] ?? null,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        if (! empty($typesToDeactivate)) {
            PlantDetail::whereIn('id', $typesToDeactivate)->update(['is_active' => false]);
        }

        if (! empty($detailsToCreate)) {
            PlantDetail::insert($detailsToCreate);
        }
    }
}
