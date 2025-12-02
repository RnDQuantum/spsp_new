<?php

namespace Database\Factories;

use App\Models\AssessmentEvent;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Batch>
 */
class BatchFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        static $batchCounter = 1;

        $startDate = fake()->dateTimeBetween('-1 month', '+1 month');
        $endDate = fake()->dateTimeBetween($startDate, '+2 months');

        return [
            'event_id' => AssessmentEvent::factory(),
            'code' => 'BATCH-'.str_pad((string) $batchCounter, 3, '0', STR_PAD_LEFT),
            'name' => 'Batch '.$batchCounter++,
            'batch_number' => $batchCounter - 1,
            'location' => fake()->city(),
            'start_date' => $startDate,
            'end_date' => $endDate,
        ];
    }
}
