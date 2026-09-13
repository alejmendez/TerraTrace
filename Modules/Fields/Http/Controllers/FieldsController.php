<?php

namespace Modules\Fields\Http\Controllers;

use Inertia\Inertia;
use Modules\Core\Http\Controllers\Controller;
use Modules\Core\Services\ListEntity;
use Modules\Core\Traits\HasPermissionMiddleware;
use Modules\Fields\Http\Requests\StoreFieldRequest;
use Modules\Fields\Http\Requests\UpdateFieldRequest;
use Modules\Fields\Http\Resources\FieldResource;
use Modules\Fields\Services\Fields\CreateField;
use Modules\Fields\Services\Fields\DeleteField;
use Modules\Fields\Services\Fields\FindField;
use Modules\Fields\Services\Fields\ListField;
use Modules\Fields\Services\Fields\UpdateField;

class FieldsController extends Controller
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

            return response()->json(ListField::call($params));
        }

        return Inertia::render('Fields::Fields/List', [
            'toast' => session('toast'),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Fields::Fields/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreFieldRequest $request)
    {
        $data = $request->validated();
        $data['blueprint'] = $this->storeBlueprint($request);
        $data['documents'] = $this->storeDocuments($request);
        CreateField::call($data);

        return redirect()->route('fields.index')->with('toast', [
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
        $current_tab = request('current_tab', 'file');
        $field = FindField::call($id);

        return Inertia::render('Fields::Fields/Show', [
            'current_tab' => $current_tab,
            'field' => new FieldResource($field),
            'harvest_available_years' => ListEntity::call('harvest_available_years'),
            'harvest_available_weeks' => ListEntity::call('harvest_available_weeks'),
            'fields' => ListEntity::call('field'),
            'quarters' => ListEntity::call('quarter'),
            'users' => ListEntity::call('user'),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $field = FindField::call($id);

        return Inertia::render('Fields::Fields/Edit', [
            'data' => new FieldResource($field),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateFieldRequest $request, string $id)
    {
        $data = $request->validated();
        $data['blueprint'] = $this->storeBlueprint($request);
        $data['documents'] = $this->storeDocuments($request);
        UpdateField::call($id, $data);

        return redirect()->route('fields.index')->with('toast', [
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
        DeleteField::call($id);

        return response()->noContent();
    }

    protected function storeBlueprint(UpdateFieldRequest|StoreFieldRequest $request)
    {
        if ($request->file('blueprint') == null) {
            return null;
        }

        return $request->file('blueprint')->storePublicly('public/blueprints');
    }

    protected function storeDocuments(UpdateFieldRequest|StoreFieldRequest $request)
    {
        if ($request->file('documents') == null) {
            return null;
        }

        $documents = $request->file('documents');
        $documentStore = [];
        foreach ($documents as $document) {
            $documentStore[] = [
                'path' => $document->storePublicly('public/documents'),
                'name' => $document->getClientOriginalName(),
                'type' => $document->getClientMimeType(),
            ];
        }

        return $documentStore;
    }
}
