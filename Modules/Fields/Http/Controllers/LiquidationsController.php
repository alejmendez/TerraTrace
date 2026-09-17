<?php

namespace Modules\Fields\Http\Controllers;

use Inertia\Inertia;
use Modules\Core\Http\Controllers\Controller;
use Modules\Core\Services\ListEntity;
use Modules\Core\Traits\HasPermissionMiddleware;
use Modules\Fields\Http\Requests\StoreLiquidationRequest;
use Modules\Fields\Http\Requests\UpdateLiquidationRequest;
use Modules\Fields\Http\Resources\LiquidationResource;
use Modules\Fields\Services\LiquidationService;

class LiquidationsController extends Controller
{
    use HasPermissionMiddleware;

    public function __construct(private readonly LiquidationService $liquidations)
    {
        $this->setupPermissionMiddleware();
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $payload = $this->liquidations->collection(request()->all());

        return Inertia::render('Fields::Liquidations/List', [
            'toast' => session('toast'),
            'records' => $payload['items'],
            'meta' => $payload['meta'],
            'summary' => $payload['summary'],
            'importers' => ListEntity::call('importer'),
            'liquidation_available_years' => $this->liquidations->availableYears(),
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
        $this->liquidations->create($request->validated());

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
        $liquidation = $this->liquidations->find($id);

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
        $liquidation = $this->liquidations->find($id);

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
        $this->liquidations->update($id, $request->validated());

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
        $this->liquidations->delete($id);

        return response()->noContent();
    }
}
