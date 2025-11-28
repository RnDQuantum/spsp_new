<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Aspect;
use App\Models\AspectAssessment;
use App\Models\CategoryAssessment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * AspectAssessment Factory
 *
 * Creates aspect-level assessment records
 *
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AspectAssessment>
 */
class AspectAssessmentFactory extends Factory
{
    protected $model = AspectAssessment::class;

    public function definition(): array
    {
        return [
            'standard_rating' => fake()->randomFloat(2, 1, 5),
            'standard_score' => fake()->randomFloat(2, 10, 100),
            'individual_rating' => fake()->randomFloat(2, 1, 5),
            'individual_score' => fake()->randomFloat(2, 10, 100),
            'gap_rating' => fake()->randomFloat(2, -2, 2),
            'gap_score' => fake()->randomFloat(2, -50, 50),
            'percentage_score' => fake()->numberBetween(50, 150),
            'conclusion_code' => fake()->randomElement(['DS', 'MS', 'BS']),
            'conclusion_text' => fake()->randomElement([
                'Di Atas Standar',
                'Memenuhi Standar',
                'Di Bawah Standar',
            ]),
        ];
    }

    /**
     * Set specific category assessment (auto-fills participant, event, etc.)
     */
    public function forCategoryAssessment(CategoryAssessment $categoryAssessment): static
    {
        return $this->state(fn (array $attributes) => [
            'category_assessment_id' => $categoryAssessment->id,
            'participant_id' => $categoryAssessment->participant_id,
            'event_id' => $categoryAssessment->event_id,
            'batch_id' => $categoryAssessment->batch_id,
            'position_formation_id' => $categoryAssessment->position_formation_id,
        ]);
    }

    /**
     * Set specific aspect
     */
    public function forAspect(Aspect $aspect): static
    {
        return $this->state(fn (array $attributes) => [
            'aspect_id' => $aspect->id,
            'standard_rating' => $aspect->standard_rating,
        ]);
    }

    /**
     * State: Di Atas Standar (Above Standard)
     */
    public function aboveStandard(): static
    {
        return $this->state(fn (array $attributes) => [
            'individual_rating' => fake()->randomFloat(2, 4, 5),
            'gap_rating' => fake()->randomFloat(2, 0.6, 2),
            'gap_score' => fake()->randomFloat(2, 10, 50),
            'percentage_score' => fake()->numberBetween(110, 150),
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
            'individual_rating' => fake()->randomFloat(2, 2.5, 4),
            'gap_rating' => fake()->randomFloat(2, -0.5, 0.5),
            'gap_score' => fake()->randomFloat(2, -10, 10),
            'percentage_score' => fake()->numberBetween(90, 110),
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
            'individual_rating' => fake()->randomFloat(2, 1, 2.5),
            'gap_rating' => fake()->randomFloat(2, -2, -0.6),
            'gap_score' => fake()->randomFloat(2, -50, -10),
            'percentage_score' => fake()->numberBetween(50, 90),
            'conclusion_code' => 'BS',
            'conclusion_text' => 'Di Bawah Standar',
        ]);
    }
}
