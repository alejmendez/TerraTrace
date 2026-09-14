<?php

namespace Modules\Fields\Http\Controllers;

use Inertia\Inertia;
use Modules\Core\Http\Controllers\Controller;
use Modules\Core\Services\ListEntity;
use Modules\Core\Traits\HasPermissionMiddleware;
use Modules\Fields\Http\Requests\StoreCategoryProductRequest;
use Modules\Fields\Http\Requests\UpdateCategoryProductRequest;
use Modules\Fields\Services\CategoryProducts\CreateCategoryProduct;
use Modules\Fields\Services\CategoryProducts\DeleteCategoryProduct;
use Modules\Fields\Services\CategoryProducts\FindCategoryProduct;
use Modules\Fields\Services\CategoryProducts\ListCategoryProduct;
use Modules\Fields\Services\CategoryProducts\UpdateCategoryProduct;

class CategoryProductsController extends Controller
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
        $payload = ListCategoryProduct::collection(request()->all());

        return Inertia::render('Fields::CategoryProducts/List', [
            'toast' => session('toast'),
            'records' => $payload['items'],
            'meta' => $payload['meta'],
            'summary' => $payload['summary'],
            'isCommercialOptions' => ListEntity::call('is_commercial_options'),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Fields::CategoryProducts/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCategoryProductRequest $request)
    {
        CreateCategoryProduct::call($request->validated());

        return redirect()->route('category_products.index')->with('toast', [
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
        $categoryProduct = FindCategoryProduct::call($id);

        return Inertia::render('Fields::CategoryProducts/Edit', [
            'data' => $categoryProduct,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCategoryProductRequest $request, string $id)
    {
        UpdateCategoryProduct::call($id, $request->validated());

        return redirect()->route('category_products.index')->with('toast', [
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
        DeleteCategoryProduct::call($id);

        return response()->noContent();
    }
}
