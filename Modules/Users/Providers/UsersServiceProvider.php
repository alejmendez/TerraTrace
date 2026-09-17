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

        // Bare model-class mapping consumed by cross-module
        // belongsTo relations (see Modules\Tasks\Models\Task::responsible()).
        // The data-shape lookups (`user`, `responsible`, `couple`) are
        // served by EntityDispatcher → UserService::forSelect().
        EntityRegistry::register('user', User::class);
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
