<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * The previous `add_auth_module` migration inserted a duplicate
     * Dashboard row instead of an Auth one (copy-paste bug). This
     * migration:
     *   1. Deletes the duplicate Dashboard row (keeping the original
     *      from `add_dashboard_module`).
     *   2. Inserts the correct Auth row.
     */
    public function up(): void
    {
        // Two rows with slug='dashboard' can exist because the bug
        // inserted one alongside the legitimate Dashboard migration.
        // Remove the extras (keeping the earliest one with id=1).
        $duplicates = DB::table('modules')
            ->where('slug', 'dashboard')
            ->orderBy('id')
            ->skip(1)
            ->take(PHP_INT_MAX)
            ->pluck('id');

        if ($duplicates->isNotEmpty()) {
            DB::table('modules')->whereIn('id', $duplicates)->delete();
        }

        if (! DB::table('modules')->where('slug', 'auth')->exists()) {
            DB::table('modules')->insert([
                'name' => 'Auth',
                'slug' => 'auth',
                'description' => 'Auth module',
                'version' => '1.0.0',
                'is_active' => true,
            ]);
        }
    }

    public function down(): void
    {
        DB::table('modules')->where('slug', 'auth')->delete();
        DB::table('modules')->insert([
            'name' => 'Dashboard',
            'slug' => 'dashboard',
            'description' => 'Dashboard module (legacy)',
            'version' => '1.0.0',
            'is_active' => true,
        ]);
    }
};
