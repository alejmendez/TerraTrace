<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('modules')->insert([
            'name' => 'Fields',
            'slug' => 'fields',
            'description' => 'Fields module',
            'version' => '1.0.0',
            'is_active' => true,
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('modules')->where('slug', 'fields')->delete();
    }
};
