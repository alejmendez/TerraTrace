<?php

namespace Modules\Fields\Http\Controllers;

use Inertia\Inertia;
use Modules\Core\Http\Controllers\Controller;
use Modules\Core\Traits\HasPermissionMiddleware;
use Modules\Fields\Http\Requests\StoreMachineryRequest;
use Modules\Fields\Http\Requests\UpdateMachineryRequest;
use Modules\Fields\Http\Resources\MachineryResource;
use Modules\Fields\Services\MachineryService;

class MachineriesController extends Controller
{
    use HasPermissionMiddleware;

    public function __construct(private readonly MachineryService $machineries)
    {
        $this->setupPermissionMiddleware();
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $payload = $this->machineries->collection(request()->all());

        return Inertia::render('Fields::Machineries/List', [
            'toast' => session('toast'),
            'records' => $payload['items'],
            'meta' => $payload['meta'],
            'summary' => $payload['summary'],
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Fields::Machineries/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreMachineryRequest $request)
    {
        $this->machineries->create($request->validated());

        return redirect()->route('machineries.index')->with('toast', [
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
        $machinery = $this->machineries->find($id);

        return Inertia::render('Fields::Machineries/Show', [
            'data' => new MachineryResource($machinery),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $machinery = $this->machineries->find($id);

        return Inertia::render('Fields::Machineries/Edit', [
            'data' => new MachineryResource($machinery),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateMachineryRequest $request, string $id)
    {
        $this->machineries->update($id, $request->validated());

        return redirect()->route('machineries.index')->with('toast', [
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
        $this->machineries->delete($id);

        return response()->noContent();
    }
}
