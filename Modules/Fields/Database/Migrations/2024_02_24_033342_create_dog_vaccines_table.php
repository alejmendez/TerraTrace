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
        Schema::create('dog_vaccines', function (Blueprint $table) {
            $table->id();
            $table->string('name', 80);
            $table->date('date');
            $table->string('code', 50);

            $table->integer('dog_id')->unsigned()->nullable();
            $table->foreign('dog_id')->references('id')->on('dogs')->onUpdate('cascade')->onDelete('cascade');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dog_vaccines');
    }
};
