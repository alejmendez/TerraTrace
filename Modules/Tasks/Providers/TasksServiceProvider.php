<?php

namespace Modules\Tasks\Providers;

use Modules\Core\Providers\CoreServiceProvider;

class TasksServiceProvider extends CoreServiceProvider
{
    /**
     * Register services.
     *
     * Tasks module's own select options (priorities, states, repeat
     * types, supplies units) live on TaskService as instance methods
     * — see AGENTS.md section 4. Each module owns its own lists.
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
        $this->loadModuleAssets(__DIR__);
        $this->mergeConfigFrom(__DIR__.'/../Config/tasks.php', 'tasks');
    }
}
