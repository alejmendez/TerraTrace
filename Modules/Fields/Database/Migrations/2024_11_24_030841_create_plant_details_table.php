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
        Schema::create('plant_details', function (Blueprint $table) {
            $table->id();

            $table->string('type', 255);
            $table->string('value', 255);
            $table->boolean('is_active')->default(true);

            $table->integer('plant_id')->unsigned()->nullable();
            $table->foreign('plant_id')->references('id')->on('plants')->onUpdate('cascade')->onDelete('cascade');

            $table->index(['plant_id', 'is_active']);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plant_details');
    }
};
