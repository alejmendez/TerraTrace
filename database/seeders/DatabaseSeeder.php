<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Modules\Fields\Database\Seeders\CategoryProductSeeder;
use Modules\Fields\Database\Seeders\DogSeeder;
use Modules\Fields\Database\Seeders\FieldSeeder;
use Modules\Fields\Database\Seeders\HarvestSeeder;
use Modules\Fields\Database\Seeders\MachinerySeeder;
use Modules\Fields\Database\Seeders\OwnerSeeder;
use Modules\Fields\Database\Seeders\PlantSeeder;
use Modules\Fields\Database\Seeders\QuarterSeeder;
use Modules\Fields\Database\Seeders\SecurityEquipmentSeeder;
use Modules\Fields\Database\Seeders\ToolSeeder;
use Modules\Users\Database\Seeders\RolSeeder;
use Modules\Users\Database\Seeders\UserSeeder;
use Modules\Users\Models\User;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        if (User::count() > 0) {
            return;
        }

        $seeders = [
            RolSeeder::class,
            UserSeeder::class,
            CategoryProductSeeder::class,
        ];

        if (app()->isLocal()) {
            $seeders = [
                RolSeeder::class,
                UserSeeder::class,
                CategoryProductSeeder::class,
                FieldSeeder::class,
                QuarterSeeder::class,
                PlantSeeder::class,
                DogSeeder::class,
                ToolSeeder::class,
                MachinerySeeder::class,
                SecurityEquipmentSeeder::class,
                // OwnerSeeder::class,
                // HarvestSeeder::class,
            ];
        }
        $this->call($seeders);
    }
}
