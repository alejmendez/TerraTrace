<?php

namespace App\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class ModulesServiceProvider extends ServiceProvider
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
        $providers = config('modules.providers');

        foreach ($providers as $provider) {
            $this->app->register($provider);

            // Extraer el nombre del módulo del namespace del provider
            if (preg_match('/Modules\\\\([^\\\\]+)\\\\/', $provider, $matches)) {
                $moduleName = $matches[1];
                $routePath = "Modules/{$moduleName}/Routes/web.php";

                // Verificar si existe el archivo de rutas y cargarlo
                if (file_exists(base_path($routePath))) {
                    Route::middleware('web')
                        ->group(base_path($routePath));
                }
            }
        }
    }
}
