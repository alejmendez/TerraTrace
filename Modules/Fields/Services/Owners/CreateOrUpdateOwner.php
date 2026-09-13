<?php

namespace Modules\Fields\Services\Owners;

use Modules\Fields\Models\Owner;

class CreateOrUpdateOwner
{
    public static function call($dni, $name): Owner
    {
        $owner = Owner::updateOrCreate(
            ['dni' => $dni],
            ['name' => $name]
        );

        return $owner;
    }
}
