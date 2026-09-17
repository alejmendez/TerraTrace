<?php

namespace Modules\Fields\Http\Controllers;

use Inertia\Inertia;
use Modules\Core\Http\Controllers\Controller;
use Modules\Core\Traits\HasPermissionMiddleware;
use Modules\Fields\Http\Requests\StoreLiquidationRequest;
use Modules\Fields\Http\Requests\UpdateLiquidationRequest;
use Modules\Fields\Http\Resources\LiquidationResource;
use Modules\Fields\Services\CategoryProductService;
use Modules\Fields\Services\FieldService;
use Modules\Fields\Services\ImporterService;
use Modules\Fields\Services\LiquidationService;

class LiquidationsController extends Controller
{
    use HasPermissionMiddleware;

    public function __construct(
        private readonly LiquidationService $liquidations,
        private readonly ImporterService $importers,
        private readonly FieldService $fields,
        private readonly CategoryProductService $categoryProducts,
    ) {
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
            'importers' => $this->importers->forSelect(),
            'liquidation_available_years' => $this->liquidations->availableYears(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Fields::Liquidations/Create', [
            'importers' => $this->importers->forSelect(),
            'fields' => $this->fields->forSelect(),
            'category_products' => $this->categoryProducts->forSelect(),
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
            'importers' => $this->importers->forSelect(),
            'fields' => $this->fields->forSelect(),
            'category_products' => $this->categoryProducts->forSelect(),
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
            'importers' => $this->importers->forSelect(),
            'fields' => $this->fields->forSelect(),
            'category_products' => $this->categoryProducts->forSelect(),
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
