<?php

namespace Modules\Tasks\Services;

use Modules\Tasks\Models\Task;
use Modules\Users\Models\User;

/**
 * Cross-module "stats" provider for the Tasks domain.
 *
 * Dashboard needs aggregated task counts per user; rather than
 * reaching into the Tasks model from Dashboard, this provider
 * exposes a single `userTaskStats()` method that returns the three
 * counters the dashboard cares about.
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
     * Aggregated task counters for the dashboard.
     *
     * @return array{
     *   pending_tasks: int,
     *   tasks_in_progress: int,
     *   tasks_totals: int
     * }
     */
    public function userTaskStats(?User $user): array
    {
        return [
            'pending_tasks' => Task::where('status', 'overdued')->count(),
            'tasks_in_progress' => Task::whereIn('status', ['started', 'overdued'])->count(),
            'tasks_totals' => Task::whereIn('status', ['to_begin', 'started', 'stopped', 'overdued'])->count(),
        ];
    }
}
