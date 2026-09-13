<?php

namespace Modules\Fields\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;
use Modules\Core\Http\Controllers\Controller;
use Modules\Core\Services\ListEntity;
use Modules\Core\Traits\HasPermissionMiddleware;
use Modules\Fields\Exports\PlantsTemplateExport;
use Modules\Fields\Http\Requests\BulkPlantRequest;
use Modules\Fields\Http\Requests\StorePlantRequest;
use Modules\Fields\Http\Requests\UpdatePlantRequest;
use Modules\Fields\Http\Resources\PlantResource;
use Modules\Fields\Imports\PlantsImport;
use Modules\Fields\Services\Plants\CreatePlant;
use Modules\Fields\Services\Plants\CreatePlantNote;
use Modules\Fields\Services\Plants\DeletePlant;
use Modules\Fields\Services\Plants\FindPlant;
use Modules\Fields\Services\Plants\ListPlant;
use Modules\Fields\Services\Plants\UpdatePlant;

class PlantsController extends Controller
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

            return response()->json(ListPlant::call($params));
        }

        return Inertia::render('Fields::Plants/List', [
            'toast' => session('toast'),
            'fields' => ListEntity::call('field'),
            'quarters' => ListEntity::call('quarter'),
            'plant_types' => ListEntity::call('plant_type'),
            'responsible' => ListEntity::call('responsible'),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Fields::Plants/Create', [
            'types' => ListEntity::call('plant_type'),
            'fields' => ListEntity::call('field'),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePlantRequest $request)
    {
        $data = $request->validated();
        CreatePlant::call($data);

        return redirect()->route('plants.index')->with('toast', [
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
        $plant = FindPlant::call($id);
        $current_tab = request()->get('current_tab', 'file');

        return Inertia::render('Fields::Plants/Show', [
            'data' => new PlantResource($plant),
            'current_tab' => $current_tab,
            'harvest_available_years' => ListEntity::call('harvest_available_years'),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $plant = FindPlant::call($id);

        return Inertia::render('Fields::Plants/Edit', [
            'data' => new PlantResource($plant),
            'types' => ListEntity::call('plant_type'),
            'fields' => ListEntity::call('field'),
            'quarters' => ListEntity::call('quarter', ['field_id' => $plant->quarter->field_id]),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePlantRequest $request, string $id)
    {
        $data = $request->validated();
        UpdatePlant::call($id, $data);

        return redirect()->route('plants.index')->with('toast', [
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
        DeletePlant::call($id);

        return response()->noContent();
    }

    public function store_note(Request $request)
    {
        $data = $request->validate([
            'plant_id' => 'required|exists:plants,id',
            'note' => 'required|string',
        ]);

        CreatePlantNote::call($data);

        return response()->json(['message' => 'Nota creada correctamente']);
    }

    public function download_bulk_template()
    {
        return Excel::download(new PlantsTemplateExport, 'carga_masiva_plantas.xlsx');
    }

    public function create_bulk()
    {
        return Inertia::render('Fields::Plants/Bulk/Create', [
            'fields' => ListEntity::call('field'),
            'message_success' => session('message_success', ''),
            'unprocessed_message' => session('unprocessed_message', ''),
            'unprocessed_details' => session('unprocessed_details', []),
            'error_message' => session('error_message', ''),
            'import_errors' => session('import_errors', []),
        ]);
    }

    public function store_bulk(BulkPlantRequest $request)
    {
        $file = request()->file('bulk_file');
        $quarter_id = $request['quarter_id']['value'];

        $import = new PlantsImport($quarter_id);
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
                $unprocessed_details[] = "Linea: $line, Codigo: $code";
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
