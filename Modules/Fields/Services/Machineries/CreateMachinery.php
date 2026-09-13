<?php

namespace Modules\Fields\Services\Machineries;

use Modules\Fields\Models\Machinery;

class CreateMachinery
{
    public static function call($data): Machinery
    {
        $machinery = new Machinery;

        $machinery->name = $data['name'];
        $machinery->purchase_date = $data['purchase_date'];
        $machinery->last_maintenance = $data['last_maintenance'];
        $machinery->purchase_location = $data['purchase_location'];
        $machinery->type = $data['type'];
        $machinery->contact = $data['contact'];
        $machinery->note = $data['note'];
        $machinery->save();

        return $machinery;
    }
}
