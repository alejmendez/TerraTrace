<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

/**
 * Scaffold a new module under Modules/<Name>.
 *
 * What it does:
 *   1. Validates the module name (PascalCase, no collisions).
 *   2. Creates the conventional directory layout.
 *   3. Generates stub files: ServiceProvider, Routes (web + api),
 *      Models, Pages, Components, Seeder, Module-migration.
 *   4. Registers the provider in config/modules.php.
 *   5. Runs `php artisan modules:sync` to regenerate vite.config.js
 *      and jsconfig.json from the new module list.
 *
 * Permissions are NOT touched here — add the module's permissions to
 * SyncPermissions.php manually when the CRUD is ready.
 */
class MakeModule extends Command
{
    protected $signature = 'modules:make {module : PascalCase module name (e.g. Reports)}
        {--no-sync : Skip running `php artisan modules:sync` after creation}';

    protected $description = 'Scaffold a new module under Modules/';

    public function handle(): int
    {
        $module = $this->argument('module');

        if (! $this->validate($module)) {
            return self::FAILURE;
        }

        $this->createDirectories($module);
        $this->createFiles($module);
        $this->registerProvider($module);

        if (! $this->option('no-sync')) {
            $this->newLine();
            $this->info('Running modules:sync to update vite.config.js + jsconfig.json...');
            $this->call('modules:sync');
        }

        $this->newLine();
        $this->info("Module [{$module}] scaffolded successfully.");
        $this->line('Next steps:');
        $this->line('  - Run `php artisan migrate` to register the module in the DB.');
        $this->line('  - Declare permissions in app/Console/Commands/SyncPermissions.php.');
        $this->line('  - Define routes in Modules/'.$module.'/Routes/web.php (and api.php if needed).');
        $this->line('  - Add controllers in Modules/'.$module.'/Http/Controllers and pages in Resources/Pages.');

        return self::SUCCESS;
    }

    /**
     * Validate the requested module name. Returns false (and prints why) on failure.
     */
    private function validate(string $module): bool
    {
        if (! preg_match('/^[A-Z][A-Za-z0-9]*$/', $module)) {
            $this->error("Module name must be PascalCase alphanumeric (got: '{$module}').");

            return false;
        }

        if ($module === 'Core') {
            $this->error("'Core' is reserved (already exists as the base provider).");

            return false;
        }

        $existing = config('modules.providers', []);
        foreach ($existing as $fqcn) {
            if (preg_match('/Modules\\\\'.preg_quote($module, '/').'\\\\Providers\\\\/', $fqcn)) {
                $this->error("Module [{$module}] is already registered in config/modules.php.");

                return false;
            }
        }

        if (is_dir(base_path("Modules/{$module}"))) {
            $this->error("Modules/{$module} already exists on disk; refusing to overwrite.");

            return false;
        }

        return true;
    }

    /**
     * Create the conventional directory tree for a module.
     */
    private function createDirectories(string $module): void
    {
        $base = base_path("Modules/{$module}");
        $dirs = [
            'Http/Controllers',
            'Http/Resources',
            'Http/Requests',
            'Models',
            'Database/Factories',
            'Database/Migrations',
            'Database/Seeders',
            'Providers',
            'Config',
            'Resources/Components',
            'Resources/Pages',
            'Routes',
            'Services',
        ];

        foreach ($dirs as $dir) {
            $path = "{$base}/{$dir}";
            File::ensureDirectoryExists($path, 0755);
            $this->line("  dir  Modules/{$module}/{$dir}");
        }
    }

    /**
     * Generate stub files. Existing files are left untouched.
     */
    private function createFiles(string $module): void
    {
        $snake = strtolower(preg_replace('/(?<!^)([A-Z])/', '_$1', $module));

        $timestamp = now()->format('Y_m_d_His');

        $files = [
            // ServiceProvider — extends CoreServiceProvider, no route loading (PR #1 contract).
            "Modules/{$module}/Providers/{$module}ServiceProvider.php" => $this->stubServiceProvider($module),

            // Routes: web.php + api.php. The web.php is loaded by ModulesServiceProvider
            // (PR #1); the api.php will be loaded by the per-module api loader (PR #5).
            "Modules/{$module}/Routes/web.php" => $this->stubWebRoutes(),
            "Modules/{$module}/Routes/api.php" => $this->stubApiRoutes(),

            // Model — minimal Eloquent stub.
            "Modules/{$module}/Models/{$module}.php" => $this->stubModel($module),

            // Migration that inserts a row into the `modules` table.
            "Modules/{$module}/Database/Migrations/{$timestamp}_add_{$snake}_module.php" => $this->stubModuleMigration($module, $snake),

            // Empty seeder.
            "Modules/{$module}/Database/Seeders/{$module}Seeder.php" => $this->stubSeeder($module),

            // Vue pages.
            "Modules/{$module}/Resources/Pages/Index.vue" => $this->stubPage('Index'),
            "Modules/{$module}/Resources/Pages/Create.vue" => $this->stubPage('Create'),
            "Modules/{$module}/Resources/Pages/Edit.vue" => $this->stubPage('Edit'),
            "Modules/{$module}/Resources/Pages/Show.vue" => $this->stubPage('Show'),
            "Modules/{$module}/Resources/Components/Form.vue" => $this->stubPage('Form'),

            // Permission manifest: read by `app:sync-permissions` to
            // register custom permission strings (the default CRUD set
            // is auto-generated from $entities × $defaultActions).
            "Modules/{$module}/Config/permissions.php" => $this->stubPermissions($module),
        ];

        foreach ($files as $path => $content) {
            $full = base_path($path);
            if (File::exists($full)) {
                $this->warn("  skip {$path} (exists)");

                continue;
            }
            File::put($full, $content);
            $this->line("  file {$path}");
        }
    }

    /**
     * Insert the provider FQCN into config/modules.php, preserving the
     * indentation and surrounding array structure.
     *
     * Strategy: find the line that closes the `providers` array (i.e.
     * "    ]," on its own line) and prepend the new entry just before it.
     * This avoids depending on the last existing provider class name.
     */
    private function registerProvider(string $module): void
    {
        $path = config_path('modules.php');
        $original = File::get($path);

        $fqcn = "Modules\\{$module}\\Providers\\{$module}ServiceProvider::class";

        // Match a line that is exactly `<spaces>],` (the closing of an
        // array literal with a trailing comma — i.e. the providers array).
        $updated = preg_replace(
            '/^(\s*\],)$/m',
            '        '.$fqcn.','.PHP_EOL.'$1',
            $original,
            1,
            $count
        );

        if ($count === 0 || $updated === $original) {
            $this->warn('Could not auto-update config/modules.php. Add this line manually:');
            $this->line("    {$fqcn},");

            return;
        }

        File::put($path, $updated);
        $this->info('  config/modules.php updated.');
    }

    private function stubServiceProvider(string $module): string
    {
        return <<<PHP
        <?php

        namespace Modules\\{$module}\\Providers;

        use Modules\\Core\\Providers\\CoreServiceProvider;

        class {$module}ServiceProvider extends CoreServiceProvider
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
             *
             * NOTE: do NOT call Route::middleware('web')->group() here.
             * ModulesServiceProvider loads Modules/<Modulo>/Routes/web.php
             * exactly once. Adding it here duplicates the work on every boot.
             */
            public function boot(): void
            {
                \$this->loadModuleAssets(__DIR__);
            }
        }

        PHP;
    }

    private function stubWebRoutes(): string
    {
        return <<<'PHP'
        <?php

        use Illuminate\Support\Facades\Route;

        /*
        |--------------------------------------------------------------------------
        | Web Routes
        |--------------------------------------------------------------------------
        |
        | Loaded by App\Providers\ModulesServiceProvider with the `web`
        | middleware group. Do NOT add Route::middleware('web')->group()
        | here — it's already applied at the loader level.
        |
        */

        // Route::get('/example', ...)->name('example.index');

        PHP;
    }

    private function stubApiRoutes(): string
    {
        return <<<'PHP'
        <?php

        use Illuminate\Support\Facades\Route;

        /*
        |--------------------------------------------------------------------------
        | API Routes
        |--------------------------------------------------------------------------
        |
        | Loaded by App\Providers\ModulesServiceProvider with the `api`
        | middleware group (Sanctum + rate limiting). PR #5 wires this up.
        |
        */

        // Route::middleware('auth:sanctum')->get('/example', ...);

        PHP;
    }

    private function stubModel(string $module): string
    {
        return <<<PHP
        <?php

        namespace Modules\\{$module}\\Models;

        use Illuminate\\Database\\Eloquent\\Factories\\HasFactory;
        use Illuminate\\Database\\Eloquent\\Model;

        class {$module} extends Model
        {
            use HasFactory;

            protected \$fillable = [
                //
            ];
        }

        PHP;
    }

    private function stubModuleMigration(string $module, string $snake): string
    {
        return <<<PHP
        <?php

        use Illuminate\\Database\\Migrations\\Migration;

        return new class extends Migration
        {
            public function up(): void
            {
                DB::table('modules')->insert([
                    'name' => '{$module}',
                    'slug' => '{$snake}',
                    'description' => '{$module} module',
                    'version' => '1.0.0',
                    'is_active' => true,
                ]);
            }

            public function down(): void
            {
                DB::table('modules')->where('slug', '{$snake}')->delete();
            }
        };

        PHP;
    }

    private function stubSeeder(string $module): string
    {
        return <<<PHP
        <?php

        namespace Modules\\{$module}\\Database\\Seeders;

        use Illuminate\\Database\\Seeder;

        class {$module}Seeder extends Seeder
        {
            public function run(): void
            {
                //
            }
        }

        PHP;
    }

    private function stubPage(string $name): string
    {
        return <<<VUE
        <script setup>
        // {$name} page — implement me.
        </script>

        <template>
          <div>
            <h1>{$name}</h1>
          </div>
        </template>

        VUE;
    }

    private function stubPermissions(string $module): string
    {
        $snake = strtolower(preg_replace('/(?<!^)([A-Z])/', '_$1', $module));

        return <<<PHP
        <?php

        /*
        |--------------------------------------------------------------------------
        | {$module} permissions manifest
        |--------------------------------------------------------------------------
        |
        | Read by `php artisan app:sync-permissions` (see SyncPermissions::save_permissions()).
        | The default CRUD set — <entity>.{index,create,store,show,edit,update,destroy}
        | — is generated automatically from \$entities × \$defaultActions in
        | SyncPermissions. Add here only the *custom* permission strings
        | that don't follow that pattern, e.g. bulk operations or special
        | routes.
        |
        | Example entries to uncomment when implemented:
        |     '{$snake}.download.bulk.template',
        |     '{$snake}.create.bulk',
        |
        */

        return [
            //
        ];

        PHP;
    }
}
