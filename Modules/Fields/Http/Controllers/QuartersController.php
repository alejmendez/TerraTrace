<?php

namespace Modules\Fields\Http\Controllers;

use Inertia\Inertia;
use Modules\Core\Http\Controllers\Controller;
use Modules\Core\Traits\HasPermissionMiddleware;
use Modules\Fields\Http\Requests\StoreQuarterRequest;
use Modules\Fields\Http\Requests\UpdateQuarterRequest;
use Modules\Fields\Http\Resources\QuarterResource;
use Modules\Fields\Services\FieldService;
use Modules\Fields\Services\HarvestService;
use Modules\Fields\Services\QuarterService;
use Modules\Users\Services\UserService;

class QuartersController extends Controller
{
    use HasPermissionMiddleware;

    public function __construct(
        private readonly QuarterService $quarters,
        private readonly FieldService $fields,
        private readonly HarvestService $harvests,
        private readonly UserService $users,
    ) {
        $this->setupPermissionMiddleware();
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if (request()->boolean('collection')) {
            return response()->json($this->quarters->collection(request()->all()));
        }

        if (request()->exists('dt_params')) {
            $params = json_decode(request('dt_params', '[]'), true);

            return response()->json($this->quarters->list($params));
        }

        return Inertia::render('Fields::Quarters/List', [
            'toast' => session('toast'),
            'fields' => $this->fields->forSelect(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Fields::Quarters/Create', [
            'fields' => $this->fields->forSelect(),
            'responsibles' => $this->users->responsibles(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreQuarterRequest $request)
    {
        $data = $request->validated();
        $data['blueprint'] = $this->storeBlueprint($request);
        $this->quarters->create($data);

        return redirect()->route('quarters.index')->with('toast', [
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
        $quarter = $this->quarters->find($id);

        return Inertia::render('Fields::Quarters/Show', [
            'data' => new QuarterResource($quarter),
            'current_tab' => $current_tab,
            'harvest_available_years' => $this->harvests->availableYears(),
            'harvest_available_weeks' => $this->harvests->availableWeeks(),
            'fields' => $this->fields->forSelect(),
            'quarters' => $this->quarters->forSelect(),
            'users' => $this->users->responsibles(),
            'scale_types' => $this->scaleTypesForSelect(),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $quarter = $this->quarters->find($id);

        return Inertia::render('Fields::Quarters/Edit', [
            'data' => new QuarterResource($quarter),
            'fields' => $this->fields->forSelect(),
            'responsibles' => $this->users->responsibles(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateQuarterRequest $request, string $id)
    {
        $data = $request->validated();
        $data['blueprint'] = $this->storeBlueprint($request);
        $this->quarters->update($id, $data);

        return redirect()->route('quarters.index')->with('toast', [
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
        $this->quarters->delete($id);

        return response()->noContent();
    }

    public function plants(string $id)
    {
        return response()->json($this->quarters->plantsWithHarvests($id));
    }

    public function plants_update_position(string $id)
    {
        $this->quarters->updatePlantPositions($id, request('data', []));

        return response()->noContent();
    }

    /**
     * Static scale-type option list from translations. Kept on the
     * controller because the values are fixed and promoting to a
     * dedicated service would be over-engineering.
     */
    private function scaleTypesForSelect(): array
    {
        return [
            ['value' => 'weight', 'text' => trans('quarter.show.statistics.scale_type.options.weight')],
            ['value' => 'quantity', 'text' => trans('quarter.show.statistics.scale_type.options.quantity')],
        ];
    }

    protected function storeBlueprint(UpdateQuarterRequest|StoreQuarterRequest $request)
    {
        if ($request->file('blueprint') == null) {
            return null;
        }

        return $request->file('blueprint')->storePublicly('public/blueprints');
    }
}
