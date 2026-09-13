<?php

namespace Modules\Fields\Services\Machineries;

use Modules\Fields\Models\Machinery;

class DeleteMachinery
{
    public static function call($id): void
    {
        Machinery::destroy($id);
    }
}
