<?php

namespace Modules\Fields\Services\Harvests;

use Modules\Fields\Models\Harvest;

class FindHarvest
{
    public static function call($id)
    {
        $harvest = Harvest::findOrFail($id);

        return $harvest;
    }
}
