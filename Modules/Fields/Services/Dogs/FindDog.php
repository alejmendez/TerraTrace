<?php

namespace Modules\Fields\Services\Dogs;

use Modules\Fields\Models\Dog;

class FindDog
{
    public static function call($id)
    {
        $dog = Dog::findOrFail($id);

        return $dog;
    }
}
