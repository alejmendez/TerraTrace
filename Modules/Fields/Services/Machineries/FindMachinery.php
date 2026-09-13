<?php

namespace Modules\Fields\Services\Machineries;

use Modules\Fields\Models\Machinery;

class FindMachinery
{
    public static function call($id)
    {
        $machinery = Machinery::findOrFail($id);

        return $machinery;
    }
}
