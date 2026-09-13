<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('harvests', function (Blueprint $table) {
            $table->decimal('weight', total: 8, places: 2)->nullable();
        });

        DB::table('harvests')->get()->each(function ($harvest) {
            $totalWeight = DB::table('harvest_details')
                ->where('harvest_id', $harvest->id)
                ->sum('weight');

            DB::table('harvests')
                ->where('id', $harvest->id)
                ->update(['weight' => $totalWeight]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('harvests', function (Blueprint $table) {
            $table->dropColumn('weight');
        });
    }
};
