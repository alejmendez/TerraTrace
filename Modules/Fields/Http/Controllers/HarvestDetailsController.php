<?php

namespace Modules\Fields\Http\Controllers;

use Inertia\Inertia;
use Modules\Core\Http\Controllers\Controller;
use Modules\Core\Traits\HasPermissionMiddleware;
use Modules\Fields\Http\Requests\StoreHarvestDetailRequest;
use Modules\Fields\Services\HarvestDetailService;
use Modules\Fields\Services\PlantService;

class HarvestDetailsController extends Controller
{
    use HasPermissionMiddleware;

    public function __construct(
        private readonly HarvestDetailService $harvestDetails,
        private readonly PlantService $plants,
    ) {
        $this->setupPermissionMiddleware();
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Fields::HarvestDetails/Create', [
            'qualities' => $this->harvestDetails->qualities('select'),
            'plant_code' => session('plant_code'),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreHarvestDetailRequest $request)
    {
        $this->harvestDetails->create($request->validated());

        if ($request->keep_plant_code) {
            return redirect()->route('harvests_details.create')->with('plant_code', $request->plant_code);
        }

        return redirect()->route('harvests_details.create');
    }

    public function find_by_code()
    {
        $plant = $this->plants->findByCode(request('code', ''));
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
