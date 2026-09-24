<?php

namespace Modules\Fields\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Fields\Models\Plant;
use Modules\Fields\Models\PlantType;
use Modules\Fields\Models\Quarter;

class PlantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        PlantType::create(['name' => 'Encina', 'slug' => 'encina']);

        $plant_types_ids = PlantType::all()->pluck('id')->all();
        $plant_types_ids_count = count($plant_types_ids) - 1;

        $quarters = Quarter::all();
        foreach ($quarters as $quarter) {
            // Each quarter must have plants, otherwise:
            //   - the dashboard's "Promedio gr por planta" renders 0
            //     (division by zero is guarded, but the value is still 0)
            //   - HarvestSeeder has nothing to attach HarvestDetails to
            // Range 8-25 keeps the field's total plant count realistic
            // (10 fields × 3 quarters × ~16 plants ≈ 480 plants total).
            Plant::factory(rand(8, 25))->create([
                'quarter_id' => $quarter->id,
                'plant_type_id' => $plant_types_ids[rand(0, $plant_types_ids_count)],
            ]);
        }

    }
}
