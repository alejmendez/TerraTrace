<?php

namespace Modules\Fields\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Fields\Models\Field;
use Modules\Fields\Models\Owner;

/**
 * @extends Factory<Field>
 */
class FieldFactory extends Factory
{
    protected $model = Field::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $owner = Owner::factory(1)->create();

        return [
            'name' => 'field '.fake()->name(),
            'location' => fake()->numerify('##.######, -##.######'), // 41.191374, -95.394946
            'size' => fake()->numerify('###'), // 123 m²
            'owner_id' => $owner->first()->id,
        ];
    }
}
