<?php

namespace Modules\Fields\Services\Liquidations;

use Modules\Fields\Models\Liquidation;

class DeleteLiquidation
{
    public static function call($id): void
    {
        Liquidation::destroy($id);
    }
}
