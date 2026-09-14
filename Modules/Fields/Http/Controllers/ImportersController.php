<?php

namespace Modules\Fields\Http\Controllers;

use Inertia\Inertia;
use Modules\Core\Http\Controllers\Controller;
use Modules\Core\Traits\HasPermissionMiddleware;
use Modules\Fields\Http\Requests\StoreImporterRequest;
use Modules\Fields\Http\Requests\UpdateImporterRequest;
use Modules\Fields\Services\Importers\CreateImporter;
use Modules\Fields\Services\Importers\DeleteImporter;
use Modules\Fields\Services\Importers\FindImporter;
use Modules\Fields\Services\Importers\ListImporter;
use Modules\Fields\Services\Importers\UpdateImporter;

class ImportersController extends Controller
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
        $payload = ListImporter::collection(request()->all());

        return Inertia::render('Fields::Importers/List', [
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
        return Inertia::render('Fields::Importers/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreImporterRequest $request)
    {
        CreateImporter::call($request->validated());

        return redirect()->route('importers.index')->with('toast', [
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
        $importer = FindImporter::call($id);

        return Inertia::render('Fields::Importers/Edit', [
            'data' => $importer,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateImporterRequest $request, string $id)
    {
        UpdateImporter::call($id, $request->validated());

        return redirect()->route('importers.index')->with('toast', [
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
        DeleteImporter::call($id);

        return response()->noContent();
    }
}
