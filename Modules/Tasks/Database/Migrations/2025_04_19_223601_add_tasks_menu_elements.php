<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $module = DB::table('modules')->where('slug', 'tasks')->first();

        $menu = [
            [
                'text' => 'menu.task_management',
                'children' => [
                    [
                        'text' => 'menu.tasks',
                        'link' => 'tasks.index',
                        'icon' => '<span class="material-symbols-rounded">checklist</span>',
                        'active_with' => 'tasks.*',
                    ],
                ],
            ],
        ];

        $menuGroupOrder = 0;
        foreach ($menu as $item) {
            $menuElementOrder = 0;
            $menuGroup = DB::table('menus')->where('text', $item['text'])->first();
            if (! $menuGroup) {
                $menuGroupId = DB::table('menus')->insertGetId([
                    'text' => $item['text'],
                    'order' => $menuGroupOrder++,
                ]);
                $menuGroup = (object) ['id' => $menuGroupId];
            } else {
                DB::table('menus')->where('id', $menuGroup->id)->update([
                    'order' => $menuGroupOrder++,
                ]);
            }

            foreach ($item['children'] as $child) {
                $menuItem = DB::table('menus')->where('text', $child['text'])->first();
                if (! $menuItem) {
                    $menuItem = DB::table('menus')->insert([
                        'text' => $child['text'],
                        'link' => $child['link'],
                        'icon' => $child['icon'],
                        'active_with' => $child['active_with'],
                        'parent_id' => $menuGroup->id,
                        'order' => $menuElementOrder++,
                        'module_id' => $module->id,
                    ]);
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $module = DB::table('modules')->where('slug', 'tasks')->first();
        DB::table('menus')->where('module_id', $module->id)->delete();
    }
};
