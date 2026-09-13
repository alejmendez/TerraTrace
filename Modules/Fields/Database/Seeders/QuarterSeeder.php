<?php

namespace Modules\Fields\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Fields\Models\Field;
use Modules\Fields\Models\Quarter;
use Modules\Users\Models\User;

class QuarterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $fields = Field::all();
        // $users = User::role('Agricultor')->get();
        $users = User::all();
        $usersCount = count($users);
        foreach ($fields as $field) {
            Quarter::factory(rand(0, 6))->create([
                'field_id' => $field->id,
                'responsible_id' => $users[rand(0, $usersCount - 1)]->id,
            ]);
        }
    }
}
