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
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('status');
            $table->string('repeat_number');
            $table->string('repeat_type');
            $table->string('priority');
            $table->date('start_date');
            $table->date('end_date');

            $table->integer('field_id')->unsigned()->nullable();
            $table->foreign('field_id')->references('id')->on('fields')->onUpdate('cascade')->onDelete('cascade');

            $table->integer('quarter_id')->unsigned()->nullable();
            $table->foreign('quarter_id')->references('id')->on('quarters')->onUpdate('cascade')->onDelete('cascade');

            $table->integer('plant_id')->unsigned()->nullable();
            $table->foreign('plant_id')->references('id')->on('plants')->onUpdate('cascade')->onDelete('cascade');

            $table->integer('responsible_id')->unsigned()->nullable();
            $table->foreign('responsible_id')->references('id')->on('users')->onUpdate('cascade')->onDelete('cascade');

            $table->text('note')->nullable();
            $table->text('comments')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
