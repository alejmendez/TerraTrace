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
        Schema::table('dogs', function (Blueprint $table) {
            $table->integer('field_id')->unsigned()->nullable();
            $table->foreign('field_id')->references('id')->on('fields')->onUpdate('cascade')->onDelete('cascade');
        });

        DB::table('dogs')->get()->each(function ($dog) {
            $field = DB::table('quarters')->where('id', $dog->quarter_id)->first();
            DB::table('dogs')->where('id', $dog->id)->update(['field_id' => $field->field_id]);
        });

        Schema::table('dogs', function (Blueprint $table) {
            $table->dropColumn('quarter_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dogs', function (Blueprint $table) {
            $table->integer('quarter_id')->unsigned()->nullable();
            $table->foreign('quarter_id')->references('id')->on('quarters')->onUpdate('cascade')->onDelete('cascade');
            $table->dropColumn('field_id');
        });
    }
};
