<?php

namespace Modules\Fields\Services\Plants;

use Modules\Core\Services\PrimevueDatatables;
use Modules\Fields\Models\Plant;

class ListPlant
{
    public static function call($params = [])
    {
        $searchableColumns = ['plants.code', 'quarter.name', 'quarter.field.name', 'plant_type.name', 'plants.age', 'quarter.responsible.full_name'];

        $query = Plant::query();

        $datatable = new PrimevueDatatables($params, $searchableColumns);
        $plants = $datatable->of($query)->make();

        return $plants;
    }
}
