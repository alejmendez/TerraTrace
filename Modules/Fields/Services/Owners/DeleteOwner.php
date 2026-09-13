<?php

namespace Modules\Fields\Services\Owners;

use Modules\Fields\Models\Owner;

class DeleteOwner
{
    public static function call($id): void
    {
        Owner::destroy($id);
    }
}
