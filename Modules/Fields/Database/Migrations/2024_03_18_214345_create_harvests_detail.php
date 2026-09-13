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
        Schema::create('harvest_details', function (Blueprint $table) {
            $table->id();

            $table->integer('harvest_id')->unsigned()->nullable();
            $table->foreign('harvest_id')->references('id')->on('harvests')->onUpdate('cascade')->onDelete('cascade');

            $table->integer('plant_id')->unsigned()->nullable();
            $table->foreign('plant_id')->references('id')->on('plants')->onUpdate('cascade')->onDelete('cascade');

            $table->string('quality');
            $table->decimal('weight', total: 5, places: 2);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('harvest_details');
    }
};
