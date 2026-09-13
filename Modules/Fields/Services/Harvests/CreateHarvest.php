<?php

namespace Modules\Fields\Services\Harvests;

use Modules\Fields\Models\Harvest;

class CreateHarvest
{
    public static function call($data): Harvest
    {
        $harvest = new Harvest;

        $harvest->date = $data['date'];
        $harvest->batch = strtoupper($data['batch']);
        $harvest->dog_id = $data['dog_id']['value'];
        $harvest->farmer_id = $data['farmer_id']['value'];
        $harvest->assistant_id = $data['assistant_id']['value'];
        $harvest->weight = $data['weight'];
        $harvest->note = $data['note'];
        $harvest->save();

        $quarter_ids = [];
        foreach ($data['quarter_ids'] as $option) {
            $quarter_ids[] = $option['value'];
        }
        $harvest->quarters()->attach($quarter_ids);

        return $harvest;
    }
}
