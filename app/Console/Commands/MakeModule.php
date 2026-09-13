<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class MakeModule extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'modules:make {module}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Make a new module';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $module = $this->argument('module');
        $this->createDirectories($module);

        // Crear archivos base
        $this->createBaseFiles($module);

        $this->updateConfigFiles($module);
    }

    private function createDirectories($module)
    {
        $directories = [
            "Modules/{$module}/Http/Controllers",
            "Modules/{$module}/Http/Resources",
            "Modules/{$module}/Http/Requests",
            "Modules/{$module}/Models",
            "Modules/{$module}/Resources/Components",
            "Modules/{$module}/Resources/Pages",
            "Modules/{$module}/Routes",
            "Modules/{$module}/Services",
            "Modules/{$module}/Database/Migrations",
            "Modules/{$module}/Database/Seeders",
        ];

        foreach ($directories as $directory) {
            if (! is_dir($directory)) {
                mkdir($directory, 0755, true);
                $this->info("Directorio creado: {$directory}");
            }
        }
    }

    private function createBaseFiles($module)
    {
        // Crear archivos base
        $files = [
            "Modules/{$module}/Routes/web.php" => "<?php\n\nuse Illuminate\Support\Facades\Route;\n",
            "Modules/{$module}/Routes/api.php" => "<?php\n\nuse Illuminate\Support\Facades\Route;\n",
            "Modules/{$module}/Database/Seeders/{$module}Seeder.php" => "<?php\n\nnamespace Modules\\{$module}\\Database\\Seeders;\n\nuse Illuminate\Database\Seeder;\n\nclass {$module}Seeder extends Seeder\n{\n    public function run()\n    {\n        //\n    }\n}",
            "Modules/{$module}/Resources/Pages/Index.vue" => "<script setup>\n</script>\n\n<template>\n</template>",
            "Modules/{$module}/Resources/Pages/Create.vue" => "<script setup>\n</script>\n\n<template>\n</template>",
            "Modules/{$module}/Resources/Pages/Edit.vue" => "<script setup>\n</script>\n\n<template>\n</template>",
            "Modules/{$module}/Resources/Components/Form.vue" => "<script setup>\n</script>\n\n<template>\n</template>",
        ];

        foreach ($files as $path => $content) {
            if (! file_exists($path)) {
                file_put_contents($path, $content);
                $this->info("Archivo creado: {$path}");
            }
        }
    }

    private function updateConfigFiles($module)
    {
        // Actualizar jsconfig.json
        $jsconfigPath = base_path('jsconfig.json');
        if (file_exists($jsconfigPath)) {
            $jsconfig = json_decode(file_get_contents($jsconfigPath), true);
            $jsconfig['compilerOptions']['paths']["@{$module}/*"] = ["./Modules/{$module}/Resources/*"];
            file_put_contents($jsconfigPath, json_encode($jsconfig, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
            $this->info('jsconfig.json actualizado con el nuevo módulo');
        }

        // Actualizar vite.config.js
        $vitePath = base_path('vite.config.js');
        if (file_exists($vitePath)) {
            $viteContent = file_get_contents($vitePath);
            $aliasPattern = "/alias\s*:\s*{([^}]*)}/";

            if (preg_match($aliasPattern, $viteContent, $matches)) {
                $currentAliases = $matches[1];
                $newAlias = "\n            '@{$module}': path.resolve(__dirname, './Modules/{$module}/Resources'),";

                if (! str_contains($currentAliases, "@{$module}")) {
                    $newContent = str_replace(
                        $matches[0],
                        "alias: {{$currentAliases}{$newAlias}\n        }",
                        $viteContent
                    );

                    file_put_contents($vitePath, $newContent);
                    $this->info('vite.config.js actualizado con el nuevo módulo');
                }
            }
        }
    }
}
