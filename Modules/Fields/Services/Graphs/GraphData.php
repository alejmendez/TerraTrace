<?php

namespace Modules\Fields\Services\Graphs;

use Illuminate\Support\Str;

class GraphData
{
    public static function call($id, $year, $type, $filters)
    {
        $typeStr = Str::of($type);

        if ($typeStr->startsWith('field-')) {
            return GraphDataField::call($id, $year, $type, $filters);
        } elseif ($typeStr->startsWith('quarter-')) {
            return GraphDataQuarter::call($id, $year, $type, $filters);
        } elseif ($typeStr->startsWith('plant-')) {
            return GraphDataPlant::call($id, $year, $type, $filters);
        }
    }
}
