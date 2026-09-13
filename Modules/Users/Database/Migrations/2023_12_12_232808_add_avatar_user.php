<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function ($table) {
            $table->string('dni', 32)->unique();
            $table->string('last_name', 80)->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('avatar')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function ($table) {
            $table->dropColumn('dni');
            $table->dropColumn('last_name');
            $table->dropColumn('phone');
            $table->dropColumn('avatar');
        });
    }
};
