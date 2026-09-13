<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;
use Modules\Core\Services\CacheService;
use Modules\Core\Services\MenuService;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'core::app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();
        $userData = $user;

        if ($user) {
            $userData = CacheService::getUserDataSession($user);
        }

        $menuService = new MenuService($request, $user);
        $menu = $menuService->getMenu();

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $userData,
            ],
            'menu' => $menu,
        ];
    }
}
