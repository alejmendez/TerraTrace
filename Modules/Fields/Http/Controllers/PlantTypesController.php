<?php

namespace Modules\Fields\Http\Controllers;

use Inertia\Inertia;
use Modules\Core\Http\Controllers\Controller;
use Modules\Core\Traits\HasPermissionMiddleware;
use Modules\Fields\Http\Requests\StorePlantTypeRequest;
use Modules\Fields\Http\Requests\UpdatePlantTypeRequest;
use Modules\Fields\Services\PlantTypeService;

class PlantTypesController extends Controller
{
    use HasPermissionMiddleware;

    public function __construct(private readonly PlantTypeService $plantTypes)
    {
        $this->setupPermissionMiddleware();
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $payload = $this->plantTypes->collection(request()->all());

        return Inertia::render('Fields::PlantTypes/List', [
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
        return Inertia::render('Fields::PlantTypes/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePlantTypeRequest $request)
    {
        $this->plantTypes->create($request->validated());

        return redirect()->route('plant_types.index')->with('toast', [
            'severity' => 'success',
            'summary' => __('generics.messages.saved_successfully'),
            'detail' => __('generics.messages.saved_successfully'),
            'life' => 5000,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $plantType = $this->plantTypes->find($id);

        return Inertia::render('Fields::PlantTypes/Edit', [
            'data' => $plantType,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePlantTypeRequest $request, string $id)
    {
        $this->plantTypes->update($id, $request->validated());

        return redirect()->route('plant_types.index')->with('toast', [
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
        $this->plantTypes->delete($id);

        return response()->noContent();
    }
}
