<?php

namespace Modules\Fields\Services\SecurityEquipments;

use Modules\Fields\Models\SecurityEquipment;

class DeleteSecurityEquipment
{
    public static function call($id): void
    {
        SecurityEquipment::destroy($id);
    }
}
