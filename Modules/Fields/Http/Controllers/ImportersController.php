<?php

namespace Modules\Fields\Http\Controllers;

use Inertia\Inertia;
use Modules\Core\Http\Controllers\Controller;
use Modules\Core\Traits\HasPermissionMiddleware;
use Modules\Fields\Http\Requests\StoreImporterRequest;
use Modules\Fields\Http\Requests\UpdateImporterRequest;
use Modules\Fields\Services\Importers\CreateImporter;
use Modules\Fields\Services\Importers\DeleteImporter;
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
        if (request()->exists('dt_params')) {
            $params = json_decode(request('dt_params', '[]'), true);

            return response()->json(ListImporter::call($params));
        }

        return Inertia::render('Fields::Importers/List');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreImporterRequest $request)
    {
        $data = $request->validated();
        $importer = CreateImporter::call($data);

        return [
            'importer' => $importer,
            'success' => true,
            'message' => 'Importer created.',
        ];
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateImporterRequest $request, string $id)
    {
        $data = $request->validated();
        UpdateImporter::call($id, $data);

        return [
            'success' => true,
            'message' => 'Importer updated.',
        ];
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
