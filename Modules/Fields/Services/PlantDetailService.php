<?php

namespace Modules\Fields\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Modules\Fields\Models\PlantDetail;
use Modules\Fields\Services\Plants\FindPlantByCode;

class PlantDetailService
{
    /**
     * Detail types we accept from the request payload. Each becomes a
     * row in plant_details (one row per active detail, deactivated
     * rows kept for history). 'note' is intentionally NOT in this
     * list -- notes live on the same row as their sibling detail
     * type (see the $detailsToCreate loop below).
     */
    private const PROCESSABLE_TYPES = [
        'height',
        'crown_diameter',
        'invasion_radius',
        'trunk_diameter',
        'root_diameter',
        'foliage_sanitation',
        'foliage_sanitation_photo',
        'trunk_sanitation',
        'trunk_sanitation_photo',
        'soil_sanitation',
        'soil_sanitation_photo',
        'irrigation_system',
    ];

    /**
     * Insert the supplied detail measurements for the plant identified
     * by code. Any existing active row for the same type is deactivated
     * (the new one wins, but history is preserved).
     *
     * Cross-module call to FindPlantByCode::call() stays static: Plants
     * module has not been consolidated yet, so the transversal lookup
     * still lives in a plain static class.
     */
    public function create(array $data): void
    {
        $plant = FindPlantByCode::call($data['plant_code']);

        if (! $plant) {
            throw new \Exception('Planta no encontrada con el código proporcionado.');
        }

        $activeDetails = $plant->activeDetails()->get()->keyBy('type');

        $detailsToCreate = [];
        $typesToDeactivate = [];

        foreach (self::PROCESSABLE_TYPES as $type) {
            if (! isset($data[$type]) || is_null($data[$type])) {
                continue;
            }

            if (isset($activeDetails[$type])) {
                $typesToDeactivate[] = $activeDetails[$type]->id;
            }

            $detailsToCreate[] = [
                'plant_id' => $plant->id,
                'type' => $type,
                'value' => $data[$type],
                'note' => $data['notes'][$type] ?? null,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        if (! empty($typesToDeactivate)) {
            PlantDetail::whereIn('id', $typesToDeactivate)->update(['is_active' => false]);
        }

        if (! empty($detailsToCreate)) {
            PlantDetail::insert($detailsToCreate);
        }
    }

    /**
     * Listing methods used by the JSON endpoint. Kept as three
     * distinct query entry points (plant / quarter / field) because
     * each has a different scope -- the quarter/field variants use
     * raw sub-queries against plants and quarters tables since those
     * relationships don't have a one-hop model path. Promote to
     * query scopes later if a fourth variant appears.
     */
    public function getByPlant(int $plantId, ?int $year = null, bool $showHarvests = false): Collection
    {
        return $this->runQuery(
            PlantDetail::with('plant')->where('plant_id', $plantId),
            $year,
            $showHarvests
        );
    }

    public function getByQuarter(int $quarterId, ?int $year = null, bool $showHarvests = false): Collection
    {
        return $this->runQuery(
            PlantDetail::with('plant')->whereRaw(
                'plant_id IN (SELECT id FROM plants WHERE quarter_id = ?)',
                [$quarterId]
            ),
            $year,
            $showHarvests
        );
    }

    public function getByField(int $fieldId, ?int $year = null, bool $showHarvests = false): Collection
    {
        return $this->runQuery(
            PlantDetail::with('plant')->whereRaw(
                'plant_id IN (SELECT id FROM plants WHERE quarter_id IN (SELECT id FROM quarters WHERE field_id = ?))',
                [$fieldId]
            ),
            $year,
            $showHarvests
        );
    }

    private function runQuery(Builder $query, ?int $year, bool $showHarvests): Collection
    {
        if (! $showHarvests) {
            $query->where('type', '!=', 'harvest');
        }

        if ($year) {
            $query->whereYear('created_at', $year);
        }

        return $query->orderBy('created_at', 'desc')->get();
    }
}
