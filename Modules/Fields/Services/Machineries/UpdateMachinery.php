<?php

namespace Modules\Fields\Services\Machineries;

use Modules\Fields\Models\Machinery;

class UpdateMachinery
{
    public static function call($id, $data): Machinery
    {
        $machinery = Machinery::findOrFail($id);

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
