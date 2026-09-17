<?php

namespace Modules\Users\Services\Users;

use Illuminate\Support\Str;
use Modules\Core\Services\PrimevueDatatables;
use Modules\Users\Models\User;

class ListUser
{
    private const SEARCHABLE_COLUMNS = ['full_name', 'dni', 'phone', 'roles.name', 'email'];

    public static function call($params = [])
    {
        $query = User::query();

        $datatable = new PrimevueDatatables($params, self::SEARCHABLE_COLUMNS);
        $users = $datatable->of($query)->make();

        return $users;
    }

    public static function collection(array $params = []): array
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
}
