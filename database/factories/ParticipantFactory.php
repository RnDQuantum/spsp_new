<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\AssessmentEvent;
use App\Models\Batch;
use App\Models\Participant;
use App\Models\PositionFormation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Participant>
 */
class ParticipantFactory extends Factory
{
    protected $model = Participant::class;

    public function definition(): array
    {
        $gender = fake()->randomElement(['L', 'P']);
        $firstName = $gender === 'L' ? fake()->firstNameMale() : fake()->firstNameFemale();
        $lastName = fake()->lastName();

        $degrees = ['S.Si', 'S.T', 'S.Kom', 'S.E', 'S.H', 'S.Ak', 'S.Psi', 'S.Pd', 'S.Sos'];
        $degree = fake()->randomElement($degrees);

        return [
            'username' => fake()->unique()->bothify('???##-###'),
            'test_number' => $this->generateTestNumber(),
            'skb_number' => fake()->unique()->numerify('244002401200#####'),
            'name' => strtoupper($firstName . ' ' . $lastName) . ', ' . $degree,
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->numerify('08##########'),
            'gender' => $gender,
            'photo_path' => null,
            'assessment_date' => fake()->dateTimeBetween('-30 days', 'now')->format('Y-m-d'),
        ];
    }

    /**
     * Generate unique test number
     */
    private function generateTestNumber(): string
    {
        $prefix = fake()->numerify('##-#-#-##');
        $sequence = fake()->unique()->numerify('###');

        return $prefix . '-' . $sequence;
    }

    /**
     * Set specific event
     */
    public function forEvent(AssessmentEvent $event): static
    {
        return $this->state(fn(array $attributes) => [
            'event_id' => $event->id,
        ]);
    }

    /**
     * Set specific batch
     */
    public function forBatch(Batch $batch): static
    {
        return $this->state(fn(array $attributes) => [
            'batch_id' => $batch->id,
            'event_id' => $batch->event_id,
        ]);
    }

    /**
     * Set specific position
     */
    public function forPosition(PositionFormation $position): static
    {
        return $this->state(fn(array $attributes) => [
            'position_formation_id' => $position->id,
        ]);
    }

    /**
     * Set assessment date
     */
    public function assessedOn(string $date): static
    {
        return $this->state(fn(array $attributes) => [
            'assessment_date' => $date,
        ]);
    }
}
