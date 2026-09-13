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
        Schema::create('menus', function (Blueprint $table) {
            $table->id();
            $table->string('text', 200);
            $table->string('link', 200)->nullable();
            $table->string('icon', 100)->nullable();
            $table->integer('order')->default(0);
            $table->integer('parent_id')->nullable();

            $table->integer('type')->default(0);
            $table->string('active_with', 200)->nullable();

            $table->integer('module_id')->unsigned()->nullable();
            $table->foreign('module_id')->references('id')->on('modules')->onUpdate('cascade')->onDelete('cascade');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('menus');
    }
};
