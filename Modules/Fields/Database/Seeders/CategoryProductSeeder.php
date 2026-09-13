<?php

namespace Modules\Fields\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Fields\Models\CategoryProduct;

class CategoryProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            ['EXTRA CLASS', true],
            ['FIRST CLASS', true],
            ['BIG TRUFFLES (>150 gr)', true],
            ['SECOND CLASS', true],
            ['SMALL', true],
            ['EXTRA SMALL (9-19 gr)', true],
            ['Mini (menor que 9 gr)', true],
            ['PIECES', true],
            ['Industria', true],
            ['Reclasificacion (industrial)', true],
            ['Blandas', false],
            ['Podridas', false],
            ['Rechazo', false],
            ['Con larvas', false],
            ['Inmaduras', false],
        ];

        foreach ($categories as $categoryRow) {
            CategoryProduct::create([
                'name' => $categoryRow[0],
                'is_commercial' => $categoryRow[1],
            ]);
        }
    }
}
