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

    private static int $counter = 0;

    public function definition(): array
    {
        $gender = fake()->randomElement(['L', 'P']);
        $firstName = $gender === 'L' ? fake()->firstNameMale() : fake()->firstNameFemale();
        $lastName = fake()->lastName();

        $degrees = ['S.Si', 'S.T', 'S.Kom', 'S.E', 'S.H', 'S.Ak', 'S.Psi', 'S.Pd', 'S.Sos'];
        $degree = fake()->randomElement($degrees);

        self::$counter++;

        return [
            'username' => $this->generateUsername(),
            'test_number' => $this->generateTestNumber(),
            'skb_number' => $this->generateSkbNumber(),
            'name' => strtoupper($firstName.' '.$lastName).', '.$degree,
            'email' => $this->generateEmail(),
            'phone' => fake()->numerify('08##########'),
            'gender' => $gender,
            'photo_path' => null,
            'assessment_date' => fake()->dateTimeBetween('-30 days', 'now')->format('Y-m-d'),
        ];
    }

    /**
     * Generate unique username using counter
     */
    private function generateUsername(): string
    {
        $letters = fake()->bothify('???');
        $numbers = str_pad((string) (self::$counter % 100), 2, '0', STR_PAD_LEFT);
        $suffix = str_pad((string) ((int) (self::$counter / 100)), 3, '0', STR_PAD_LEFT);

        return strtoupper($letters.$numbers.'-'.$suffix);
    }

    /**
     * Generate unique test number using counter
     */
    private function generateTestNumber(): string
    {
        $prefix = fake()->numerify('##-#-#-##');
        $sequence = str_pad((string) self::$counter, 5, '0', STR_PAD_LEFT);

        return $prefix.'-'.$sequence;
    }

    /**
     * Generate unique SKB number using counter
     */
    private function generateSkbNumber(): string
    {
        $baseNumber = str_pad((string) self::$counter, 5, '0', STR_PAD_LEFT);

        return '244002401200'.$baseNumber;
    }

    /**
     * Generate unique email using counter
     */
    private function generateEmail(): string
    {
        $providers = ['gmail.com', 'yahoo.com', 'hotmail.com', 'outlook.com'];
        $provider = fake()->randomElement($providers);

        return 'participant'.self::$counter.'@'.$provider;
    }

    /**
     * Reset the counter (useful for seeding fresh data)
     */
    public static function resetCounter(): void
    {
        self::$counter = 0;
    }

    /**
     * Set specific event
     */
    public function forEvent(AssessmentEvent $event): static
    {
        return $this->state(fn (array $attributes) => [
            'event_id' => $event->id,
        ]);
    }

    /**
     * Set specific batch
     */
    public function forBatch(Batch $batch): static
    {
        return $this->state(fn (array $attributes) => [
            'batch_id' => $batch->id,
            'event_id' => $batch->event_id,
        ]);
    }

    /**
     * Set specific position
     */
    public function forPosition(PositionFormation $position): static
    {
        return $this->state(fn (array $attributes) => [
            'position_formation_id' => $position->id,
        ]);
    }

    /**
     * Set assessment date
     */
    public function assessedOn(string $date): static
    {
        return $this->state(fn (array $attributes) => [
            'assessment_date' => $date,
        ]);
    }
}
