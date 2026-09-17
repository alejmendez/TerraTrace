<?php

namespace Modules\Core\Providers;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\ServiceProvider;

class CoreServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * Loads Core's own migrations, views (if present), and translations
     * (if present). Child providers inherit `loadModuleAssets(__DIR__)`
     * to do the same for their own module directory — avoiding the
     * "translation path not found" warning when a module lacks Lang/.
     */
    public function boot(): void
    {
        $this->loadModuleAssets(__DIR__);

        $views = __DIR__.'/../Resources/views';
        if (is_dir($views)) {
            $this->loadViewsFrom($views, 'core');
        }

        if ($this->app->runningInConsole()) {
            $this->registerFactoryResolver();
        }
    }

    /**
     * Load migrations and translations from a module's directory tree.
     *
     * Each module has the same conventional layout under
     * `Modules/<Name>/`, so child providers can pass their own
     * `__DIR__` (i.e. the Providers/ folder) and the helper resolves
     * `Database/Migrations` and `Lang` relatively. Both paths are
     * checked with is_dir() so an empty module without translations
     * doesn't log warnings on every boot.
     */
    protected function loadModuleAssets(string $providerDir): void
    {
        $migrations = $providerDir.'/../Database/Migrations';
        if (is_dir($migrations)) {
            $this->loadMigrationsFrom($migrations);
        }

        $lang = $providerDir.'/../Lang';
        if (! is_dir($lang)) {
            return;
        }

        $this->loadTranslationsFrom($lang);
        $this->loadJsonTranslationsFrom($lang);
    }

    /**
     * Register Eloquent's factory name resolvers with closures that know
     * about the project's Modules\<Name>\Database\Factories layout.
     *
     * Two resolvers are needed:
     *   - guessModelNamesUsing() resolves factory → model. Called from
     *     Factory::modelName() which is what `User::factory()->create()`
     *     hits. Bound on Factory::class so it acts as a fallback for
     *     every subclass that doesn't set its own resolver.
     *   - guessFactoryNamesUsing() resolves model → factory. Called from
     *     Factory::factoryForModel(). Less critical for this codebase
     *     but kept for symmetry.
     *
     * The model resolver strips the `Factory` suffix and the
     * `Database\Factories` namespace, then rebuilds the FQCN from the
     * `Modules\<Name>\Models\<Model>` convention.
     */
    protected function registerFactoryResolver(): void
    {
        Factory::guessModelNamesUsing(function (Factory $factory): string {
            $class = $factory::class;

            // Strip the trailing "Factory" suffix to get the model name.
            // E.g. "UserFactory" → "User".
            $basename = class_basename($factory);
            $basename = preg_replace('/Factory$/', '', $basename);

            if (strpos($class, 'Modules\\') === 0) {
                $parts = explode('\\', $class);
                $moduleName = $parts[1];

                return "Modules\\{$moduleName}\\Models\\{$basename}";
            }

            return "App\\Models\\{$basename}";
        });

        Factory::guessFactoryNamesUsing(function (string $modelName): string {
            if (strpos($modelName, 'Modules\\') === 0) {
                $parts = explode('\\', $modelName);
                $moduleName = $parts[1];

                return "Modules\\{$moduleName}\\Database\\Factories\\".class_basename($modelName).'Factory';
            }

            return 'Database\\Factories\\'.class_basename($modelName).'Factory';
        });
    }
}
