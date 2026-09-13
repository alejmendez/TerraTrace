<?php

namespace Modules\Fields\Http\Controllers;

use Inertia\Inertia;
use Modules\Core\Http\Controllers\Controller;
use Modules\Core\Services\ListEntity;
use Modules\Core\Traits\HasPermissionMiddleware;
use Modules\Fields\Http\Requests\StoreBatchRequest;
use Modules\Fields\Http\Requests\UpdateBatchRequest;
use Modules\Fields\Http\Resources\BatchResource;
use Modules\Fields\Services\Batches\CreateBatch;
use Modules\Fields\Services\Batches\DeleteBatch;
use Modules\Fields\Services\Batches\FindBatch;
use Modules\Fields\Services\Batches\ListBatch;
use Modules\Fields\Services\Batches\UpdateBatch;
use Modules\Fields\Models\Harvest;


class BatchesController extends Controller
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

            return response()->json(ListBatch::call($params));
        }

        return Inertia::render('Fields::Batches/List', [
            'toast' => session('toast'),
            'importers' => ListEntity::call('importer'),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Fields::Batches/Create', [
            'importers' => ListEntity::call('importer'),
            'harvests' => $this->getHarvests(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreBatchRequest $request)
    {
        CreateBatch::call($request->validated());

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
        $batch = FindBatch::call($id);

        if (request()->exists('print')) {
            return new BatchResource($batch);
        }

        return Inertia::render('Fields::Batches/Show', [
            'importers' => ListEntity::call('importer'),
            'harvests' => $this->getHarvests($id),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $batch = FindBatch::call($id);

        return Inertia::render('Fields::Batches/Edit', [
            'data' => new BatchResource($batch),
            'importers' => ListEntity::call('importer'),
            'harvests' => $this->getHarvests($id),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateBatchRequest $request, string $id)
    {
        UpdateBatch::call($id, $request->validated());

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
        DeleteBatch::call($id);

        return response()->noContent();
    }


    protected function getHarvests($batch_id = null)
    {
        return Harvest::select('year')->distinct()->orderBy('year', 'desc')->get()->map(function ($harvest) use ($batch_id) {
            $harvests = Harvest::select('id', 'week', 'batch')
                ->where('year', $harvest->year)
                ->whereDoesntHave('batches', function ($query) use ($batch_id) {
                    if ($batch_id) {
                        $query->where('batch_id', '!=', $batch_id);
                    }
                })
                ->orderBy('date', 'desc')
                ->get();
            return [
                'items' => $harvests->map(function ($harvest) {
                    return [
                        'value' => $harvest->id,
                        'label' => __('harvest.form.batch.renderText', ['week' => $harvest->week, 'batch' => $harvest->batch]),
                    ];
                }),
                'label' => $harvest->year,
            ];
        })->filter(function ($harvest) {
            return $harvest['items']->isNotEmpty();
        })->values();
    }
}
