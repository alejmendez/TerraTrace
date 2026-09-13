<?php

namespace Modules\Tasks\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class TasksServiceProvider extends ServiceProvider
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
        $this->mergeConfigFrom(__DIR__.'/../Config/tasks.php', 'tasks');
        Route::middleware('web')->group(base_path('Modules/Tasks/Routes/web.php'));
    }
}
