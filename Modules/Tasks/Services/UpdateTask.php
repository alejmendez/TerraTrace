<?php

namespace Modules\Tasks\Services;

use Modules\Tasks\Models\SupplyTask;
use Modules\Tasks\Models\Task;

class UpdateTask
{
    public static function call($id, $data): Task
    {
        $task = Task::findOrFail($id);

        $task->name = $data['name'];
        $task->status = $data['status']['value'];
        $task->repeat_number = '0';
        $task->repeat_type = '';
        $task->priority = $data['priority']['value'];
        $task->start_date = $data['start_date'];
        $task->end_date = $data['end_date'];
        $task->field_id = $data['field_id']['value'];
        $task->rows = collect($data['rows'])->map(fn ($q) => $q['value'])->toArray();
        $task->responsible_id = $data['responsible_id']['value'];
        $task->save();

        self::syncRelationship($task, 'quarters', $data['quarter_id'] ?? []);
        self::syncRelationship($task, 'plants', $data['plant_id'] ?? []);
        self::syncRelationship($task, 'tools', $data['tools'] ?? []);
        self::syncRelationship($task, 'security_equipments', $data['security_equipments'] ?? []);
        self::syncRelationship($task, 'machineries', $data['machineries'] ?? []);

        self::saveSupplies($task, $data['supplies'] ?? []);

        return $task;
    }

    protected static function syncRelationship($task, $relation, $data)
    {
        $ids = collect($data)->map(fn ($d) => $d['value'])->toArray();
        $task->{$relation}()->sync($ids);
    }

    protected static function saveSupplies($task, $data)
    {
        $supplies = collect($data);

        $existingSupplyIds = $task->supplies()->pluck('id');
        $supplyIdsToDelete = $existingSupplyIds->diff($supplies->pluck('id')->filter());

        SupplyTask::destroy($supplyIdsToDelete);

        foreach ($supplies as $supplyData) {
            $supply = SupplyTask::find($supplyData['id'] ?? null) ?? new SupplyTask;

            if ($supplyData['name'] == null && $supplyData['brand'] == null && $supplyData['quantity'] == null && $supplyData['unit'] == null) {
                continue;
            }

            $supply->name = $supplyData['name'];
            $supply->brand = $supplyData['brand'];
            $supply->quantity = $supplyData['quantity'];
            $supply->unit = $supplyData['unit']['value'];
            $supply->task_id = $task->id;

            $supply->save();
        }
    }
}
