<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $module = DB::table('modules')->where('slug', 'fields')->first();

        $menu = [
            [
                'text' => 'menu.management',
                'children' => [
                    [
                        'text' => 'menu.fields',
                        'link' => 'fields.index',
                        'icon' => '<span class="material-symbols-outlined">grid_on</span>',
                        'active_with' => 'fields.*',
                    ],
                    [
                        'text' => 'menu.quarters',
                        'link' => 'quarters.index',
                        'icon' => '<span class="material-symbols-outlined">border_all</span>',
                        'active_with' => 'quarters.*',
                    ],
                    [
                        'text' => 'menu.plants',
                        'link' => 'plants.index',
                        'icon' => '<span class="material-symbols-outlined">eco</span>',
                        'active_with' => 'plants.*',
                    ],
                ],
            ],
            [
                'text' => 'menu.harvest_management',
                'children' => [
                    [
                        'text' => 'menu.harvest',
                        'link' => 'harvests.index',
                        'icon' => '<span class="material-symbols-outlined">shopping_basket</span>',
                        'active_with' => 'harvests.*',
                    ],
                    [
                        'text' => 'menu.harvest_details',
                        'link' => 'harvests_details.create',
                        'icon' => '<span class="material-symbols-outlined">barcode</span>',
                        'active_with' => 'harvests_details.*',
                    ],

                    [
                        'text' => 'menu.batch',
                        'link' => 'batches.index',
                        'icon' => '<span class="material-symbols-outlined">format_list_bulleted</span>',
                        'active_with' => 'batches.*',
                    ],

                    [
                        'text' => 'menu.liquidations',
                        'link' => 'liquidations.index',
                        'icon' => '<span class="material-symbols-outlined">request_quote</span>',
                        'active_with' => 'liquidations.*',
                    ],
                ],
            ],
            [
                'text' => 'menu.records',
                'children' => [
                    [
                        'text' => 'menu.machineries',
                        'link' => 'machineries.index',
                        'icon' => '<span class="material-symbols-outlined">agriculture</span>',
                        'active_with' => 'machineries.*',
                    ],
                    [
                        'text' => 'menu.tools',
                        'link' => 'tools.index',
                        'icon' => '<span class="material-symbols-outlined">build</span>',
                        'active_with' => 'tools.*',
                    ],
                    [
                        'text' => 'menu.security_equipments',
                        'link' => 'security_equipments.index',
                        'icon' => '<span class="material-symbols-outlined">shield_with_heart</span>',
                        'active_with' => 'security_equipments.*',
                    ],
                ],
            ],
            [
                'text' => 'menu.settings',
                'children' => [
                    [
                        'text' => 'menu.dogs',
                        'link' => 'dogs.index',
                        'icon' => '<span class="material-symbols-outlined">pets</span>',
                        'active_with' => 'dogs.*',
                    ],
                    [
                        'text' => 'menu.bulk_uploads',
                        'link' => 'bulk.index',
                        'icon' => '<span class="material-symbols-outlined">unarchive</span>',
                        'active_with' => 'bulk.*',
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
        $module = DB::table('modules')->where('slug', 'fields')->first();
        DB::table('menus')->where('module_id', $module->id)->delete();
    }
};
