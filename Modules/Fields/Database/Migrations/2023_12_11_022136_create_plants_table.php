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
        Schema::create('plants', function (Blueprint $table) {
            $table->id();
            $table->date('planned_at');
            $table->string('nursery_origin', 80);
            $table->string('code', 20);
            $table->string('row', 2);
            $table->decimal('age', total: 5, places: 2);
            $table->string('blueprint')->nullable();

            $table->integer('quarter_id')->unsigned()->nullable();
            $table->foreign('quarter_id')->references('id')->on('quarters')->onUpdate('cascade')->onDelete('cascade');

            $table->integer('plant_type_id')->unsigned()->nullable();
            $table->foreign('plant_type_id')->references('id')->on('plant_types')->onUpdate('cascade')->onDelete('cascade');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plants');
    }
};
