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
        Schema::create('quarters', function (Blueprint $table) {
            $table->id();
            $table->string('name', 80);
            $table->decimal('area', total: 5, places: 2);

            $table->string('blueprint')->nullable();

            $table->integer('responsible_id')->unsigned()->nullable();
            $table->foreign('responsible_id')->references('id')->on('users')->onUpdate('cascade')->onDelete('cascade');

            $table->integer('field_id')->unsigned()->nullable();
            $table->foreign('field_id')->references('id')->on('fields')->onUpdate('cascade')->onDelete('cascade');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quarters');
    }
};
