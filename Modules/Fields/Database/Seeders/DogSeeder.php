<?php

namespace Modules\Fields\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Fields\Models\Dog;
use Modules\Fields\Models\Quarter;
use Modules\Users\Models\User;

class DogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $quarters = Quarter::all();
        $users = User::all();
        $usersCount = count($users);
        foreach ($quarters as $quarter) {
            Dog::factory(rand(0, 2))->create([
                'field_id' => $quarter->field_id,
                'couple_id' => $users[rand(0, $usersCount - 1)]->id,
            ]);
        }
    }
}
