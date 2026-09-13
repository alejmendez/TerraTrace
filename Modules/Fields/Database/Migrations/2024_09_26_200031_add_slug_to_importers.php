<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('importers', function (Blueprint $table) {
            $table->string('slug', 80)->nullable();
        });

        DB::table('importers')->get()->each(function ($importer) {
            DB::table('importers')
                ->where('id', $importer->id)
                ->update(['slug' => Str::slug($importer->name)]);
        });

        Schema::table('importers', function (Blueprint $table) {
            $table->string('slug', 80)->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('importers', function ($table) {
            $table->dropColumn('slug');
        });
    }
};
