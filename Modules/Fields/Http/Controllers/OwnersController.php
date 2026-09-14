<?php

namespace Modules\Fields\Http\Controllers;

use Inertia\Inertia;
use Modules\Core\Http\Controllers\Controller;
use Modules\Core\Traits\HasPermissionMiddleware;
use Modules\Fields\Http\Requests\StoreOwnerRequest;
use Modules\Fields\Http\Requests\UpdateOwnerRequest;
use Modules\Fields\Services\Owners\CreateOwner;
use Modules\Fields\Services\Owners\DeleteOwner;
use Modules\Fields\Services\Owners\FindOwner;
use Modules\Fields\Services\Owners\ListOwner;
use Modules\Fields\Services\Owners\UpdateOwner;

class OwnersController extends Controller
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
        $payload = ListOwner::collection(request()->all());

        return Inertia::render('Fields::Owners/List', [
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
        return Inertia::render('Fields::Owners/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreOwnerRequest $request)
    {
        CreateOwner::call($request->validated());

        return redirect()->route('owners.index')->with('toast', [
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
        $owner = FindOwner::call($id);

        return Inertia::render('Fields::Owners/Edit', [
            'data' => $owner,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateOwnerRequest $request, string $id)
    {
        UpdateOwner::call($id, $request->validated());

        return redirect()->route('owners.index')->with('toast', [
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
        DeleteOwner::call($id);

        return response()->noContent();
    }
}
