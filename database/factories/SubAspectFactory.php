<?php

namespace Database\Factories;

use App\Models\Aspect;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SubAspect>
 */
class SubAspectFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'aspect_id' => Aspect::factory(),
            'code' => strtolower(fake()->unique()->bothify('sub_####')),
            'name' => fake()->words(3, true),
            'description' => fake()->sentence(),
            'standard_rating' => fake()->numberBetween(1, 5),
            'order' => fake()->numberBetween(1, 10),
        ];
    }
}
