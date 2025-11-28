<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\AspectAssessment;
use App\Models\SubAspect;
use App\Models\SubAspectAssessment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * SubAspectAssessment Factory
 *
 * Creates sub-aspect level assessment records (for Potensi aspects)
 *
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SubAspectAssessment>
 */
class SubAspectAssessmentFactory extends Factory
{
    protected $model = SubAspectAssessment::class;

    public function definition(): array
    {
        $rating = fake()->numberBetween(1, 5);

        return [
            'standard_rating' => fake()->numberBetween(1, 5),
            'individual_rating' => $rating,
            'rating_label' => $this->getRatingLabel($rating),
        ];
    }

    /**
     * Set specific aspect assessment (auto-fills participant, event)
     */
    public function forAspectAssessment(AspectAssessment $aspectAssessment): static
    {
        return $this->state(fn (array $attributes) => [
            'aspect_assessment_id' => $aspectAssessment->id,
            'participant_id' => $aspectAssessment->participant_id,
            'event_id' => $aspectAssessment->event_id,
        ]);
    }

    /**
     * Set specific sub-aspect
     */
    public function forSubAspect(SubAspect $subAspect): static
    {
        return $this->state(fn (array $attributes) => [
            'sub_aspect_id' => $subAspect->id,
            'standard_rating' => $subAspect->standard_rating,
        ]);
    }

    /**
     * State: Rating 1 (Sangat Kurang)
     */
    public function rating1(): static
    {
        return $this->state(fn (array $attributes) => [
            'individual_rating' => 1,
            'rating_label' => 'Sangat Kurang',
        ]);
    }

    /**
     * State: Rating 2 (Kurang)
     */
    public function rating2(): static
    {
        return $this->state(fn (array $attributes) => [
            'individual_rating' => 2,
            'rating_label' => 'Kurang',
        ]);
    }

    /**
     * State: Rating 3 (Cukup)
     */
    public function rating3(): static
    {
        return $this->state(fn (array $attributes) => [
            'individual_rating' => 3,
            'rating_label' => 'Cukup',
        ]);
    }

    /**
     * State: Rating 4 (Baik)
     */
    public function rating4(): static
    {
        return $this->state(fn (array $attributes) => [
            'individual_rating' => 4,
            'rating_label' => 'Baik',
        ]);
    }

    /**
     * State: Rating 5 (Sangat Baik)
     */
    public function rating5(): static
    {
        return $this->state(fn (array $attributes) => [
            'individual_rating' => 5,
            'rating_label' => 'Sangat Baik',
        ]);
    }

    /**
     * Get rating label from numeric rating
     */
    private function getRatingLabel(int $rating): string
    {
        return match ($rating) {
            1 => 'Sangat Kurang',
            2 => 'Kurang',
            3 => 'Cukup',
            4 => 'Baik',
            5 => 'Sangat Baik',
            default => 'Cukup',
        };
    }
}
