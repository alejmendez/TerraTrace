<?php

namespace Modules\Fields\Providers;

use Modules\Core\Providers\CoreServiceProvider;

class FieldsServiceProvider extends CoreServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
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
