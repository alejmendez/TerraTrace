<?php

namespace Modules\Users\Services;

use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Modules\Core\Services\CacheService;
use Modules\Users\Models\User;

class UserService
{
    public function find($id): User
    {
        return User::with('roles')->findOrFail($id);
    }

    /**
     * Flat {value, text} list for cross-module consumers and the
     * SelectsController HTTP endpoint. Same shape for `user`,
     * `responsible`, and `couple` (the three named aliases the
     * dispatcher maps to this single method).
     */
    public function forSelect(): array
    {
        return User::select('id as value', 'full_name as text')
            ->orderBy('full_name')
            ->get()
            ->toArray();
    }

    /**
     * Public self-registration: minimal fields, fires the framework
     * `Registered` event so the email-verification listener can pick it up.
     *
     * `Auth::login()` deliberately NOT called here — that is HTTP-flow
     * specific and belongs in the controller.
     */
    public function register(array $data): User
    {
        $user = new User;
        $user->name = $data['name'];
        $user->email = $data['email'];
        $user->password = Hash::make($data['password']);
        $user->save();

        event(new Registered($user));

        CacheService::clearUserCache($user);

        return $user;
    }

    public function collection(array $params = []): array
    {
        $query = User::query()
            ->select('users.id', 'users.name', 'users.last_name', 'users.full_name', 'users.email', 'users.dni', 'users.phone', 'users.avatar')
            ->with(['roles:id,name']);

        $search = trim($params['q'] ?? '');

        if ($search !== '') {
            $query->where(function ($builder) use ($search) {
                $builder
                    ->where('users.full_name', 'like', "%{$search}%")
                    ->orWhere('users.dni', 'like', "%{$search}%")
                    ->orWhere('users.phone', 'like', "%{$search}%")
                    ->orWhere('users.email', 'like', "%{$search}%")
                    ->orWhereHas('roles', function ($roleQuery) use ($search) {
                        $roleQuery->where('name', 'like', "%{$search}%");
                    });
            });
        }

        if (! empty($params['role'])) {
            $query->whereHas('roles', function ($roleQuery) use ($params) {
                $roleQuery->where('name', $params['role']);
            });
        }

        $summary = [
            'users' => (clone $query)->count(),
            'roles' => (clone $query)->whereHas('roles')->count(),
        ];

        $sort = in_array($params['sort'] ?? '', ['full_name', 'dni', 'phone', 'email'], true)
            ? $params['sort']
            : 'full_name';
        $direction = ($params['direction'] ?? 'asc') === 'desc' ? 'desc' : 'asc';
        $perPage = min(max((int) ($params['per_page'] ?? 12), 1), 24);
        $page = max((int) ($params['page'] ?? 1), 1);

        $paginator = $query
            ->orderBy("users.{$sort}", $direction)
            ->paginate($perPage, ['*'], 'page', $page);

        $items = collect($paginator->items())->map(function ($user) {
            $firstRole = $user->roles->first();

            $user->role = $firstRole ? [
                'id' => $firstRole->id,
                'name' => $firstRole->name,
                'slug' => Str::slug($firstRole->name),
            ] : null;

            return $user;
        })->all();

        return [
            'items' => $items,
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'from' => $paginator->firstItem() ?? 0,
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'to' => $paginator->lastItem() ?? 0,
                'total' => $paginator->total(),
            ],
            'summary' => $summary,
        ];
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
        // last_name / dni / phone are nullable in the schema; use null
        // coalescing so the service tolerates partial updates (e.g. the
        // Breeze-style profile tests that only send name + email).
        $user->last_name = $data['last_name'] ?? null;
        $user->dni = $data['dni'] ?? null;
        $user->email = $data['email'];
        $user->phone = $data['phone'] ?? null;

        if (! empty($data['avatar'])) {
            $user->avatar = $data['avatar'];
        }

        if (($data['avatarRemove'] ?? null) === '1') {
            $user->avatar = null;
        }

        if (! empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }

        if ($user->getOriginal('email') !== $data['email']) {
            $user->email_verified_at = null;
        }

        $user->save();

        if (isset($data['role'])) {
            $user->syncRoles([$data['role']]);
        }

        CacheService::clearUserCache($user);

        return $user;
    }

    /**
     * Update a user's password and invalidate every cache that depends
     * on the password hash.
     *
     * IMPORTANT: must invalidate the user cache after the update. The
     * cached `CachedAuthUserProvider` entry (Modules\\Users model) lives
     * for 2 hours (TTL aligned with CacheService::getUserDataSession at
     * 600s after commit d7f84c5) and would otherwise serve the OLD
     * password hash until it expires, breaking downstream `Hash::check()`
     * on the cached instance. `CacheService::clearUserCache()` also
     * drops the user data session, menu and unread-notifications caches.
     *
     * Caller is expected to validate `current_password` before invoking
     * this method (the standard Laravel `current_password` rule is the
     * idiomatic way).
     */
    public function updatePassword(User $user, string $newPassword): void
    {
        $user->password = Hash::make($newPassword);
        $user->save();

        CacheService::clearUserCache($user);
    }

    public function delete($id): void
    {
        CacheService::clearUserCacheById($id);
        User::destroy($id);
    }
}
