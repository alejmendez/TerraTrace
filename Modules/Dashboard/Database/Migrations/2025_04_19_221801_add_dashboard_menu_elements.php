<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $dashboardModule = DB::table('modules')->where('slug', 'dashboard')->first();

        DB::table('menus')->insert([
            'text' => 'menu.dashboard',
            'link' => 'dashboard.index',
            'icon' => '<span class="material-symbols-rounded">insert_chart</span>',
            'order' => 0,
            'parent_id' => null,
            'module_id' => $dashboardModule->id,
            'active_with' => 'dashboard.*',
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $dashboardModule = DB::table('modules')->where('slug', 'dashboard')->first();
        DB::table('menus')->where('module_id', $dashboardModule->id)->delete();
    }
};
