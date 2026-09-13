<?php

namespace Modules\Fields\Services\SecurityEquipments;

use Modules\Fields\Models\SecurityEquipment;

class UpdateSecurityEquipment
{
    public static function call($id, $data): SecurityEquipment
    {
        $security_equipment = SecurityEquipment::findOrFail($id);

        $security_equipment->name = $data['name'];
        $security_equipment->purchase_date = $data['purchase_date'];
        $security_equipment->last_maintenance = $data['last_maintenance'];
        $security_equipment->purchase_location = $data['purchase_location'];
        $security_equipment->type = $data['type'];
        $security_equipment->contact = $data['contact'];
        $security_equipment->note = $data['note'];
        $security_equipment->save();

        return $security_equipment;
    }
}
