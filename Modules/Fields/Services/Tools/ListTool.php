<?php

namespace Modules\Fields\Services\Tools;

use Modules\Core\Services\PrimevueDatatables;
use Modules\Fields\Models\Tool;

class ListTool
{
    public static function call($params = [])
    {
        $searchableColumns = ['name', 'purchase_date', 'last_maintenance', 'purchase_location', 'contact'];

        $query = Tool::query();

        $datatable = new PrimevueDatatables($params, $searchableColumns);
        $plants = $datatable->of($query)->make();

        return $plants;
    }
}
