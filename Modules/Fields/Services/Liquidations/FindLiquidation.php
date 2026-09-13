<?php

namespace Modules\Fields\Services\Liquidations;

use Modules\Fields\Models\Liquidation;

class FindLiquidation
{
    public static function call($id)
    {
        $liquidation = Liquidation::findOrFail($id);

        return $liquidation;
    }
}
