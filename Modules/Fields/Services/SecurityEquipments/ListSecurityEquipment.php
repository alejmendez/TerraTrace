<?php

namespace Modules\Fields\Services\SecurityEquipments;

use Modules\Core\Services\PrimevueDatatables;
use Modules\Fields\Models\SecurityEquipment;

class ListSecurityEquipment
{
    public static function call($params = [])
    {
        $searchableColumns = ['name', 'purchase_date', 'last_maintenance', 'purchase_location', 'contact'];

        $query = SecurityEquipment::query();

        $datatable = new PrimevueDatatables($params, $searchableColumns);
        $plants = $datatable->of($query)->make();

        return $plants;
    }
}
