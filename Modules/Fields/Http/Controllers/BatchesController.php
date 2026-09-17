<?php

namespace Modules\Fields\Http\Controllers;

use Inertia\Inertia;
use Modules\Core\Http\Controllers\Controller;
use Modules\Core\Traits\HasPermissionMiddleware;
use Modules\Fields\Http\Requests\StoreBatchRequest;
use Modules\Fields\Http\Requests\UpdateBatchRequest;
use Modules\Fields\Http\Resources\BatchResource;
use Modules\Fields\Services\BatchService;
use Modules\Fields\Services\ImporterService;

class BatchesController extends Controller
{
    use HasPermissionMiddleware;

    public function __construct(
        private readonly BatchService $batches,
        private readonly ImporterService $importers,
    ) {
        $this->setupPermissionMiddleware();
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $payload = $this->batches->collection(request()->all());

        return Inertia::render('Fields::Batches/List', [
            'toast' => session('toast'),
            'records' => $payload['items'],
            'meta' => $payload['meta'],
            'summary' => $payload['summary'],
            'importers' => $this->importers->forSelect(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Fields::Batches/Create', [
            'importers' => $this->importers->forSelect(),
            'harvests' => $this->batches->availableHarvests(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreBatchRequest $request)
    {
        $this->batches->create($request->validated());

        return redirect()->route('batches.index')->with('toast', [
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
        $batch = $this->batches->find($id);

        if (request()->exists('print')) {
            return new BatchResource($batch);
        }

        return Inertia::render('Fields::Batches/Show', [
            'importers' => $this->importers->forSelect(),
            'harvests' => $this->batches->availableHarvests($id),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $batch = $this->batches->find($id);

        return Inertia::render('Fields::Batches/Edit', [
            'data' => new BatchResource($batch),
            'importers' => $this->importers->forSelect(),
            'harvests' => $this->batches->availableHarvests($id),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateBatchRequest $request, string $id)
    {
        $this->batches->update($id, $request->validated());

        return redirect()->route('batches.index')->with('toast', [
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
        $this->batches->delete($id);

        return response()->noContent();
    }
}
