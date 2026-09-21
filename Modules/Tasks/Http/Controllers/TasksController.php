<?php

namespace Modules\Tasks\Http\Controllers;

use Inertia\Inertia;
use Modules\Core\Http\Controllers\Controller;
use Modules\Core\Traits\HasPermissionMiddleware;
use Modules\Fields\Services\FieldService;
use Modules\Fields\Services\MachineryService;
use Modules\Fields\Services\PlantService;
use Modules\Fields\Services\QuarterService;
use Modules\Fields\Services\SecurityEquipmentService;
use Modules\Fields\Services\ToolService;
use Modules\Tasks\Http\Requests\StoreTaskRequest;
use Modules\Tasks\Http\Requests\UpdateTaskRequest;
use Modules\Tasks\Http\Resources\TaskResource;
use Modules\Tasks\Services\TaskService;
use Modules\Users\Services\UserService;

class TasksController extends Controller
{
    use HasPermissionMiddleware;

    public function __construct(
        private readonly TaskService $tasks,
        private readonly UserService $users,
        private readonly FieldService $fields,
        private readonly QuarterService $quarters,
        private readonly PlantService $plants,
        private readonly ToolService $tools,
        private readonly SecurityEquipmentService $securityEquipments,
        private readonly MachineryService $machineries,
    ) {
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
            'responsibles' => $this->users->forSelect(),
            'task_priorities' => $this->tasks->priorities(),
            'task_states' => $this->tasks->states(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Tasks::Create', [
            'fields' => $this->fields->forSelect(),
            'responsibles' => $this->users->forSelect(),
            'tools' => $this->tools->forSelect(),
            'security_equipments' => $this->securityEquipments->forSelect(),
            'machineries' => $this->machineries->forSelect(),
            'task_priorities' => $this->tasks->priorities(),
            'task_states' => $this->tasks->states(),
            'task_repeat_type' => $this->tasks->repeatTypes(),
            'task_supplies_units' => $this->tasks->suppliesUnits(),
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
            'fields' => $this->fields->forSelect(),
            'quarters' => $this->quarters->byField($task->field_id),
            'plants' => $this->plants->byQuarter($task->quarters->map(fn ($q) => $q->id)->toArray()),
            'responsibles' => $this->users->forSelect(),
            'tools' => $this->tools->forSelect(),
            'security_equipments' => $this->securityEquipments->forSelect(),
            'machineries' => $this->machineries->forSelect(),
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
            'fields' => $this->fields->forSelect(),
            'quarters' => $this->quarters->byField($task->field_id),
            'plants' => $this->plants->byQuarter($task->quarters->map(fn ($q) => $q->id)->toArray()),
            'responsibles' => $this->users->forSelect(),
            'tools' => $this->tools->forSelect(),
            'security_equipments' => $this->securityEquipments->forSelect(),
            'machineries' => $this->machineries->forSelect(),
            'task_priorities' => $this->tasks->priorities(),
            'task_states' => $this->tasks->states(),
            'task_repeat_type' => $this->tasks->repeatTypes(),
            'task_supplies_units' => $this->tasks->suppliesUnits(),
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
