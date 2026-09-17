<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Modules\Core\Models\Module;
use Symfony\Component\HttpFoundation\Response;

/**
 * Reject requests for a module that has is_active=false in the database.
 *
 * Applied automatically by ModulesServiceProvider to every per-module
 * route group (`Modules/<Name>/Routes/{web,api}.php`). The module name
 * is derived from the provider FQCN, not from the URL, so this works
 * regardless of how routes are prefixed inside the module.
 *
 * Lookup is cached for one minute: toggling a module's active flag
 * takes effect within 60 seconds without a `php artisan cache:clear`.
 * The cache is keyed by `module:is_active:<slug>` to stay compatible
 * with MenuService::getModules() (which can be invalidated in tandem).
 */
class ModuleActive
{
    public function handle(Request $request, Closure $next, string $module): Response
    {
        $isActive = cache()->remember(
            "module:is_active:{$module}",
            now()->addMinute(),
            fn () => (bool) Module::where('slug', strtolower($module))->value('is_active')
        );

        if (! $isActive) {
            // 404 (not 403) — the module doesn't exist as far as this
            // request is concerned, mirroring the menu's behaviour.
            abort(404);
        }

        return $next($request);
    }
}
