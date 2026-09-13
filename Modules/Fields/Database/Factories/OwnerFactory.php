<?php

namespace Modules\Fields\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Fields\Models\Owner;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Modules\Fields\Models\Owner>
 */
class OwnerFactory extends Factory
{
    protected $model = Owner::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'dni' => fake()->numerify('##.###.###-#'),
        ];
    }
}
