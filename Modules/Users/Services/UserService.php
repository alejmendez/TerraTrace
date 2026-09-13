<?php

namespace Modules\Users\Services;

use Illuminate\Support\Facades\Hash;
use Modules\Core\Services\CacheService;
use Modules\Core\Services\PrimevueDatatables;
use Modules\Users\Models\User;

class UserService
{
    private const SEARCHABLE_COLUMNS = ['full_name', 'dni', 'phone', 'roles.name', 'email'];

    public function list($params = [])
    {
        $query = User::query();

        $datatable = new PrimevueDatatables($params, self::SEARCHABLE_COLUMNS);
        $users = $datatable->of($query)->make();

        return $users;
    }

    public function find($id): User
    {
        return User::with('roles')->findOrFail($id);
    }

    public function create(array $data): User
    {
        $user = new User;
        $user->name = $data['name'];
        $user->last_name = $data['last_name'];
        $user->dni = $data['dni'];
        $user->email = $data['email'];
        $user->phone = $data['phone'];
        $user->avatar = $data['avatar'];
        $user->password = Hash::make($data['password']);
        $user->save();

        if (isset($data['role'])) {
            $user->syncRoles([$data['role']]);
        }

        CacheService::clearUserCache($user);

        return $user;
    }

    public function update($id, array $data): User
    {
        $user = User::findOrFail($id);

        $user->name = $data['name'];
        $user->last_name = $data['last_name'];
        $user->dni = $data['dni'];
        $user->email = $data['email'];
        $user->phone = $data['phone'];

        if ($data['avatar']) {
            $user->avatar = $data['avatar'];
        }

        if ($data['avatarRemove'] === '1') {
            $user->avatar = null;
        }

        if ($data['password'] != '') {
            $user->password = Hash::make($data['password']);
        }

        if ($user->email !== $data['email']) {
            $user->email_verified_at = null;
        }

        $user->save();

        if (isset($data['role'])) {
            $user->syncRoles([$data['role']]);
        }

        CacheService::clearUserCache($user);

        return $user;
    }

    public function delete($id): void
    {
        CacheService::clearUserCacheById($id);
        User::destroy($id);
    }
}
