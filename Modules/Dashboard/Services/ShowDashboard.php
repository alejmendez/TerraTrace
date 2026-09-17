<?php

namespace Modules\Dashboard\Services;

use Modules\Core\Services\ListEntity;
use Modules\Fields\Services\FieldsStatsProvider;
use Modules\Tasks\Services\TasksStatsProvider;

/**
 * Dashboard data assembler.
 *
 * After the cross-module refactor, this class no longer imports
 * Fields or Tasks models. It composes data from two "stats providers":
 *
 *   - FieldsStatsProvider (Fields module):  harvest / yield stats
 *   - TasksStatsProvider  (Tasks module):   task counters
 *
 * Adding a new dashboard widget = adding a method to the relevant
 * provider, NOT adding a new cross-module import here.
 *
 * Injected via the DashboardController constructor.
 */
class ShowDashboard
{
    public function __construct(
        private FieldsStatsProvider $fieldsStats,
        private TasksStatsProvider $tasksStats,
    ) {}

    /**
     * Build the payload for Dashboard::Index.
     *
     * @return array{
     *   fields: array,
     *   field: mixed,
     *   harvest_data: array,
     *   task_data: array
     * }
     */
    public function call($id = null)
    {
        $current_user = auth()->user();
        $fields = ListEntity::call('field');
        $field = $this->fieldsStats->findField($id ?? $fields[0]['value']);

        return [
            'fields' => $fields,
            'field' => $field,
            'harvest_data' => $this->fieldsStats->harvestStatsForField($field),
            'task_data' => $this->tasksStats->userTaskStats($current_user),
        ];
    }
}
