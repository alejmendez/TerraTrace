<?php

namespace Modules\Fields\Http\Controllers;

use Modules\Core\Http\Controllers\Controller;
use Modules\Core\Traits\HasPermissionMiddleware;
use Modules\Fields\Http\Requests\StorePlantDetailRequest;
use Modules\Fields\Http\Resources\PlantDetailCollection;
use Modules\Fields\Services\PlantDetailService;

class PlantDetailsController extends Controller
{
    use HasPermissionMiddleware;

    public function __construct(private readonly PlantDetailService $plantDetails)
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

        $this->plantDetails->create($data);

        return redirect()->back()->with('success', 'Variables agregadas correctamente');
    }

    public function index(int $id)
    {
        return new PlantDetailCollection(
            $this->plantDetails->getByPlant(
                $id,
                request('year'),
                request('show_harvests') === 'true'
            )
        );
    }

    public function index_by_quarter(int $id)
    {
        return new PlantDetailCollection(
            $this->plantDetails->getByQuarter(
                $id,
                request('year'),
                request('show_harvests') === 'true'
            )
        );
    }

    public function index_by_field(int $id)
    {
        return new PlantDetailCollection(
            $this->plantDetails->getByField(
                $id,
                request('year'),
                request('show_harvests') === 'true'
            )
        );
    }

    protected function storeFile($request, $field)
    {
        if ($request->file($field) == null) {
            return null;
        }

        return $request->file($field)->storePublicly('public/variables');
    }
}
