<?php

namespace Modules\Auth\Services;

use Spatie\Permission\Models\Role;

/**
 * Auth-owned operations.
 *
 * Created during the ListEntity migration (each module owns its own
 * lists per AGENTS.md section 4). At the moment this only exposes
 * `roles()` for the role <select>, but new Auth-domain operations
 * (e.g. token lifecycle helpers, MFA, socialite redirects) belong
 * here too once they appear.
 *
 * Cross-module auth (login/register flows) stays in the existing
 * Auth controllers — UserService handles the User-domain side of
 * registration/password update per the previous refactor.
 */
class AuthService
{
    /**
     * Roles as {value, text} pairs for <select> options.
     *
     * Uses the role name as both the value and the displayed text
     * (matches the original EntityRegistry registration).
     */
    public function roles(): array
    {
        return Role::select('name as value', 'name as text')
            ->orderBy('name')
            ->get()
            ->toArray();
    }
}
