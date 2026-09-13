<?php

namespace Modules\Fields\Services\Owners;

use Modules\Fields\Models\Owner;

class FindOwner
{
    public static function call($id)
    {
        $owner = Owner::findOrFail($id);

        return $owner;
    }
}
