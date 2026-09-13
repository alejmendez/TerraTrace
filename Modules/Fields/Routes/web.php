<?php

use Illuminate\Support\Facades\Route;
use Modules\Fields\Http\Controllers\BatchesController;
use Modules\Fields\Http\Controllers\BulksController;
use Modules\Fields\Http\Controllers\CategoryProductsController;
use Modules\Fields\Http\Controllers\DogsController;
use Modules\Fields\Http\Controllers\FieldsController;
use Modules\Fields\Http\Controllers\GraphsController;
use Modules\Fields\Http\Controllers\HarvestDetailsController;
use Modules\Fields\Http\Controllers\HarvestsController;
use Modules\Fields\Http\Controllers\ImportersController;
use Modules\Fields\Http\Controllers\LiquidationsController;
use Modules\Fields\Http\Controllers\MachineriesController;
use Modules\Fields\Http\Controllers\OwnersController;
use Modules\Fields\Http\Controllers\PlantDetailsController;
use Modules\Fields\Http\Controllers\PlantsController;
use Modules\Fields\Http\Controllers\PlantTypesController;
use Modules\Fields\Http\Controllers\QuartersController;
use Modules\Fields\Http\Controllers\SecurityEquipmentsController;
use Modules\Fields\Http\Controllers\ToolsController;

Route::middleware('auth')->group(function () {
    Route::get('/bulk', [BulksController::class, 'index'])->name('bulk.index');

    Route::get('/plants/bulk', [PlantsController::class, 'create_bulk'])->name('plants.create.bulk');
    Route::post('/plants/bulk', [PlantsController::class, 'store_bulk'])->name('plants.store.bulk');
    Route::get('/plants/download/bulk/template', [PlantsController::class, 'download_bulk_template'])->name('plants.download.bulk.template');
    Route::post('/plants/notes/store', [PlantsController::class, 'store_note'])->name('plants.notes.store');

    Route::post('/plants/details', [PlantDetailsController::class, 'store'])->name('plants.details.store');
    Route::get('/plants/details/quarter/{id}', [PlantDetailsController::class, 'index_by_quarter'])->name('plants.details.by_quarter');
    Route::get('/plants/details/field/{id}', [PlantDetailsController::class, 'index_by_field'])->name('plants.details.by_field');
    Route::get('/plants/details/{id}', [PlantDetailsController::class, 'index'])->name('plants.details.index');

    Route::get('/harvests/bulk/{id?}', [HarvestsController::class, 'create_bulk'])->name('harvests.create.bulk');
    Route::post('/harvests/bulk', [HarvestsController::class, 'store_bulk'])->name('harvests.store.bulk');
    Route::get('/harvests/download/bulk/template', [HarvestsController::class, 'download_bulk_template'])->name('harvests.download.bulk.template');

    Route::get('/harvests/details', [HarvestDetailsController::class, 'create'])->name('harvests_details.create');
    Route::post('/harvests/details', [HarvestDetailsController::class, 'store'])->name('harvests_details.store');
    Route::get('/harvests/details/{code}', [HarvestDetailsController::class, 'find_by_code'])->name('harvests_details.find_by_code');

    Route::post('/importers', [ImportersController::class, 'store'])->name('importers.store');

    Route::get('/quarter/{id}/plants', [QuartersController::class, 'plants'])->name('quarters.plants');
    Route::put('/quarter/{id}/plants/position', [QuartersController::class, 'plants_update_position'])->name('quarters.plants.update.position');
    Route::get('/graphs/{id}/{type}', [GraphsController::class, 'index'])->name('graphs.index');

    Route::resources([
        'fields' => FieldsController::class,
        'quarters' => QuartersController::class,
        'plants' => PlantsController::class,
        'dogs' => DogsController::class,
        'harvests' => HarvestsController::class,
        'tools' => ToolsController::class,
        'machineries' => MachineriesController::class,
        'batches' => BatchesController::class,
        'liquidations' => LiquidationsController::class,
        'security_equipments' => SecurityEquipmentsController::class,

        'category_products' => CategoryProductsController::class,
        'importers' => ImportersController::class,
        'owners' => OwnersController::class,
        'plant_types' => PlantTypesController::class,
    ]);
});
