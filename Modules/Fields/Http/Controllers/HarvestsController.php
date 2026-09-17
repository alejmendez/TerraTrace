<?php

namespace Modules\Fields\Http\Controllers;

use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;
use Modules\Core\Http\Controllers\Controller;
use Modules\Core\Traits\HasPermissionMiddleware;
use Modules\Fields\Exports\HarvestsTemplateExport;
use Modules\Fields\Http\Requests\BulkHarvestRequest;
use Modules\Fields\Http\Requests\StoreHarvestRequest;
use Modules\Fields\Http\Requests\UpdateHarvestRequest;
use Modules\Fields\Http\Resources\HarvestResource;
use Modules\Fields\Imports\HarvestsImport;
use Modules\Fields\Services\DogService;
use Modules\Fields\Services\FieldService;
use Modules\Fields\Services\HarvestDetailService;
use Modules\Fields\Services\HarvestService;
use Modules\Fields\Services\PlantService;
use Modules\Fields\Services\QuarterService;
use Modules\Users\Services\UserService;

class HarvestsController extends Controller
{
    use HasPermissionMiddleware;

    public function __construct(
        private readonly HarvestService $harvests,
        private readonly HarvestDetailService $harvestDetails,
        private readonly FieldService $fields,
        private readonly QuarterService $quarters,
        private readonly DogService $dogs,
        private readonly PlantService $plants,
        private readonly UserService $users,
    ) {
        $this->setupPermissionMiddleware();
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $payload = $this->harvests->collection(request()->all());

        return Inertia::render('Fields::Harvests/List', [
            'toast' => session('toast'),
            'records' => $payload['items'],
            'meta' => $payload['meta'],
            'summary' => $payload['summary'],
            'harvest_available_years' => $this->harvests->availableYears(),
            'harvest_available_weeks' => $this->harvests->availableWeeks(),
            'fields' => $this->fields->forSelect(),
            'quarters' => $this->quarters->forSelect(),
            'users' => $this->users->responsibles(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Fields::Harvests/Create', [
            'quarters' => $this->quarters->quartersByFieldGrouped(),
            'dogs' => $this->dogs->forSelect(),
            'users' => $this->users->responsibles(),
            'plant_codes' => $this->plants->forSelect(),
            'qualities' => $this->harvestDetails->qualities('select'),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreHarvestRequest $request)
    {
        $this->harvests->create($request->validated());

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
        $harvest = $this->harvests->find($id);

        return Inertia::render('Fields::Harvests/Show', [
            'data' => new HarvestResource($harvest),
            'quarters' => $this->quarters->quartersByFieldGrouped(),
            'dogs' => $this->dogs->forSelect(),
            'users' => $this->users->responsibles(),
            'plant_codes' => $this->plants->forSelect(),
            'qualities' => $this->harvestDetails->qualities('select'),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $harvest = $this->harvests->find($id);

        return Inertia::render('Fields::Harvests/Edit', [
            'data' => new HarvestResource($harvest),
            'quarters' => $this->quarters->quartersByFieldGrouped(),
            'dogs' => $this->dogs->forSelect(),
            'users' => $this->users->responsibles(),
            'plant_codes' => $this->plants->forSelect(),
            'qualities' => $this->harvestDetails->qualities('select'),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateHarvestRequest $request, string $id)
    {
        $this->harvests->update($id, $request->validated());

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
        $this->harvests->delete($id);

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
            'harvests' => $this->harvests->forSelect(),
            'harvest_available_years' => $this->harvests->availableYears(),
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
