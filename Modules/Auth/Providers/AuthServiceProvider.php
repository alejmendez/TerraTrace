<?php

namespace Modules\Auth\Providers;

use Modules\Core\Providers\CoreServiceProvider;
use Modules\Core\Registry\EntityRegistry;
use Spatie\Permission\Models\Role;

class AuthServiceProvider extends CoreServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Roles come from Spatie/Permission; Auth module owns them.
        EntityRegistry::register('role', Role::class, static fn () => Role::select('name as value', 'name as text')->orderBy('name')->get());
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
