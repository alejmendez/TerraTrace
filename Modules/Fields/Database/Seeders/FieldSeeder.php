<?php

namespace Modules\Fields\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Fields\Models\Field;

class FieldSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Field::factory()->count(10)->create();
    }
}
