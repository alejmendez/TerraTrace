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
        Schema::create('security_equipments', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->date('purchase_date');
            $table->date('last_maintenance');
            $table->string('purchase_location');
            $table->string('type');
            $table->string('contact');
            $table->text('note')->nullable();
            $table->timestamps();
        });

        Schema::create('security_equipment_task', function (Blueprint $table) {
            $table->id();

            $table->integer('task_id')->unsigned()->nullable();
            $table->foreign('task_id')->references('id')->on('tasks')->onUpdate('cascade')->onDelete('cascade');

            $table->integer('security_equipment_id')->unsigned()->nullable();
            $table->foreign('security_equipment_id')->references('id')->on('security_equipments')->onUpdate('cascade')->onDelete('cascade');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('security_equipment_task');
        Schema::dropIfExists('security_equipments');
    }
};
