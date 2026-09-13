<?php

namespace Modules\Fields\Services\Roles;

use Spatie\Permission\Models\Role;

class DeleteRole
{
    public static function call($id): void
    {
        Role::destroy($id);
    }
}
