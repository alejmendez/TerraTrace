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
        Schema::table('security_equipments', function (Blueprint $table) {
            $table->date('last_maintenance')->nullable()->change();
        });

        Schema::table('tools', function (Blueprint $table) {
            $table->date('last_maintenance')->nullable()->change();
        });

        Schema::table('machineries', function (Blueprint $table) {
            $table->date('last_maintenance')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('security_equipments', function (Blueprint $table) {
            $table->date('last_maintenance')->nullable(false)->change();
        });

        Schema::table('tools', function (Blueprint $table) {
            $table->date('last_maintenance')->nullable(false)->change();
        });

        Schema::table('machineries', function (Blueprint $table) {
            $table->date('last_maintenance')->nullable(false)->change();
        });
    }
};
