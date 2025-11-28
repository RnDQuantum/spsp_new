<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\CategoryAssessment;
use App\Models\CategoryType;
use App\Models\Participant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * CategoryAssessment Factory
 *
 * Creates category-level assessment records (Potensi/Kompetensi totals)
 *
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CategoryAssessment>
 */
class CategoryAssessmentFactory extends Factory
{
    protected $model = CategoryAssessment::class;

    public function definition(): array
    {
        return [
            'total_standard_rating' => fake()->randomFloat(2, 10, 25),
            'total_standard_score' => fake()->randomFloat(2, 200, 500),
            'total_individual_rating' => fake()->randomFloat(2, 10, 25),
            'total_individual_score' => fake()->randomFloat(2, 200, 500),
            'gap_rating' => fake()->randomFloat(2, -5, 5),
            'gap_score' => fake()->randomFloat(2, -100, 100),
            'conclusion_code' => fake()->randomElement(['DS', 'MS', 'BS']),
            'conclusion_text' => fake()->randomElement([
                'Di Atas Standar',
                'Memenuhi Standar',
                'Di Bawah Standar',
            ]),
        ];
    }

    /**
     * Set specific participant
     */
    public function forParticipant(Participant $participant): static
    {
        return $this->state(fn (array $attributes) => [
            'participant_id' => $participant->id,
            'event_id' => $participant->event_id,
            'batch_id' => $participant->batch_id,
            'position_formation_id' => $participant->position_formation_id,
        ]);
    }

    /**
     * Set specific category type
     */
    public function forCategoryType(CategoryType $categoryType): static
    {
        return $this->state(fn (array $attributes) => [
            'category_type_id' => $categoryType->id,
        ]);
    }

    /**
     * State: Di Atas Standar (Above Standard)
     */
    public function aboveStandard(): static
    {
        return $this->state(fn (array $attributes) => [
            'gap_rating' => fake()->randomFloat(2, 1, 5),
            'gap_score' => fake()->randomFloat(2, 20, 100),
            'conclusion_code' => 'DS',
            'conclusion_text' => 'Di Atas Standar',
        ]);
    }

    /**
     * State: Memenuhi Standar (Meets Standard)
     */
    public function meetsStandard(): static
    {
        return $this->state(fn (array $attributes) => [
            'gap_rating' => fake()->randomFloat(2, -0.5, 0.5),
            'gap_score' => fake()->randomFloat(2, -10, 10),
            'conclusion_code' => 'MS',
            'conclusion_text' => 'Memenuhi Standar',
        ]);
    }

    /**
     * State: Di Bawah Standar (Below Standard)
     */
    public function belowStandard(): static
    {
        return $this->state(fn (array $attributes) => [
            'gap_rating' => fake()->randomFloat(2, -5, -1),
            'gap_score' => fake()->randomFloat(2, -100, -20),
            'conclusion_code' => 'BS',
            'conclusion_text' => 'Di Bawah Standar',
        ]);
    }
}
