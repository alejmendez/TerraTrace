<?php

namespace Modules\Fields\Services\Harvests;

use Modules\Fields\Models\Harvest;

class DeleteHarvest
{
    public static function call($id): void
    {
        Harvest::destroy($id);
    }
}
