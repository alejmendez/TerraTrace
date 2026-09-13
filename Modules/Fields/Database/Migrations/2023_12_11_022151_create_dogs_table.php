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
        Schema::create('dogs', function (Blueprint $table) {
            $table->id();
            $table->string('name', 80);
            $table->string('breed', 80);
            $table->string('gender', 80);
            $table->date('birthdate');
            $table->string('veterinary', 80);

            $table->string('avatar', 250)->nullable();

            $table->integer('couple_id')->unsigned()->nullable();
            $table->foreign('couple_id')->references('id')->on('users')->onUpdate('cascade')->onDelete('cascade');

            $table->integer('quarter_id')->unsigned()->nullable();
            $table->foreign('quarter_id')->references('id')->on('quarters')->onUpdate('cascade')->onDelete('cascade');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dogs');
    }
};
