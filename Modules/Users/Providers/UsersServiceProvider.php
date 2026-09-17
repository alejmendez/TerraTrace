<?php

namespace Modules\Users\Providers;

use Modules\Core\Providers\CoreServiceProvider;
use Modules\Core\Registry\EntityRegistry;
use Modules\Users\Models\User;
use Modules\Users\Services\UserService;

class UsersServiceProvider extends CoreServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(UserService::class, function ($app) {
            return new UserService;
        });

        // Users module owns these named entities. Registering here means
        // ListEntity::call('user') resolves via the registry without
        // Core having to import the User model.
        EntityRegistry::register('user', User::class, static fn () => User::select('id as value', 'full_name as text')->orderBy('full_name'));
        EntityRegistry::register('responsible', User::class, static fn () => User::select('id as value', 'full_name as text')->orderBy('full_name'));
        EntityRegistry::register('couple', User::class, static fn () => User::select('id as value', 'full_name as text')->orderBy('full_name'));

        // Roles come from Spatie; Auth module registers them (it owns
        // authentication).
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
