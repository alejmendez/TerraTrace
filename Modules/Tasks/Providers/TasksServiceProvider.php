<?php

namespace Modules\Tasks\Providers;

use Modules\Core\Providers\CoreServiceProvider;
use Modules\Core\Registry\EntityRegistry;

class TasksServiceProvider extends CoreServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Tasks module's named entities — derived from config/tasks.php
        // values so adding a new option in config doesn't require
        // editing this provider.
        EntityRegistry::register('task_priorities', null, static function () {
            return collect(config('tasks.priorities'))->map(fn ($priority) => [
                'value' => $priority,
                'text' => __("task.form.priority.options.{$priority}"),
            ])->values()->toArray();
        });

        EntityRegistry::register('task_states', null, static function () {
            return collect(config('tasks.states'))->map(fn ($state) => [
                'value' => $state,
                'text' => __("task.form.status.options.{$state}"),
            ])->values()->toArray();
        });

        EntityRegistry::register('task_repeat_type', null, static function () {
            return collect(config('tasks.repeat_type'))->map(fn ($type) => [
                'value' => $type,
                'text' => __("task.form.repeat_type.options.{$type}"),
            ])->values()->toArray();
        });

        EntityRegistry::register('task_supplies_units', null, static function () {
            return collect(config('tasks.supplies_units'))->map(fn ($unit) => [
                'value' => $unit,
                'text' => __("task.form.supplies.unit.options.{$unit}"),
            ])->values()->toArray();
        });
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
