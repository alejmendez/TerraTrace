<?php

namespace Modules\Tasks\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Modules\Core\Models\Notification;
use Modules\Core\Services\PrimevueDatatables;
use Modules\Tasks\Models\SupplyTask;
use Modules\Tasks\Models\Task;
use Modules\Tasks\Models\TaskCorrelative;
use Modules\Tasks\Notifications\TaskNotification;
use Modules\Users\Models\User;

class TaskService
{
    private const SEARCHABLE_COLUMNS = ['correlative', 'name', 'status', 'priority', 'updated_at', 'responsible.full_name'];

    public function __construct(private readonly TaskCommentService $taskComments) {}

    /**
     * Tasks-owned select options. Source: config/tasks.php.
     *
     * These used to be registered in EntityRegistry via
     * TasksServiceProvider; moved here per the cross-module pattern
     * (each module is the owner of its own lists, AGENTS.md §4).
     * Cross-module lists (field, quarter, plant, user, tool, etc.)
     * still resolve through EntityRegistry / ListEntity.
     */
    public function priorities(): array
    {
        return collect(config('tasks.priorities'))->map(fn ($priority) => [
            'value' => $priority,
            'text' => __("task.form.priority.options.{$priority}"),
        ])->values()->toArray();
    }

    public function states(): array
    {
        return collect(config('tasks.states'))->map(fn ($state) => [
            'value' => $state,
            'text' => __("task.form.status.options.{$state}"),
        ])->values()->toArray();
    }

    public function repeatTypes(): array
    {
        return collect(config('tasks.repeat_type'))->map(fn ($type) => [
            'value' => $type,
            'text' => __("task.form.repeat_type.options.{$type}"),
        ])->values()->toArray();
    }

    public function suppliesUnits(): array
    {
        return collect(config('tasks.supplies_units'))->map(fn ($unit) => [
            'value' => $unit,
            'text' => __("task.form.supplies.unit.options.{$unit}"),
        ])->values()->toArray();
    }

    public function create(array $data): Task
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
            $correlative = $this->getCorrelative($data['start_date']);
            $task->correlative = $correlative->getCorrelative();
            $task->save();

            $this->syncRelationship($task, 'quarters', $data['quarter_id'] ?? [], skipIfEmpty: true);
            $this->syncRelationship($task, 'plants', $data['plant_id'] ?? [], skipIfEmpty: true);
            $this->syncRelationship($task, 'tools', $data['tools'] ?? [], skipIfEmpty: true);
            $this->syncRelationship($task, 'security_equipments', $data['security_equipments'] ?? [], skipIfEmpty: true);
            $this->syncRelationship($task, 'machineries', $data['machineries'] ?? [], skipIfEmpty: true);

            $this->saveSupplies($task, $data['supplies'] ?? []);

            $this->saveComment($task, $data['comment'] ?? '');

            DB::commit();

            return $task;
        } catch (\Exception $e) {
            DB::rollBack();

            throw $e;
        }
    }

    public function find(string|int $id): Task
    {
        $task = Task::with('comments', 'responsible')->findOrFail($id);
        $currentUser = auth()->user();
        $this->markNotificationAsRead($task, $currentUser);

        return $task;
    }

    public function update(string|int $id, array $data): Task
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

        $this->syncRelationship($task, 'quarters', $data['quarter_id'] ?? []);
        $this->syncRelationship($task, 'plants', $data['plant_id'] ?? []);
        $this->syncRelationship($task, 'tools', $data['tools'] ?? []);
        $this->syncRelationship($task, 'security_equipments', $data['security_equipments'] ?? []);
        $this->syncRelationship($task, 'machineries', $data['machineries'] ?? []);

        $this->saveSupplies($task, $data['supplies'] ?? [], update: true);

        return $task;
    }

    public function delete(string|int $id): void
    {
        $task = Task::find($id);

        if ($task) {
            $task->comments()->delete();
            $task->delete();

            Notification::whereRaw("data like '%\"task_id\":$id,%'")->delete();
        }
    }

    public function list(array $params): mixed
    {
        $query = Task::query();

        $statusFilter = collect($params['filters']['status']['value'] ?? []);
        $params['filters']['status']['value'] = $statusFilter->map(fn ($item) => $item['value'])->toArray();

        $datatable = new PrimevueDatatables($params, self::SEARCHABLE_COLUMNS);

        return $datatable->of($query)->make();
    }

    public function collection(array $params = []): array
    {
        $query = Task::query()
            ->select('tasks.id', 'tasks.correlative', 'tasks.name', 'tasks.status', 'tasks.priority', 'tasks.start_date', 'tasks.end_date', 'tasks.updated_at', 'tasks.responsible_id')
            ->with(['responsible:id,full_name']);

        $search = trim($params['q'] ?? '');

        if ($search !== '') {
            $query->where(function ($builder) use ($search) {
                $builder
                    ->where('tasks.correlative', 'like', "%{$search}%")
                    ->where('tasks.name', 'like', "%{$search}%")
                    ->orWhereHas('responsible', function ($responsibleQuery) use ($search) {
                        $responsibleQuery->where('full_name', 'like', "%{$search}%");
                    });
            });
        }

        $statuses = collect(explode(',', (string) ($params['status'] ?? '')))
            ->map(fn ($value) => trim($value))
            ->filter()
            ->values()
            ->all();

        if (! empty($statuses)) {
            $query->whereIn('tasks.status', $statuses);
        }

        if (! empty($params['priority'])) {
            $query->where('tasks.priority', $params['priority']);
        }

        if (! empty($params['responsible_id'])) {
            $query->where('tasks.responsible_id', $params['responsible_id']);
        }

        $summary = [
            'tasks' => (clone $query)->count(),
            'assigned' => (clone $query)->whereNotNull('tasks.responsible_id')->count(),
        ];

        $sort = in_array($params['sort'] ?? '', ['correlative', 'name', 'status', 'priority', 'updated_at', 'end_date'], true)
            ? $params['sort']
            : 'updated_at';
        $direction = ($params['direction'] ?? 'desc') === 'asc' ? 'asc' : 'desc';
        $perPage = min(max((int) ($params['per_page'] ?? 12), 1), 24);
        $page = max((int) ($params['page'] ?? 1), 1);

        $paginator = $query
            ->orderBy("tasks.{$sort}", $direction)
            ->paginate($perPage, ['*'], 'page', $page);

        return [
            'items' => $paginator->items(),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'from' => $paginator->firstItem() ?? 0,
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'to' => $paginator->lastItem() ?? 0,
                'total' => $paginator->total(),
            ],
            'summary' => $summary,
        ];
    }

    /**
     * Mark unread notifications for the given task as read for the given user.
     *
     * Originally lived in MarkTaskNotificationAsRead::call and was duplicated
     * inline inside FindTask::call; centralised here.
     */
    public function markNotificationAsRead(Task $task, User $user): void
    {
        if (! $task->responsible || $task->responsible->id !== $user->id) {
            return;
        }

        $notifications = $user->notifications
            ->where('type', TaskNotification::class)
            ->whereNull('read_at')
            ->filter(fn ($notification) => $notification->data['task_id'] === $task->id);

        if ($notifications->isEmpty()) {
            return;
        }

        $notifications->markAsRead();
    }

    private function syncRelationship(Task $task, string $relation, array $data, bool $skipIfEmpty = false): void
    {
        if ($skipIfEmpty && empty($data)) {
            return;
        }
        $ids = collect($data)->map(fn ($q) => $q['value'])->toArray();
        $task->{$relation}()->sync($ids);
    }

    private function saveSupplies(Task $task, array $data, bool $update = false): void
    {
        $supplies = collect($data);

        if ($update) {
            $existingSupplyIds = $task->supplies()->pluck('id');
            $supplyIdsToDelete = $existingSupplyIds->diff($supplies->pluck('id')->filter());

            SupplyTask::destroy($supplyIdsToDelete);
        }

        foreach ($supplies as $supplyData) {
            if ($update) {
                $supply = SupplyTask::find($supplyData['id'] ?? null) ?? new SupplyTask;
            } else {
                $supply = new SupplyTask;
            }

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

    private function saveComment(Task $task, string $comment): void
    {
        $this->taskComments->create([
            'task_id' => $task->id,
            'comment' => $comment,
        ], auth()->user());
    }

    private function getCorrelative(string $date): TaskCorrelative
    {
        $year = Carbon::parse($date)->year;
        $correlative = TaskCorrelative::where('year', $year)->first();
        if ($correlative) {
            $correlative->correlative++;
            $correlative->save();

            return $correlative;
        }

        return TaskCorrelative::create([
            'correlative' => 0,
            'year' => $year,
        ]);
    }
}
