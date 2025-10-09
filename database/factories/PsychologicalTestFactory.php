<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Participant;
use App\Models\PsychologicalTest;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PsychologicalTest>
 */
class PsychologicalTestFactory extends Factory
{
    protected $model = PsychologicalTest::class;

    public function definition(): array
    {
        $rawScore = fake()->randomFloat(2, 30, 50);
        $iqScore = fake()->numberBetween(85, 125);

        // Determine conclusion based on raw score
        $isGood = $rawScore >= 45;
        $isMedium = $rawScore >= 38 && $rawScore < 45;

        $conclusionCode = $isGood ? 'MS' : ($isMedium ? 'MS' : 'TMS');
        $conclusionText = $isGood
            ? 'MEMENUHI SYARAT (MS)'
            : ($isMedium ? 'MEMENUHI SYARAT (MS)' : 'TIDAK MEMENUHI SYARAT (TMS)');

        return [
            'raw_score' => $rawScore,
            'iq_score' => $iqScore,
            'validity_status' => $isGood
                ? 'Hasil tes ini konsisten dan dapat dipercaya'
                : ($isMedium
                    ? 'Hasil tes ini cukup konsisten dan cukup dapat dipercaya'
                    : 'Hasil tes ini kurang konsisten dan kurang dapat dipercaya'),
            'internal_status' => $isGood ? 'Terbuka' : ($isMedium ? 'Cukup terbuka' : 'Kurang terbuka'),
            'interpersonal_status' => $isGood ? 'Terbuka' : ($isMedium ? 'Cukup terbuka' : 'Kurang terbuka'),
            'work_capacity_status' => $isGood ? 'Terbuka' : ($isMedium ? 'Cukup terbuka' : 'Kurang terbuka'),
            'clinical_status' => $isGood ? 'Normal' : ($isMedium ? 'Cukup normal' : 'Kurang normal'),
            'conclusion_code' => $conclusionCode,
            'conclusion_text' => $conclusionText,
            'notes' => ! $isGood && ! $isMedium
                ? 'Perlu perhatian khusus pada aspek kejiwaan. Mungkin terdapat psikopatologi yang perlu diwaspadai.'
                : null,
        ];
    }

    /**
     * Set for specific participant
     */
    public function forParticipant(Participant $participant): static
    {
        return $this->state(fn (array $attributes) => [
            'participant_id' => $participant->id,
            'event_id' => $participant->event_id,
        ]);
    }

    /**
     * State: High performance (MS - Memenuhi Syarat)
     */
    public function highPerformance(): static
    {
        return $this->state(function (array $attributes) {
            $rawScore = fake()->randomFloat(2, 45, 50);
            $iqScore = fake()->numberBetween(110, 125);

            return [
                'raw_score' => $rawScore,
                'iq_score' => $iqScore,
                'validity_status' => 'Hasil tes ini konsisten dan dapat dipercaya',
                'internal_status' => 'Terbuka',
                'interpersonal_status' => 'Terbuka',
                'work_capacity_status' => 'Terbuka',
                'clinical_status' => 'Normal',
                'conclusion_code' => 'MS',
                'conclusion_text' => 'MEMENUHI SYARAT (MS)',
                'notes' => null,
            ];
        });
    }

    /**
     * State: Medium performance (MS - Memenuhi Syarat)
     */
    public function mediumPerformance(): static
    {
        return $this->state(function (array $attributes) {
            $rawScore = fake()->randomFloat(2, 38, 45);
            $iqScore = fake()->numberBetween(95, 110);

            return [
                'raw_score' => $rawScore,
                'iq_score' => $iqScore,
                'validity_status' => 'Hasil tes ini cukup konsisten dan cukup dapat dipercaya',
                'internal_status' => 'Cukup terbuka',
                'interpersonal_status' => 'Cukup terbuka',
                'work_capacity_status' => 'Cukup terbuka',
                'clinical_status' => 'Cukup normal',
                'conclusion_code' => 'MS',
                'conclusion_text' => 'MEMENUHI SYARAT (MS)',
                'notes' => null,
            ];
        });
    }

    /**
     * State: Low performance (TMS - Tidak Memenuhi Syarat)
     */
    public function lowPerformance(): static
    {
        return $this->state(function (array $attributes) {
            $rawScore = fake()->randomFloat(2, 30, 38);
            $iqScore = fake()->numberBetween(85, 95);

            return [
                'raw_score' => $rawScore,
                'iq_score' => $iqScore,
                'validity_status' => 'Hasil tes ini kurang konsisten dan kurang dapat dipercaya',
                'internal_status' => 'Kurang terbuka',
                'interpersonal_status' => 'Kurang terbuka',
                'work_capacity_status' => 'Kurang terbuka',
                'clinical_status' => 'Kurang normal',
                'conclusion_code' => 'TMS',
                'conclusion_text' => 'TIDAK MEMENUHI SYARAT (TMS)',
                'notes' => 'Perlu perhatian khusus pada aspek kejiwaan. Mungkin terdapat psikopatologi yang perlu diwaspadai.',
            ];
        });
    }
}
