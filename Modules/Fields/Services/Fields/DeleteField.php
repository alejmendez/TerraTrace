<?php

namespace Modules\Fields\Services\Fields;

use Modules\Fields\Models\Field;

class DeleteField
{
    public static function call($id): void
    {
        Field::destroy($id);
    }
}
