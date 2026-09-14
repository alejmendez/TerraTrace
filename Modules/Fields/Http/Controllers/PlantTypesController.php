<?php

namespace Modules\Fields\Http\Controllers;

use Inertia\Inertia;
use Modules\Core\Http\Controllers\Controller;
use Modules\Core\Traits\HasPermissionMiddleware;
use Modules\Fields\Http\Requests\StorePlantTypeRequest;
use Modules\Fields\Http\Requests\UpdatePlantTypeRequest;
use Modules\Fields\Services\PlantTypes\CreatePlantType;
use Modules\Fields\Services\PlantTypes\DeletePlantType;
use Modules\Fields\Services\PlantTypes\FindPlantType;
use Modules\Fields\Services\PlantTypes\ListPlantType;
use Modules\Fields\Services\PlantTypes\UpdatePlantType;

class PlantTypesController extends Controller
{
    use HasPermissionMiddleware;

    public function __construct()
    {
        $this->setupPermissionMiddleware();
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $payload = ListPlantType::collection(request()->all());

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
        CreatePlantType::call($request->validated());

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
        $plantType = FindPlantType::call($id);

        return Inertia::render('Fields::PlantTypes/Edit', [
            'data' => $plantType,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePlantTypeRequest $request, string $id)
    {
        UpdatePlantType::call($id, $request->validated());

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
        DeletePlantType::call($id);

        return response()->noContent();
    }
}
