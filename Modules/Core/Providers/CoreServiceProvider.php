<?php

namespace Modules\Core\Providers;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class CoreServiceProvider extends ServiceProvider
{
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
        $this->loadViewsFrom(__DIR__.'/../Resources/views', 'core');
        $this->loadTranslationsFrom(__DIR__.'/../Lang');
        $this->loadJsonTranslationsFrom(__DIR__.'/../Lang');
        Route::middleware('web')->group(base_path('Modules/Core/Routes/web.php'));

        if ($this->app->runningInConsole()) {
            Factory::guessFactoryNamesUsing(function (string $modelName) {
                if (strpos($modelName, 'Modules\\') === 0) {
                    $parts = explode('\\', $modelName);
                    $moduleName = $parts[1];

                    return "Modules\\{$moduleName}\\Database\\Factories\\".class_basename($modelName).'Factory';
                }

                return 'Database\\Factories\\'.class_basename($modelName).'Factory';
            });
        }
    }
}
