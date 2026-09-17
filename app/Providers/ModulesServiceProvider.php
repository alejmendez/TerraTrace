<?php

namespace App\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class ModulesServiceProvider extends ServiceProvider
{
    /**
     * Cached list of provider FQCNs from config/modules.php.
     * Resolved once in register(); reused in boot().
     *
     * @var array<int, string>|null
     */
    private ?array $providers = null;

    /**
     * Register child module providers.
     *
     * Registration happens in register() (not boot()) so every module's
     * service container bindings are available before any boot() runs.
     * The provider list is cached here so boot() doesn't re-read config.
     */
    public function register(): void
    {
        $this->providers = config('modules.providers', []);

        foreach ($this->providers as $provider) {
            $this->app->register($provider);
        }
    }

    /**
     * Bootstrap services.
     *
     * Loads each module's Routes/web.php and Routes/api.php (if present)
     * exactly once. Individual <Xxx>ServiceProvider::boot() methods must
     * NOT load routes themselves — doing so duplicates every route file's
     * work on every boot, even though Laravel dedupes by URI+method.
     *
     * The `api` group is loaded with the standard `/api` URL prefix so
     * routes inside Routes/api.php can be declared without their own prefix.
     */
    public function boot(): void
    {
        $providers = $this->providers ?? config('modules.providers', []);

        foreach ($providers as $provider) {
            if (! preg_match('/Modules\\\\([^\\\\]+)\\\\/', $provider, $matches)) {
                continue;
            }

            $module = $matches[1];
            $base = base_path("Modules/{$module}/Routes");

            // Every route from this module gets the module.active:<Module>
            // middleware applied. If the module row has is_active=false,
            // all routes 404. This closes the gap where deactivating a
            // module only hid its menu entry but left URLs reachable.
            $moduleMiddleware = "module.active:{$module}";

            $web = "{$base}/web.php";
            if (file_exists($web)) {
                Route::middleware(['web', $moduleMiddleware])->group($web);
            }

            $api = "{$base}/api.php";
            if (file_exists($api)) {
                Route::middleware(['api', $moduleMiddleware])->prefix('api')->group($api);
            }
        }
    }
}
