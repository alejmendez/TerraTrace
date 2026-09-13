<?php

namespace Modules\Users\Providers;

use Illuminate\Support\Facades\Route;
use Modules\Core\Providers\CoreServiceProvider;
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
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');
        $this->loadTranslationsFrom(__DIR__.'/../Lang');
        $this->loadJsonTranslationsFrom(__DIR__.'/../Lang');
        Route::middleware('web')->group(base_path('Modules/Users/Routes/web.php'));
    }
}
