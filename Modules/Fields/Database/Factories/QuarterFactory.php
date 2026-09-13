<?php

namespace Modules\Fields\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Fields\Models\Quarter;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Modules\Fields\Models\Quarter>
 */
class QuarterFactory extends Factory
{
    protected $model = Quarter::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => 'quarter '.fake()->name(),
            'area' => fake()->numerify('##.###'),
        ];
    }
}
