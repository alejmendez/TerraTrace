<?php

namespace Modules\Fields\Http\Controllers;

use Modules\Core\Http\Controllers\Controller;
use Modules\Core\Traits\HasPermissionMiddleware;
use Modules\Fields\Http\Requests\StorePlantDetailRequest;
use Modules\Fields\Http\Resources\PlantDetailCollection;
use Modules\Fields\Services\PlantDetails\CreatePlantDetails;
use Modules\Fields\Services\PlantDetails\FindPlantDetails;

class PlantDetailsController extends Controller
{
    use HasPermissionMiddleware;

    public function __construct()
    {
        $this->setupPermissionMiddleware();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePlantDetailRequest $request)
    {
        $data = $request->validated();
        $data['foliage_sanitation_photo'] = $this->storeFile($request, 'foliage_sanitation_photo');
        $data['trunk_sanitation_photo'] = $this->storeFile($request, 'trunk_sanitation_photo');
        $data['soil_sanitation_photo'] = $this->storeFile($request, 'soil_sanitation_photo');

        CreatePlantDetails::call($data);

        return redirect()->back()->with('success', 'Variables agregadas correctamente');
    }

    public function index(int $id)
    {
        $show_harvests = request('show_harvests') === 'true';
        $year = request('year');
        $plant_details = FindPlantDetails::get_by_plant_id($id, $year, $show_harvests);

        return new PlantDetailCollection($plant_details);
    }

    public function index_by_quarter(int $id)
    {
        $show_harvests = request('show_harvests') === 'true';
        $year = request('year');
        $plant_details = FindPlantDetails::get_by_quarter_id($id, $year, $show_harvests);

        return new PlantDetailCollection($plant_details);
    }

    public function index_by_field(int $id)
    {
        $show_harvests = request('show_harvests') === 'true';
        $year = request('year');
        $plant_details = FindPlantDetails::get_by_field_id($id, $year, $show_harvests);

        return new PlantDetailCollection($plant_details);
    }

    protected function storeFile($request, $field)
    {
        if ($request->file($field) == null) {
            return null;
        }

        return $request->file($field)->storePublicly('public/variables');
    }
}
