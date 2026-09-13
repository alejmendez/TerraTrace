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
        Schema::table('harvest_details', function (Blueprint $table) {
            $table->decimal('weight', total: 8, places: 2)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('harvest_details', function (Blueprint $table) {
            $table->decimal('weight', total: 8, places: 2)->nullable(false)->change();
        });

        DB::table('harvest_details')
            ->whereNull('weight')
            ->update(['weight' => 0]);
    }
};
