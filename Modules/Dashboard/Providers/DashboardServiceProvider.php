<?php

namespace Modules\Dashboard\Providers;

use Modules\Core\Providers\CoreServiceProvider;
use Modules\Dashboard\Services\Dashboard;

class DashboardServiceProvider extends CoreServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Dashboard is a singleton because the only thing it carries is
        // its two stats-provider collaborators (also singletons). No
        // per-request state, no need to rebuild on every resolve.
        $this->app->singleton(Dashboard::class);
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
