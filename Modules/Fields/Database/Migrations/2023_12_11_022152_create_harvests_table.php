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
        Schema::create('harvests', function (Blueprint $table) {
            $table->id();

            $table->date('date');
            $table->string('batch', 2);

            $table->integer('dog_id')->unsigned()->nullable();
            $table->foreign('dog_id')->references('id')->on('dogs')->onUpdate('cascade')->onDelete('cascade');

            $table->integer('farmer_id')->unsigned()->nullable();
            $table->foreign('farmer_id')->references('id')->on('users')->onUpdate('cascade')->onDelete('cascade');

            $table->integer('assistant_id')->unsigned()->nullable();
            $table->foreign('assistant_id')->references('id')->on('users')->onUpdate('cascade')->onDelete('cascade');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('harvests');
    }
};
