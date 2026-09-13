<?php

namespace Modules\Fields\Services\Owners;

use Modules\Fields\Models\Owner;

class UpdateOwner
{
    public static function call($id, $data): Owner
    {
        $owner = Owner::findOrFail($id);

        $owner->name = $data['name'];
        $owner->dni = $data['dni'];
        $owner->save();

        return $owner;
    }
}
