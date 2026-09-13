<?php

namespace Modules\Fields\Http\Controllers;

use Inertia\Inertia;
use Modules\Core\Http\Controllers\Controller;
use Modules\Core\Traits\HasPermissionMiddleware;
use Modules\Fields\Http\Requests\StoreSecurityEquipmentRequest;
use Modules\Fields\Http\Requests\UpdateSecurityEquipmentRequest;
use Modules\Fields\Http\Resources\SecurityEquipmentResource;
use Modules\Fields\Services\SecurityEquipments\CreateSecurityEquipment;
use Modules\Fields\Services\SecurityEquipments\DeleteSecurityEquipment;
use Modules\Fields\Services\SecurityEquipments\FindSecurityEquipment;
use Modules\Fields\Services\SecurityEquipments\ListSecurityEquipment;
use Modules\Fields\Services\SecurityEquipments\UpdateSecurityEquipment;

class SecurityEquipmentsController extends Controller
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

            return response()->json(ListSecurityEquipment::call($params));
        }

        return Inertia::render('Fields::SecurityEquipments/List', [
            'toast' => session('toast'),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Fields::SecurityEquipments/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreSecurityEquipmentRequest $request)
    {
        CreateSecurityEquipment::call($request->validated());

        return redirect()->route('security_equipments.index')->with('toast', [
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
        $security_equipment = FindSecurityEquipment::call($id);

        return Inertia::render('Fields::SecurityEquipments/Show', [
            'data' => new SecurityEquipmentResource($security_equipment),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $security_equipment = FindSecurityEquipment::call($id);

        return Inertia::render('Fields::SecurityEquipments/Edit', [
            'data' => new SecurityEquipmentResource($security_equipment),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateSecurityEquipmentRequest $request, string $id)
    {
        UpdateSecurityEquipment::call($id, $request->validated());

        return redirect()->route('security_equipments.index')->with('toast', [
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
        DeleteSecurityEquipment::call($id);

        return response()->noContent();
    }
}
