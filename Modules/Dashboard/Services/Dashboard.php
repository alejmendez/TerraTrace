<?php

namespace Modules\Dashboard\Services;

use Modules\Fields\Models\Field;
use Modules\Fields\Services\FieldService;
use Modules\Fields\Services\FieldsStatsProvider;
use Modules\Tasks\Services\TasksStatsProvider;

/**
 * Dashboard data assembler.
 *
 * This class does not import Fields or Tasks models, queries or
 * per-action services. It composes data from:
 *
 *   - FieldsStatsProvider (Fields module):  harvest / yield stats
 *   - TasksStatsProvider  (Tasks module):   global task counters
 *   - FieldService        (Fields module):  flat field list for the
 *                                           picker dropdown
 *
 * Adding a new dashboard widget = adding a method to the relevant
 * provider / service, NOT adding a new cross-module import here.
 *
 * Cross-module communication pattern documented in AGENTS.md §4.
 */
class Dashboard
{
    public function __construct(
        private FieldsStatsProvider $fieldsStats,
        private TasksStatsProvider $tasksStats,
        private FieldService $fields,
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
        $fields = $this->fields->forSelect();
        $resolvedId = $fieldId ?: ($fields[0]['value'] ?? null);

        // No fields at all (fresh DB / no seed): return an empty payload
        // instead of crashing findField(). The Index page must handle the
        // empty `field` shape (FieldResource on null is the caller's job).
        if ($resolvedId === null || $resolvedId === 'undefined') {
            return [
                'fields' => $fields,
                'field' => null,
                'harvest_data' => [
                    'total_weight_of_last_harvest' => 0,
                    'average_weight_per_plant' => 0,
                    'variation_between_harvests' => 0,
                    'years_variation' => [0, 0],
                ],
                'task_data' => $this->tasksStats->taskCounters(),
            ];
        }

        $field = $this->fieldsStats->findField($resolvedId);

        return [
            'fields' => $fields,
            'field' => $field,
            'harvest_data' => $this->fieldsStats->harvestStatsForField($field),
            'task_data' => $this->tasksStats->taskCounters(),
        ];
    }
}
