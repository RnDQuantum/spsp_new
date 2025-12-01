<?php

namespace Database\Factories;

use App\Models\Institution;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AssessmentEvent>
 */
class AssessmentEventFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startDate = fake()->dateTimeBetween('-1 year', 'now');
        $endDate = fake()->dateTimeBetween($startDate, '+6 months');

        return [
            'institution_id' => Institution::factory(),
            'code' => strtoupper(fake()->unique()->bothify('EVT-####')),
            'name' => fake()->words(3, true),
            'description' => fake()->sentence(),
            'year' => fake()->year(),
            'start_date' => $startDate,
            'end_date' => $endDate,
            'status' => fake()->randomElement(['draft', 'ongoing', 'completed']),
            'last_synced_at' => null,
        ];
    }
}
