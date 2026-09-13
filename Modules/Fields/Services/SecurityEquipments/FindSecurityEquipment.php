<?php

namespace Modules\Fields\Services\SecurityEquipments;

use Modules\Fields\Models\SecurityEquipment;

class FindSecurityEquipment
{
    public static function call($id)
    {
        $security_equipment = SecurityEquipment::findOrFail($id);

        return $security_equipment;
    }
}
