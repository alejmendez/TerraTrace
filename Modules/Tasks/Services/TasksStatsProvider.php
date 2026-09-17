<?php

namespace Modules\Tasks\Services;

use Modules\Tasks\Models\Task;

/**
 * Cross-module "stats" provider for the Tasks domain.
 *
 * Dashboard needs aggregated task counters; rather than reaching
 * into the Tasks model from Dashboard, this provider exposes
 * `taskCounters()` returning the three counters the dashboard
 * cares about.
 *
 * Note: counters are GLOBAL by design. The dashboard's CardsDashboard
 * displays "X tareas" company-wide (per the click-through filters
 * `status=overdued`, `status=started,overdued`, etc.). Filtering by
 * responsible would change the displayed totals and break the UI's
 * promise.
 *
 * Adding new task-related dashboard widgets = adding methods here
 * (not new imports in Dashboard).
 *
 * NOTE: the original ShowDashboard used `Task::whereStatus(...)`
 * which relied on a non-existent query scope and would have crashed
 * at runtime. We use plain `where('status', ...)` here instead.
 */
class TasksStatsProvider
{
    /**
     * Global task counters for the dashboard.
     *
     * @return array{
     *   pending_tasks: int,
     *   tasks_in_progress: int,
     *   tasks_totals: int
     * }
     */
    public function taskCounters(): array
    {
        return [
            'pending_tasks' => Task::where('status', 'overdued')->count(),
            'tasks_in_progress' => Task::whereIn('status', ['started', 'overdued'])->count(),
            'tasks_totals' => Task::whereIn('status', ['to_begin', 'started', 'stopped', 'overdued'])->count(),
        ];
    }
}
