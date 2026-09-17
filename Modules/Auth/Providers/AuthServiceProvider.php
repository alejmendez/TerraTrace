<?php

namespace Modules\Auth\Providers;

use Modules\Auth\Services\AuthService;
use Modules\Core\Providers\CoreServiceProvider;
use Modules\Core\Registry\EntityRegistry;
use Spatie\Permission\Models\Role;

class AuthServiceProvider extends CoreServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(AuthService::class);

        // Auth module exposes the `role` named entity via EntityRegistry
        // so the SelectsController HTTP endpoint (/api/selects/{entity})
        // can resolve it from a string. The same list also lives on
        // AuthService::roles() per AGENTS.md section 4 — Laravel
        // controllers (UsersController, ProfileController) use the
        // service method directly.
        //
        // When SelectsController is rewritten or removed (e.g. once
        // the frontend uses per-module REST endpoints), this
        // registration can go away.
        EntityRegistry::register('role', Role::class, static fn () => Role::select('name as value', 'name as text')->orderBy('name')->get());
    }

    /**
     * Bootstrap services.
     *
     * NOTE: do NOT call Route::middleware('web')->group() here.
     * ModulesServiceProvider loads Modules/<Modulo>/Routes/web.php
     * exactly once. Adding it here duplicates the work on every boot.
     */
    public function boot(): void
    {
        $this->loadModuleAssets(__DIR__);
    }
}
