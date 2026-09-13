<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('harvest_details', function ($table) {
            $table->integer('quarter_id')->unsigned()->nullable();
            $table->foreign('quarter_id')->references('id')->on('quarters')->onUpdate('cascade')->onDelete('cascade');
        });

        DB::table('harvest_details')->get()->each(function ($detail) {
            $quarter_id = DB::table('plants')->find($detail->plant_id)->quarter_id;
            DB::table('harvest_details')
                ->where('id', $detail->id)
                ->update(['quarter_id' => $quarter_id]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('harvest_details', function ($table) {
            $table->dropColumn('quarter_id');
        });
    }
};
