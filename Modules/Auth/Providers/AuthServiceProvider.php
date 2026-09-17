<?php

namespace Modules\Auth\Providers;

use Modules\Auth\Services\AuthService;
use Modules\Core\Providers\CoreServiceProvider;

class AuthServiceProvider extends CoreServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(AuthService::class);
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
