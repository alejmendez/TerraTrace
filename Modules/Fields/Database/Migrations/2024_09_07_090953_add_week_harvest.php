<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('harvests', function (Blueprint $table) {
            $table->integer('week')->nullable();
        });

        DB::table('harvests')->get()->each(function ($harvest) {
            $week = \Illuminate\Support\Carbon::parse($harvest->date)->weekOfYear;
            DB::table('harvests')
                ->where('id', $harvest->id)
                ->update(['week' => $week]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('harvests', function (Blueprint $table) {
            $table->dropColumn('week');
        });
    }
};
