<?php

namespace Database\Factories;

use App\Models\AssessmentTemplate;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AssessmentTemplate>
 */
class AssessmentTemplateFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => strtoupper(fake()->unique()->bothify('TPL-####')),
            'name' => fake()->words(3, true),
            'description' => fake()->sentence(),
        ];
    }
}
