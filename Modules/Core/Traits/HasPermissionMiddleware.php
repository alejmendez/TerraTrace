<?php

namespace Modules\Core\Traits;

use Modules\Core\Exceptions\PermissionException;
use Modules\Core\Services\CacheService;

trait HasPermissionMiddleware
{
    protected function setupPermissionMiddleware()
    {
        $this->middleware(function ($request, $next) {
            $routeName = $request->route()->getName();
            $user = auth()->user();

            $userPermissions = CacheService::getUserPermissions($user);

            if (! $userPermissions->contains($routeName)) {
                throw new PermissionException($routeName);
            }

            return $next($request);
        });
    }
}
