<?php

namespace Database\Factories;

use App\Models\AssessmentTemplate;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CategoryType>
 */
class CategoryTypeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'template_id' => AssessmentTemplate::factory(),
            'code' => strtolower(fake()->unique()->word()),
            'name' => fake()->words(2, true),
            'weight_percentage' => fake()->numberBetween(20, 80),
            'order' => fake()->numberBetween(1, 10),
        ];
    }
}
