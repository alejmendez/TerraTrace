<?php

namespace Modules\Fields\Services\Quarters;

use Modules\Fields\Models\Quarter;

class DeleteQuarter
{
    public static function call($id): void
    {
        Quarter::destroy($id);
    }
}
