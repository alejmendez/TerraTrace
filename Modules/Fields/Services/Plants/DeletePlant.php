<?php

namespace Modules\Fields\Services\Plants;

use Modules\Fields\Models\Plant;

class DeletePlant
{
    public static function call($id): void
    {
        Plant::destroy($id);
    }
}
