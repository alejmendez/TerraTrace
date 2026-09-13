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
        Schema::table('tasks', function (Blueprint $table) {
            if (Schema::hasColumn('tasks', 'quarter_id')) {
                $table->dropColumn('quarter_id');
            }
        });

        Schema::create('quarter_task', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quarter_id')->constrained()->onDelete('cascade');
            $table->foreignId('task_id')->constrained()->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quarter_task');

        Schema::table('tasks', function (Blueprint $table) {
            $table->integer('quarter_id')->unsigned()->nullable();
            $table->foreign('quarter_id')->references('id')->on('quarters')->onUpdate('cascade')->onDelete('cascade');
        });
    }
};
