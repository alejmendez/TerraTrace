<?php

namespace Modules\Dashboard\Services;

use Modules\Core\Services\ListEntity;
use Modules\Fields\Models\Field;
use Modules\Fields\Services\FieldsStatsProvider;
use Modules\Tasks\Services\TasksStatsProvider;

/**
 * Dashboard data assembler.
 *
 * This class does not import Fields or Tasks models, queries or
 * per-action services. It composes data from two "stats providers":
 *
 *   - FieldsStatsProvider (Fields module):  harvest / yield stats
 *   - TasksStatsProvider  (Tasks module):   global task counters
 *
 * Adding a new dashboard widget = adding a method to the relevant
 * provider, NOT adding a new cross-module import here.
 *
 * Cross-module communication pattern documented in AGENTS.md.
 */
class Dashboard
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
     *   field: Field,
     *   harvest_data: array,
     *   task_data: array
     * }
     */
    public function show(int|string|null $fieldId = null): array
    {
        $fields = ListEntity::call('field');
        $field = $this->fieldsStats->findField($fieldId ?? $fields[0]['value']);

        return [
            'fields' => $fields,
            'field' => $field,
            'harvest_data' => $this->fieldsStats->harvestStatsForField($field),
            'task_data' => $this->tasksStats->taskCounters(),
        ];
    }
}
