<?php

namespace Modules\Tasks\Http\Controllers;

use Inertia\Inertia;
use Modules\Core\Http\Controllers\Controller;
use Modules\Core\Services\ListEntity;
use Modules\Core\Traits\HasPermissionMiddleware;
use Modules\Tasks\Http\Requests\StoreTaskRequest;
use Modules\Tasks\Http\Requests\UpdateTaskRequest;
use Modules\Tasks\Http\Resources\TaskResource;
use Modules\Tasks\Services\TaskService;

class TasksController extends Controller
{
    use HasPermissionMiddleware;

    public function __construct(private readonly TaskService $tasks)
    {
        $this->setupPermissionMiddleware();
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $payload = $this->tasks->collection(request()->all());

        return Inertia::render('Tasks::List', [
            'toast' => session('toast'),
            'records' => $payload['items'],
            'meta' => $payload['meta'],
            'summary' => $payload['summary'],
            'responsibles' => ListEntity::call('responsible'),
            'task_priorities' => ListEntity::call('task_priorities'),
            'task_states' => ListEntity::call('task_states'),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Tasks::Create', [
            'fields' => ListEntity::call('field'),
            'responsibles' => ListEntity::call('responsible'),
            'tools' => ListEntity::call('tool'),
            'security_equipments' => ListEntity::call('security_equipment'),
            'machineries' => ListEntity::call('machinery'),
            'task_priorities' => ListEntity::call('task_priorities'),
            'task_states' => ListEntity::call('task_states'),
            'task_repeat_type' => ListEntity::call('task_repeat_type'),
            'task_supplies_units' => ListEntity::call('task_supplies_units'),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTaskRequest $request)
    {
        $this->tasks->create($request->validated());

        return redirect()->route('tasks.index')->with('toast', [
            'severity' => 'success',
            'summary' => __('generics.messages.saved_successfully'),
            'detail' => __('generics.messages.saved_successfully'),
            'life' => 5000,
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $task = $this->tasks->find($id);

        $current_tab = request()->get('current_tab', 'detail');

        return Inertia::render('Tasks::Show', [
            'data' => new TaskResource($task),
            'fields' => ListEntity::call('field'),
            'quarters' => ListEntity::call('quarter', ['field_id' => $task->field_id]),
            'plants' => ListEntity::call('plant', ['quarter_id' => $task->quarters->map(fn ($q) => $q->id)->toArray()]),
            'responsibles' => ListEntity::call('responsible'),
            'tools' => ListEntity::call('tool'),
            'security_equipments' => ListEntity::call('security_equipment'),
            'machineries' => ListEntity::call('machinery'),
            'task_priorities' => ListEntity::call('task_priorities'),
            'task_states' => ListEntity::call('task_states'),
            'task_repeat_type' => ListEntity::call('task_repeat_type'),
            'task_supplies_units' => ListEntity::call('task_supplies_units'),
            'current_tab' => $current_tab,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $task = $this->tasks->find($id);

        return Inertia::render('Tasks::Edit', [
            'data' => new TaskResource($task),
            'fields' => ListEntity::call('field'),
            'quarters' => ListEntity::call('quarter', ['field_id' => $task->field_id]),
            'plants' => ListEntity::call('plant', ['quarter_id' => $task->quarters->map(fn ($q) => $q->id)->toArray()]),
            'responsibles' => ListEntity::call('responsible'),
            'tools' => ListEntity::call('tool'),
            'security_equipments' => ListEntity::call('security_equipment'),
            'machineries' => ListEntity::call('machinery'),
            'task_priorities' => ListEntity::call('task_priorities'),
            'task_states' => ListEntity::call('task_states'),
            'task_repeat_type' => ListEntity::call('task_repeat_type'),
            'task_supplies_units' => ListEntity::call('task_supplies_units'),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTaskRequest $request, string $id)
    {
        $this->tasks->update($id, $request->validated());

        return redirect()->route('tasks.index')->with('toast', [
            'severity' => 'success',
            'summary' => __('generics.messages.saved_successfully'),
            'detail' => __('generics.messages.saved_successfully'),
            'life' => 5000,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $this->tasks->delete($id);

        return response()->noContent();
    }
}
