<?php

namespace Modules\Fields\Services\Owners;

use Modules\Fields\Models\Owner;

class CreateOwner
{
    public static function call($data): Owner
    {
        $owner = new Owner;
        $owner->name = $data['name'];
        $owner->dni = $data['dni'];

        $owner->save();

        return $owner;
    }
}
