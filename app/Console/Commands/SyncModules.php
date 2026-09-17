<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

/**
 * Keeps vite.config.js, jsconfig.json, and package.json in sync with
 * config/modules.php.
 *
 * Source of truth: config/modules.php#providers. The module list is derived
 * from each provider FQCN's first namespace segment after "Modules\\".
 * The order of providers in config/modules.php is preserved in all files.
 *
 * What gets regenerated:
 *   - vite.config.js  → `const modules = [...]` array (used for Vite aliases
 *                       and laravel-vue-i18n additionalLangPaths).
 *   - jsconfig.json   → `@<Module>/*` entries in compilerOptions.paths
 *                       (other paths like `ziggy-js` are preserved verbatim).
 *   - package.json    → `#<Module>/*` entries under `imports` for
 *                       Node-native resolution. Note: requires `npm install`
 *                       to take effect at runtime.
 *
 * Idempotent: running twice in a row produces no diff.
 */
class SyncModules extends Command
{
    protected $signature = 'modules:sync {--dry-run : Show what would change without writing files}';

    protected $description = 'Sync vite.config.js, jsconfig.json, and package.json from config/modules.php';

    public function handle(): int
    {
        // Read directly from disk (not via config()) so this command works
        // correctly when invoked from inside another artisan command that has
        // just modified config/modules.php — the in-memory config repo
        // wouldn't see that change.
        $modules = $this->extractModuleNames($this->readProvidersFromDisk());

        if ($modules === []) {
            $this->error('config/modules.php has no providers; nothing to sync.');

            return self::FAILURE;
        }

        $viteChanged = $this->syncViteConfig($modules);
        $jsChanged = $this->syncJsConfig($modules);
        $pkgChanged = $this->syncPackageJson($modules);

        if (! $viteChanged && ! $jsChanged && ! $pkgChanged) {
            $this->info('Already in sync.');
        }

        return self::SUCCESS;
    }

    /**
     * Read the providers list from config/modules.php on disk.
     *
     * @return array<int, string>
     */
    private function readProvidersFromDisk(): array
    {
        $path = config_path('modules.php');

        if (! file_exists($path)) {
            return [];
        }

        $config = require $path;

        if (! is_array($config) || ! isset($config['providers']) || ! is_array($config['providers'])) {
            return [];
        }

        return array_values($config['providers']);
    }

    /**
     * Extract module names from FQCN provider class strings, preserving
     * the order defined in config/modules.php.
     *
     * @param  array<int, string>  $providers
     * @return array<int, string>
     */
    private function extractModuleNames(array $providers): array
    {
        $names = [];

        foreach ($providers as $provider) {
            if (preg_match('/Modules\\\\([A-Z][A-Za-z0-9_]*)\\\\Providers\\\\/', $provider, $m)) {
                $names[] = $m[1];
            }
        }

        return $names;
    }

    /**
     * Replace the `const modules = [...]` array in vite.config.js,
     * leaving the rest of the file untouched.
     *
     * @param  array<int, string>  $modules
     * @return bool true if file content changed (or would change in dry-run)
     */
    private function syncViteConfig(array $modules): bool
    {
        $path = base_path('vite.config.js');

        if (! file_exists($path)) {
            $this->warn('vite.config.js not found, skipping.');

            return false;
        }

        $original = file_get_contents($path);
        $literal = "[\n  '".implode("',\n  '", $modules)."',\n]";
        $replacement = "const modules = {$literal}";

        // [^\]]* is safe: module names are PascalCase identifiers, never contain "]".
        $updated = preg_replace('/const\s+modules\s*=\s*\[[^\]]*\]/s', $replacement, $original, 1);

        if ($updated === null) {
            $this->error('Could not locate `const modules = [...]` in vite.config.js.');

            return false;
        }

        if ($updated === $original) {
            $this->line('vite.config.js: already in sync.');

            return false;
        }

        if ($this->option('dry-run')) {
            $this->line('vite.config.js: would update.');

            return true;
        }

        file_put_contents($path, $updated);
        $this->info('vite.config.js: updated.');

        return true;
    }

    /**
     * Sync `@<Module>/*` entries in jsconfig.json compilerOptions.paths.
     * Non-module paths (e.g. `ziggy-js`) are preserved verbatim and appended
     * after the regenerated module paths.
     *
     * @param  array<int, string>  $modules
     * @return bool true if file content changed (or would change in dry-run)
     */
    private function syncJsConfig(array $modules): bool
    {
        $path = base_path('jsconfig.json');

        if (! file_exists($path)) {
            $this->warn('jsconfig.json not found, skipping.');

            return false;
        }

        $original = file_get_contents($path);
        $config = json_decode($original, true);

        if (! is_array($config) || ! isset($config['compilerOptions']['paths']) || ! is_array($config['compilerOptions']['paths'])) {
            $this->error('jsconfig.json does not have a compilerOptions.paths object.');

            return false;
        }

        $existingPaths = $config['compilerOptions']['paths'];

        // Identify which keys correspond to known modules (so we can remove
        // them and rebuild in config order). Unknown @<Name>/* entries are
        // treated as "non-module" and preserved.
        $moduleNames = array_flip($modules);
        $preserved = [];

        foreach ($existingPaths as $key => $value) {
            if (preg_match('/^@([A-Z][A-Za-z0-9_]*)\/\*$/', $key, $m) && isset($moduleNames[$m[1]])) {
                continue;
            }
            $preserved[$key] = $value;
        }

        // Rebuild: module paths first (config order), then preserved paths.
        $newPaths = [];

        foreach ($modules as $module) {
            $newPaths["@{$module}/*"] = ["./Modules/{$module}/Resources/*"];
        }

        foreach ($preserved as $key => $value) {
            $newPaths[$key] = $value;
        }

        $config['compilerOptions']['paths'] = $newPaths;

        $updated = json_encode($config, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)."\n";

        if ($updated === $original) {
            $this->line('jsconfig.json: already in sync.');

            return false;
        }

        if ($this->option('dry-run')) {
            $this->line('jsconfig.json: would update.');

            return true;
        }

        file_put_contents($path, $updated);
        $this->info('jsconfig.json: updated.');

        return true;
    }

    /**
     * Sync `#<Module>/*` entries under package.json#imports.
     *
     * Node's subpath imports spec lets consumers of this package resolve
     * `#Foo/Components/Bar.vue` from JavaScript without going through
     * Vite's bundler resolver. Useful for editor/IDE support and for
     * Node-side tooling that imports from the same source tree.
     *
     * NOTE: `npm install` must be run after this sync for the changes
     * to be picked up at runtime, because Node caches the `imports` map.
     *
     * @param  array<int, string>  $modules
     * @return bool true if file content changed (or would change in dry-run)
     */
    private function syncPackageJson(array $modules): bool
    {
        $path = base_path('package.json');

        if (! file_exists($path)) {
            $this->warn('package.json not found, skipping.');

            return false;
        }

        $original = file_get_contents($path);
        $config = json_decode($original, true);

        if (! is_array($config)) {
            $this->error('package.json is not valid JSON.');

            return false;
        }

        $existingImports = $config['imports'] ?? [];
        if (! is_array($existingImports)) {
            $existingImports = [];
        }

        $moduleNames = array_flip($modules);
        $preserved = [];

        foreach ($existingImports as $key => $value) {
            // Strip the leading "#" to get the bare name; "#Foo/*" → "Foo".
            if (preg_match('/^#([A-Z][A-Za-z0-9_]*)\/\*$/', $key, $m) && isset($moduleNames[$m[1]])) {
                continue;
            }
            $preserved[$key] = $value;
        }

        $newImports = [];
        foreach ($modules as $module) {
            $newImports["#{$module}/*"] = "./Modules/{$module}/Resources/*";
        }
        foreach ($preserved as $key => $value) {
            $newImports[$key] = $value;
        }

        $config['imports'] = $newImports;

        $updated = json_encode($config, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)."\n";

        if ($updated === $original) {
            $this->line('package.json: already in sync.');

            return false;
        }

        if ($this->option('dry-run')) {
            $this->line('package.json: would update.');

            return true;
        }

        file_put_contents($path, $updated);
        $this->info('package.json: updated (run `npm install` to apply).');

        return true;
    }
}
