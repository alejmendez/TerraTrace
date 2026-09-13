<?php

namespace Modules\Core\Services;

use Illuminate\Support\Str;
use Modules\Users\Models\User;

class CacheService
{
    protected static int $userDataTtl = 600; // 10 minutes

    protected static int $unreadNotificationsTtl = 60; // 1 minute

    protected static int $menuTtl = 1; // 86_400; // 24 hours

    public static function getUserDataSession(User $user)
    {
        return cache()->remember('user_'.$user->id.'_data_session', self::$userDataTtl, function () use ($user) {
            return [
                'id' => $user->id,
                'full_name' => $user->full_name,
                'avatar_url' => self::getUserAvatar($user),
                'roles' => self::getUserRoles($user),
                'permissions' => self::getUserPermissions($user),
                'unread_notifications' => self::getUserUnreadNotifications($user),
            ];
        });
    }

    public static function getUserDataSessionAjax(User $user)
    {
        return cache()->remember('user_'.$user->id.'_data_session_ajax', self::$userDataTtl, function () use ($user) {
            return [
                'id' => $user->id,
                'full_name' => $user->full_name,
                'avatar_url' => self::getUserAvatar($user),
                'unread_notifications' => self::getUserUnreadNotifications($user),
            ];
        });
    }

    public static function getUserAvatar(User $user)
    {
        return cache()->remember('user_'.$user->id.'_avatar', self::$userDataTtl, function () use ($user) {
            return $user->avatar_url;
        });
    }

    public static function getUserRoles(User $user)
    {
        return cache()->remember('user_'.$user->id.'_roles', self::$userDataTtl, function () use ($user) {
            return collect($user->getRoleNames())->map(fn ($roleName) => Str::slug($roleName));
        });
    }

    public static function getUserPermissions(User $user)
    {
        return cache()->remember('user_'.$user->id.'_permissions', self::$userDataTtl, function () use ($user) {
            return $user->getAllPermissions()->pluck('name')->map(fn ($permissionName) => $permissionName);
        });
    }

    public static function getUserUnreadNotifications(User $user)
    {
        return cache()->remember('user_'.$user->id.'_unreadnotifications', self::$unreadNotificationsTtl, function () use ($user) {
            return $user->unreadNotifications->where('type', '!=', 'Modules\Tasks\Notifications\TaskNotification');
        });
    }

    public static function clearUserCache(User $user)
    {
        self::clearUserCacheById($user->id);
    }

    public static function getMenu(User $user)
    {
        return cache()->remember('menu_'.$user->id.'_1', self::$menuTtl, function () use ($user) {
            $menuService = new MenuService($user);

            return $menuService->getMenu();
        });
    }

    public static function clearUserCacheById($userId)
    {
        cache()->forget('menu_'.$userId);
        cache()->forget('user_'.$userId);
        cache()->forget('user_'.$userId.'_avatar');
        cache()->forget('user_'.$userId.'_roles');
        cache()->forget('user_'.$userId.'_permissions');
        cache()->forget('user_'.$userId.'_unreadnotifications');
        cache()->forget('user_'.$userId.'_data_session');
        cache()->forget('user_'.$userId.'_data_session_ajax');
    }
}
