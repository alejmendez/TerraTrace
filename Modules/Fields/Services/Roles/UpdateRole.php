<?php

namespace Modules\Fields\Services\Roles;

use Spatie\Permission\Models\Role;

class UpdateRole
{
    public static function call($id, $data): Role
    {
        unset($data['id']);
        $role = Role::findOrFail($id);

        $role->update($data);

        return $role;
    }
}
