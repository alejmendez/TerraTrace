<?php

namespace Modules\Fields\Providers;

use Modules\Core\Providers\CoreServiceProvider;
use Modules\Fields\Services\FieldService;
use Modules\Fields\Services\OwnerService;
use Modules\Fields\Services\PlantService;

class FieldsServiceProvider extends CoreServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // FieldService and OwnerService are singletons: both are stateless
        // and only carry collaborators (also singletons). Explicit
        // registration keeps the wiring visible and prevents Laravel from
        // rebuilding the dependency graph on every resolve.
        $this->app->singleton(FieldService::class);
        $this->app->singleton(OwnerService::class);
        $this->app->singleton(PlantService::class);

        EntityRegistrations::register();
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
