<?php

namespace Modules\Fields\Services\Quarters;

use Modules\Fields\Models\Quarter;

class FindQuarter
{
    public static function call($id)
    {
        $quarter = Quarter::with('responsible', 'field')
            ->withCount('plants')
            ->findOrFail($id);

        return $quarter;
    }
}
