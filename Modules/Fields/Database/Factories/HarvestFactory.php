<?php

namespace Modules\Fields\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Fields\Models\Harvest;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Modules\Fields\Models\Harvest>
 */
class HarvestFactory extends Factory
{
    protected $model = Harvest::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            //
        ];
    }
}
