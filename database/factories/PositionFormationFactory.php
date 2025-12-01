<?php

namespace Database\Factories;

use App\Models\AssessmentEvent;
use App\Models\AssessmentTemplate;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PositionFormation>
 */
class PositionFormationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'event_id' => AssessmentEvent::factory(),
            'template_id' => AssessmentTemplate::factory(),
            'code' => strtoupper(fake()->unique()->bothify('POS-####')),
            'name' => fake()->jobTitle(),
            'quota' => fake()->numberBetween(1, 50),
        ];
    }
}
