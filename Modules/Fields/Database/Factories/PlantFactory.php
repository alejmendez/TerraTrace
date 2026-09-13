<?php

namespace Modules\Fields\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Fields\Models\Plant;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Modules\Fields\Models\Plant>
 */
class PlantFactory extends Factory
{
    protected $model = Plant::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'planned_at' => fake()->date(),
            'nursery_origin' => fake()->name(),
            'code' => strtoupper(fake()->lexify('??-??-??')),
            'row' => strtoupper(fake()->randomLetter().fake()->randomLetter()),
            'age' => fake()->numerify('###'),
        ];
    }
}
