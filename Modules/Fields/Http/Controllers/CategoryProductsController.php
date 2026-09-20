<?php

namespace Modules\Fields\Http\Controllers;

use Inertia\Inertia;
use Modules\Core\Http\Controllers\Controller;
use Modules\Core\Traits\HasPermissionMiddleware;
use Modules\Fields\Http\Requests\StoreCategoryProductRequest;
use Modules\Fields\Http\Requests\UpdateCategoryProductRequest;
use Modules\Fields\Services\CategoryProductService;

class CategoryProductsController extends Controller
{
    use HasPermissionMiddleware;

    public function __construct(private readonly CategoryProductService $categoryProducts)
    {
        $this->setupPermissionMiddleware();
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $payload = $this->categoryProducts->collection(request()->all());

        return Inertia::render('Fields::CategoryProducts/List', [
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
        return Inertia::render('Fields::CategoryProducts/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCategoryProductRequest $request)
    {
        $this->categoryProducts->create($request->validated());

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
        $categoryProduct = $this->categoryProducts->find($id);

        return Inertia::render('Fields::CategoryProducts/Edit', [
            'data' => $categoryProduct,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCategoryProductRequest $request, string $id)
    {
        $this->categoryProducts->update($id, $request->validated());

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
        $this->categoryProducts->delete($id);

        return response()->noContent();
    }
}
