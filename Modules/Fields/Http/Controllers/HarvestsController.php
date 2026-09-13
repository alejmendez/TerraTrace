<?php

namespace Modules\Fields\Http\Controllers;

use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;
use Modules\Core\Http\Controllers\Controller;
use Modules\Core\Services\ListEntity;
use Modules\Core\Traits\HasPermissionMiddleware;
use Modules\Fields\Exports\HarvestsTemplateExport;
use Modules\Fields\Http\Requests\BulkHarvestRequest;
use Modules\Fields\Http\Requests\StoreHarvestRequest;
use Modules\Fields\Http\Requests\UpdateHarvestRequest;
use Modules\Fields\Http\Resources\HarvestResource;
use Modules\Fields\Imports\HarvestsImport;
use Modules\Fields\Services\HarvestDetails\ListHarvestQualities;
use Modules\Fields\Services\Harvests\CreateHarvest;
use Modules\Fields\Services\Harvests\DeleteHarvest;
use Modules\Fields\Services\Harvests\FindHarvest;
use Modules\Fields\Services\Harvests\ListHarvest;
use Modules\Fields\Services\Harvests\UpdateHarvest;

class HarvestsController extends Controller
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
            $paramsCollection = collect($params);

            $harvests = ListHarvest::call($params);

            return response()->json($harvests);
        }

        return Inertia::render('Fields::Harvests/List', [
            'toast' => session('toast'),
            'harvest_available_years' => ListEntity::call('harvest_available_years'),
            'harvest_available_weeks' => ListEntity::call('harvest_available_weeks'),
            'fields' => ListEntity::call('field'),
            'quarters' => ListEntity::call('quarter'),
            'users' => ListEntity::call('user'),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Fields::Harvests/Create', [
            'quarters' => ListEntity::call('quarterMultiselect'),
            'dogs' => ListEntity::call('dog'),
            'users' => ListEntity::call('user'),
            'plant_codes' => ListEntity::call('plant'),
            'qualities' => ListHarvestQualities::call('select'),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreHarvestRequest $request)
    {
        CreateHarvest::call($request->validated());

        return redirect()->route('harvests.index')->with('toast', [
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
        $harvest = FindHarvest::call($id);

        return Inertia::render('Fields::Harvests/Show', [
            'data' => new HarvestResource($harvest),
            'quarters' => ListEntity::call('quarterMultiselect'),
            'dogs' => ListEntity::call('dog'),
            'users' => ListEntity::call('user'),
            'plant_codes' => ListEntity::call('plant'),
            'qualities' => ListHarvestQualities::call('select'),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $harvest = FindHarvest::call($id);

        return Inertia::render('Fields::Harvests/Edit', [
            'data' => new HarvestResource($harvest),
            'quarters' => ListEntity::call('quarterMultiselect'),
            'dogs' => ListEntity::call('dog'),
            'users' => ListEntity::call('user'),
            'plant_codes' => ListEntity::call('plant'),
            'qualities' => ListHarvestQualities::call('select'),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateHarvestRequest $request, string $id)
    {
        UpdateHarvest::call($id, $request->validated());

        return redirect()->route('harvests.index')->with('toast', [
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
        DeleteHarvest::call($id);

        return response()->noContent();
    }

    public function download_bulk_template()
    {
        return Excel::download(new HarvestsTemplateExport, 'carga_masiva_cosecha.xlsx');
    }

    public function create_bulk()
    {
        return Inertia::render('Fields::Harvests/Bulk/Create', [
            'id' => request('id'),
            'message_success' => session('message_success', ''),
            'unprocessed_message' => session('unprocessed_message', ''),
            'unprocessed_details' => session('unprocessed_details', []),
            'error_message' => session('error_message', ''),
            'import_errors' => session('import_errors', []),
            'harvests' => ListEntity::call('harvest'),
            'harvest_available_years' => ListEntity::call('harvest_available_years'),
        ]);
    }

    public function store_bulk(BulkHarvestRequest $request)
    {
        $file = request()->file('bulk_file');
        $harvest_id = $request['harvest_id']['value'];

        $import = new HarvestsImport($harvest_id);
        $import->import($file);

        $errors = [];
        foreach ($import->failures() as $failure) {
            foreach ($failure->errors() as $error) {
                $errors[] = "Linea {$failure->row()}: {$error}";
            }
        }

        $rowCount = $import->getRowCount();
        $countErrors = count($errors);
        $unprocessedRecords = $import->getUnprocessedRecords();
        $numberOfUnprocessedRecords = $import->getNumberOfUnprocessedRecords();

        $message_success = '';
        if ($countErrors > 0) {
            $message_success = "Se han ingresado $rowCount registros al sistema y se tienen $countErrors errores.";
        } else {
            $message_success = "La carga de datos ha sido completada con éxito. Se han ingresado $rowCount registros de tipo de datos al sistema. ¡Buen trabajo!";
        }

        $error_message = '';
        if ($countErrors > 0) {
            $error_message = "Hay $countErrors errores o advertencias que debes corregir. Puedes ver el detalle de los errores en el resumen de la carga.";
        }

        $unprocessed_message = '';
        $unprocessed_details = [];
        if ($numberOfUnprocessedRecords > 0) {
            $unprocessed_message = "Hay $numberOfUnprocessedRecords que no tienen errores pero no fueron procesados porque ya existen.";
            foreach ($unprocessedRecords as $record) {
                $line = $record['line'];
                $code = $record['code'];
                $quality = $record['quality'];
                $weight = $record['weight'];
                $unprocessed_details[] = "Linea: $line, Codigo: $code, Calidad: $quality, Peso: $weight";
            }
        }

        return redirect()
            ->back()
            ->with('message_success', $message_success)
            ->with('unprocessed_message', $unprocessed_message)
            ->with('unprocessed_details', $unprocessed_details)
            ->with('error_message', $error_message)
            ->with('import_errors', $errors);
    }
}
