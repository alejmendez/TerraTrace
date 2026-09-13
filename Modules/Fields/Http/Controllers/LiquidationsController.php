<?php

namespace Modules\Fields\Http\Controllers;

use Inertia\Inertia;
use Modules\Core\Http\Controllers\Controller;
use Modules\Core\Services\ListEntity;
use Modules\Core\Traits\HasPermissionMiddleware;
use Modules\Fields\Http\Requests\StoreLiquidationRequest;
use Modules\Fields\Http\Requests\UpdateLiquidationRequest;
use Modules\Fields\Http\Resources\LiquidationResource;
use Modules\Fields\Services\Liquidations\CreateLiquidation;
use Modules\Fields\Services\Liquidations\DeleteLiquidation;
use Modules\Fields\Services\Liquidations\FindLiquidation;
use Modules\Fields\Services\Liquidations\ListLiquidation;
use Modules\Fields\Services\Liquidations\UpdateLiquidation;

class LiquidationsController extends Controller
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

            return response()->json(ListLiquidation::call($params));
        }

        return Inertia::render('Fields::Liquidations/List', [
            'toast' => session('toast'),
            'importers' => ListEntity::call('importer'),
            'liquidation_available_years' => ListEntity::call('liquidation_available_years'),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Fields::Liquidations/Create', [
            'importers' => ListEntity::call('importer'),
            'fields' => ListEntity::call('field'),
            'category_products' => ListEntity::call('category_products'),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreLiquidationRequest $request)
    {
        CreateLiquidation::call($request->validated());

        return redirect()->route('liquidations.index')->with('toast', [
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
        $liquidation = FindLiquidation::call($id);

        return Inertia::render('Fields::Liquidations/Show', [
            'data' => new LiquidationResource($liquidation),
            'importers' => ListEntity::call('importer'),
            'fields' => ListEntity::call('field'),
            'category_products' => ListEntity::call('category_products'),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $liquidation = FindLiquidation::call($id);

        return Inertia::render('Fields::Liquidations/Edit', [
            'data' => new LiquidationResource($liquidation),
            'importers' => ListEntity::call('importer'),
            'fields' => ListEntity::call('field'),
            'category_products' => ListEntity::call('category_products'),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateLiquidationRequest $request, string $id)
    {
        UpdateLiquidation::call($id, $request->validated());

        return redirect()->route('liquidations.index')->with('toast', [
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
        DeleteLiquidation::call($id);

        return response()->noContent();
    }
}
