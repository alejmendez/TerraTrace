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
        Schema::create('liquidations', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->integer('week');
            $table->integer('year');

            $table->date('delivery_date');
            $table->date('reception_date');

            $table->decimal('weight_with_earth', total: 8, places: 2);
            $table->decimal('weight_washed', total: 8, places: 2);
            $table->decimal('dollar_value', total: 8, places: 2);

            $table->integer('importer_id')->unsigned()->nullable();
            $table->foreign('importer_id')->references('id')->on('importers')->onUpdate('cascade')->onDelete('cascade');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('liquidations');
    }
};
