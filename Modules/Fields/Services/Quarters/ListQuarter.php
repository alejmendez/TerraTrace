<?php

namespace Modules\Fields\Services\Quarters;

use Modules\Core\Services\PrimevueDatatables;
use Modules\Fields\Models\Quarter;

class ListQuarter
{
    public static function call($params = [])
    {
        $query = Quarter::leftJoin('fields', 'quarters.field_id', '=', 'fields.id')
            ->select('quarters.id', 'quarters.name', 'fields.name as field_name', 'quarters.area')
            ->withCount('plants');
        $searchableColumns = ['quarters.name', 'field.name', 'quarters.area'];
        $datatable = new PrimevueDatatables($params, $searchableColumns);
        $quarters = $datatable->of($query)->make();

        return $quarters;
    }
}
