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
        Schema::create('batch_harvest', function (Blueprint $table) {
            $table->id();
            $table->integer('harvest_id')->unsigned()->nullable();
            $table->foreign('harvest_id')->references('id')->on('harvests')->onUpdate('cascade')->onDelete('cascade');

            $table->integer('batch_id')->unsigned()->nullable();
            $table->foreign('batch_id')->references('id')->on('batches')->onUpdate('cascade')->onDelete('cascade');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('batch_harvest');
    }
};
