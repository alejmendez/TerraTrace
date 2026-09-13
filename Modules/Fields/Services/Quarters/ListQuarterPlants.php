<?php

namespace Modules\Fields\Services\Quarters;

use Illuminate\Support\Facades\DB;
use Modules\Fields\Models\Harvest;
use Modules\Fields\Models\HarvestDetail;
use Modules\Fields\Models\Plant;

class ListQuarterPlants
{
    public static function call($id)
    {
        $harvests = Harvest::whereHas('quarters', function ($q) use ($id) {
            $q->where('quarters.id', $id);
        })
            ->get()
            ->map(fn ($a) => $a->only(['id', 'year', 'week', 'date', 'batch']));

        $harvestDetails = HarvestDetail::with('harvest')->where('quarter_id', $id)->get();
        $maxWeight = HarvestDetail::where('quarter_id', $id)
            ->groupBy('plant_id')
            ->select(DB::raw('sum(weight) as weight'))
            ->get()
            ->max('weight');
        $maxWeight = floatval($maxWeight);

        $maxQuantityByPlant = HarvestDetail::where('quarter_id', $id)
            ->groupBy('plant_id')
            ->select(DB::raw('count(*) as count'))
            ->get()
            ->max('count');
        $maxQuantityByPlant = floatval($maxQuantityByPlant);

        $harvestDetailsByPlantId = $harvestDetails->groupBy('plant_id');

        $plants = Plant::select('id', 'code', 'row', 'position')
            ->where('quarter_id', $id)
            ->orderBy('id')
            ->get()
            ->map(function ($plant) use ($harvestDetailsByPlantId, $maxWeight, $maxQuantityByPlant) {
                $plant->data = [];

                $detail = $harvestDetailsByPlantId->get($plant->id);
                $plant->scaleByWeight = 0;
                $plant->scaleByQuantity = 0;

                if ($detail) {
                    $quantityPlant = $detail->count();
                    $plant->scaleByWeight = round($detail->sum('weight') * 100 / $maxWeight, 2);
                    $plant->scaleByQuantity = round($quantityPlant * 100 / $maxQuantityByPlant, 2);
                    $detail = $detail->map(fn ($a) => $a->only(['id', 'harvest_id', 'quality', 'weight']))
                        ->map(function ($a) use ($maxWeight, $maxQuantityByPlant, $quantityPlant) {
                            $a['weight'] = floatval($a['weight']);
                            $a['scaleByWeight'] = round($a['weight'] * 100 / $maxWeight, 2);
                            $a['scaleByQuantity'] = round($quantityPlant * 100 / $maxQuantityByPlant, 2);

                            return $a;
                        })
                        ->groupBy('harvest_id');
                }
                $plant->data = $detail;

                return $plant;
            });

        return [
            'harvests' => $harvests,
            'plants' => $plants,
        ];
    }
}
