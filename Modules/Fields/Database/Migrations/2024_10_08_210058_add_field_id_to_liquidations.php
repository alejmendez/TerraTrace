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
        Schema::table('liquidations', function (Blueprint $table) {
            $table->integer('field_id')->unsigned()->nullable();
            $table->foreign('field_id')->references('id')->on('fields')->onUpdate('cascade')->onDelete('cascade');
        });

        $field = DB::table('fields')->first();
        if ($field) {
            DB::table('liquidations')->update(['field_id' => $field->id]);
        }

        Schema::table('liquidations', function (Blueprint $table) {
            $table->integer('field_id')->unsigned()->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('liquidations', function ($table) {
            $table->dropColumn('field_id');
        });
    }
};
