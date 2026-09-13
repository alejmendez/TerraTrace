<?php

namespace Modules\Fields\Http\Controllers;

use Inertia\Inertia;
use Modules\Core\Http\Controllers\Controller;
use Modules\Core\Traits\HasPermissionMiddleware;
use Modules\Fields\Http\Requests\StorePlantTypeRequest;
use Modules\Fields\Http\Requests\UpdatePlantTypeRequest;
use Modules\Fields\Services\PlantTypes\CreatePlantType;
use Modules\Fields\Services\PlantTypes\DeletePlantType;
use Modules\Fields\Services\PlantTypes\ListPlantType;
use Modules\Fields\Services\PlantTypes\UpdatePlantType;

class PlantTypesController extends Controller
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

            return response()->json(ListPlantType::call($params));
        }

        return Inertia::render('Fields::PlantTypes/List');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePlantTypeRequest $request)
    {
        $data = $request->validated();
        $type = CreatePlantType::call($data);

        return [
            'type' => $type,
            'success' => true,
            'message' => 'Plant Type created.',
        ];
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePlantTypeRequest $request, string $id)
    {
        $data = $request->validated();
        UpdatePlantType::call($id, $data);

        return [
            'success' => true,
            'message' => 'Plant Type updated.',
        ];
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        DeletePlantType::call($id);

        return response()->noContent();
    }
}
