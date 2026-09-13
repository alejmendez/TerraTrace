<?php

namespace Modules\Fields\Services\Dogs;

use Modules\Fields\Models\Dog;

class DeleteDog
{
    public static function call($id): void
    {
        Dog::destroy($id);
    }
}
