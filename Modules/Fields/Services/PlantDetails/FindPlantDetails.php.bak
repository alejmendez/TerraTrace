<?php

namespace Modules\Fields\Services\PlantDetails;

use Modules\Fields\Models\Plant;
use Modules\Fields\Models\PlantDetail;

class FindPlantDetails
{
    public static function get_by_plant(Plant $plant, $year, $show_harvests = false)
    {
        return self::get_by_plant_id($plant->id, $year, $show_harvests);
    }

    public static function get_by_plant_id($id, $year, $show_harvests = false)
    {
        $query = PlantDetail::with('plant')->where('plant_id', $id);

        return self::get_details($query, $year, $show_harvests);
    }

    public static function get_by_quarter_id($id, $year, $show_harvests = false)
    {
        $query = PlantDetail::with('plant')->whereRaw('plant_id IN (SELECT id FROM plants WHERE quarter_id = ?)', [$id]);

        return self::get_details($query, $year, $show_harvests);
    }

    public static function get_by_field_id($id, $year, $show_harvests = false)
    {
        $query = PlantDetail::with('plant')->whereRaw('plant_id IN (SELECT id FROM plants WHERE quarter_id in (SELECT id FROM quarters WHERE field_id = ?))', [$id]);

        return self::get_details($query, $year, $show_harvests);
    }

    public static function get_details($query, $year, $show_harvests = false)
    {
        if (! $show_harvests) {
            $query->where('type', '!=', 'harvest');
        }

        if ($year) {
            $query->whereYear('created_at', $year);
        }

        return $query->orderBy('created_at', 'desc')->get();
    }
}
