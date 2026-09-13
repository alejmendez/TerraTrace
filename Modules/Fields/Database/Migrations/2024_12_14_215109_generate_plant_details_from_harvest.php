<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('harvest_details')->where('harvest_id', '!=', null)->get()->each(function ($harvestDetail) {
            DB::table('plant_details')
                ->where('plant_id', $harvestDetail->plant_id)
                ->where('type', 'harvest')
                ->update(['is_active' => false]);

            DB::table('plant_details')->insert([
                'plant_id' => $harvestDetail->plant_id,
                'type' => 'harvest',
                'value' => $harvestDetail->weight,
                'is_active' => true,
                'created_at' => $harvestDetail->created_at,
                'updated_at' => $harvestDetail->updated_at,
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('plant_details')->where('type', 'harvest')->delete();
    }
};
