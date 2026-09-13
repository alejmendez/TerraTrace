<?php

namespace Modules\Tasks\Services;

use Illuminate\Support\Facades\DB;
use Modules\Tasks\Models\SupplyTask;
use Modules\Tasks\Models\Task;
use Modules\Tasks\Models\TaskCorrelative;
use Modules\Tasks\Models\TaskComment;
use Carbon\Carbon;

class CreateTask
{
    public static function call($data): Task
    {
        DB::beginTransaction();
        try {
            $task = new Task;

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
            $correlative = self::getCorrelative($data['start_date']);
            $task->correlative = $correlative->getCorrelative();
            $task->save();

            self::syncRelationship($task, 'quarters', $data['quarter_id'] ?? []);
            self::syncRelationship($task, 'plants', $data['plant_id'] ?? []);
            self::syncRelationship($task, 'tools', $data['tools'] ?? []);
            self::syncRelationship($task, 'security_equipments', $data['security_equipments'] ?? []);
            self::syncRelationship($task, 'machineries', $data['machineries'] ?? []);

            self::saveSupplies($task, $data['supplies'] ?? []);

            self::saveComment($task, $data['comment'] ?? '');

            DB::commit();

            return $task;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    protected static function syncRelationship($task, $relation, $data)
    {
        if (! empty($data)) {
            $ids = collect($data)->map(fn ($q) => $q['value'])->toArray();
            $task->{$relation}()->sync($ids);
        }
    }

    protected static function saveSupplies($task, $data)
    {
        foreach ($data as $supplyData) {
            if ($supplyData['name'] == null && $supplyData['brand'] == null && $supplyData['quantity'] == null && $supplyData['unit'] == null) {
                continue;
            }
            $supply = new SupplyTask;
            $supply->name = $supplyData['name'];
            $supply->brand = $supplyData['brand'];
            $supply->quantity = $supplyData['quantity'];
            $supply->unit = $supplyData['unit']['value'];
            $supply->task_id = $task->id;

            $supply->save();
        }
    }

    protected static function saveComment($task, $comment)
    {
        $data = [
            'task_id' => $task->id,
            'comment' => $comment,
        ];

        $taskComment = CreateTaskComment::call($data, auth()->user());
    }

    protected static function getCorrelative($date)
    {
        $year = Carbon::parse($date)->year;
        $correlative = TaskCorrelative::where('year', $year)->first();
        if ($correlative) {
            $correlative->correlative++;
            $correlative->save();

            return $correlative;
        }

        $correlative = TaskCorrelative::create([
            'correlative' => 0,
            'year' => $year,
        ]);

        return $correlative;
    }
}
