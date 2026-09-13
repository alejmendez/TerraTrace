<?php

namespace Modules\Dashboard\Providers;

use Illuminate\Support\Facades\Route;
use Modules\Core\Providers\CoreServiceProvider;

class DashboardServiceProvider extends CoreServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');
        $this->loadTranslationsFrom(__DIR__.'/../Lang');
        $this->loadJsonTranslationsFrom(__DIR__.'/../Lang');
        Route::middleware('web')->group(base_path('Modules/Dashboard/Routes/web.php'));
    }
}
