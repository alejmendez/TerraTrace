<?php

namespace Modules\Fields\Http\Controllers;

use Inertia\Inertia;
use Modules\Core\Http\Controllers\Controller;
use Modules\Core\Traits\HasPermissionMiddleware;
use Modules\Fields\Http\Requests\StoreHarvestDetailRequest;
use Modules\Fields\Services\HarvestDetails\CreateHarvestDetails;
use Modules\Fields\Services\HarvestDetails\ListHarvestQualities;
use Modules\Fields\Services\Plants\FindPlantByCode;

class HarvestDetailsController extends Controller
{
    use HasPermissionMiddleware;

    public function __construct()
    {
        $this->setupPermissionMiddleware();
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Fields::HarvestDetails/Create', [
            'qualities' => ListHarvestQualities::call('select'),
            'plant_code' => session('plant_code'),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreHarvestDetailRequest $request)
    {
        CreateHarvestDetails::call($request->validated());
        if ($request->keep_plant_code) {
            return redirect()->route('harvests_details.create')->with('plant_code', $request->plant_code);
        } else {
            return redirect()->route('harvests_details.create');
        }

    }

    public function find_by_code()
    {
        $plant = FindPlantByCode::call(request('code', ''));
        if (! $plant) {
            return response()->json([
                'error' => 'Plant not found',
            ], 404);
        }

        return [
            'plant' => $plant,
            'details' => $plant->activeDetails()->pluck('value', 'type'),
        ];
    }
}
