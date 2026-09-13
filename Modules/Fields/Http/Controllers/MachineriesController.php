<?php

namespace Modules\Fields\Http\Controllers;

use Inertia\Inertia;
use Modules\Core\Http\Controllers\Controller;
use Modules\Core\Traits\HasPermissionMiddleware;
use Modules\Fields\Http\Requests\StoreMachineryRequest;
use Modules\Fields\Http\Requests\UpdateMachineryRequest;
use Modules\Fields\Http\Resources\MachineryResource;
use Modules\Fields\Services\Machineries\CreateMachinery;
use Modules\Fields\Services\Machineries\DeleteMachinery;
use Modules\Fields\Services\Machineries\FindMachinery;
use Modules\Fields\Services\Machineries\ListMachinery;
use Modules\Fields\Services\Machineries\UpdateMachinery;

class MachineriesController extends Controller
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
        if (request()->exists('dt_params')) {
            $params = json_decode(request('dt_params', '[]'), true);

            return response()->json(ListMachinery::call($params));
        }

        return Inertia::render('Fields::Machineries/List', [
            'toast' => session('toast'),
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
        CreateMachinery::call($request->validated());

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
        $machinery = FindMachinery::call($id);

        return Inertia::render('Fields::Machineries/Show', [
            'data' => new MachineryResource($machinery),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $machinery = FindMachinery::call($id);

        return Inertia::render('Fields::Machineries/Edit', [
            'data' => new MachineryResource($machinery),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateMachineryRequest $request, string $id)
    {
        UpdateMachinery::call($id, $request->validated());

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
        DeleteMachinery::call($id);

        return response()->noContent();
    }
}
