<?php

declare(strict_types=1);

namespace App\Services\Assessment;

use App\Models\Aspect;
use App\Models\AspectAssessment;
use App\Models\CategoryAssessment;
use App\Models\SubAspectAssessment;
use App\Services\Cache\AspectCacheService;
use App\Services\DynamicStandardService;

class AspectService
{
    public function __construct(
        private DynamicStandardService $dynamicStandardService
    ) {}

    /**
     * Calculate aspect assessment (UNIFIED for all types)
     *
     * Logic (DATA-DRIVEN):
     * - Get aspect weight from DynamicStandardService (handles session/custom/default)
     * - If aspect has sub-aspect assessments: calculate rating from them
     * - If aspect has no sub-aspect assessments: use individual_rating parameter
     * - Calculate scores (standard, individual, gap)
     * - Determine conclusion
     * - Save to database
     *
     * @param  float|int|null  $individualRating  From API for aspects without sub-aspects
     * @param  \Illuminate\Support\Collection|null  $subAssessments  Pre-loaded sub-assessments (for performance)
     */
    public function calculateAspect(AspectAssessment $aspectAssessment, $individualRating = null, $subAssessments = null): void
    {
        // 1. ⚡ Get aspect from cache first, then database
        $aspect = AspectCacheService::getById($aspectAssessment->aspect_id);

        if (! $aspect) {
            $aspect = Aspect::with('subAspects')->findOrFail($aspectAssessment->aspect_id);
        }

        $templateId = $aspect->template_id;

        // 2. Get adjusted weight from DynamicStandardService
        $weight = $this->dynamicStandardService->getAspectWeight($templateId, $aspect->code);

        // 3. ⚡ DATA-DRIVEN: Determine rating source (use passed sub-assessments if available)
        if ($subAssessments === null) {
            $subAssessments = SubAspectAssessment::where(
                'aspect_assessment_id',
                $aspectAssessment->id
            )->get();
        }

        if ($subAssessments->isNotEmpty()) {
            // Has sub-aspect assessments: calculate ratings from them
            // ⚡ Note: subAspect relationship should be already loaded from storeSubAspectAssessment

            // Filter only ACTIVE sub-aspects based on session
            $activeSubAssessments = $subAssessments->filter(function ($subAssessment) use ($templateId) {
                // Access subAspect relationship (already loaded, no query)
                $subAspect = $subAssessment->subAspect;

                return $this->dynamicStandardService->isSubAspectActive($templateId, $subAspect->code);
            });

            if ($activeSubAssessments->isEmpty()) {
                return; // Skip if no active sub-aspects
            }

            // Calculate individual_rating = AVERAGE of ACTIVE sub-aspects (DECIMAL)
            $individualRating = $activeSubAssessments->avg('individual_rating');
        } elseif ($individualRating === null) {
            // No sub-aspects and no individual rating provided, skip
            return;
        }

        // 4. Calculate scores using adjusted weight
        // Score = rating × weight_percentage
        $standardScore = $aspectAssessment->standard_rating * $weight;
        $individualScore = $individualRating * $weight;

        // 5. Calculate gaps
        $gapRating = $individualRating - $aspectAssessment->standard_rating;
        $gapScore = $individualScore - $standardScore;

        // 6. Calculate percentage for spider chart (rating out of max scale 5)
        $percentageScore = (int) round(($individualRating / 5) * 100);

        // 7. Determine conclusion
        $conclusionCode = $this->determineConclusion($gapRating);
        $conclusionText = $this->getConclusionText($conclusionCode);

        // 8. Update aspect assessment
        $aspectAssessment->update([
            'individual_rating' => round($individualRating, 2),
            'standard_score' => round($standardScore, 2),
            'individual_score' => round($individualScore, 2),
            'gap_rating' => round($gapRating, 2),
            'gap_score' => round($gapScore, 2),
            'percentage_score' => $percentageScore,
            'conclusion_code' => $conclusionCode,
            'conclusion_text' => $conclusionText,
        ]);
    }

    /**
     * Create aspect assessment record (without calculation)
     *
     * Logic (DATA-DRIVEN):
     * - If aspect has sub-aspects: calculate standard_rating from sub-aspects average
     * - If aspect has no sub-aspects: use aspect's own standard_rating
     */
    public function createAspectAssessment(
        CategoryAssessment $categoryAssessment,
        string $aspectCode
    ): AspectAssessment {
        // 1. ⚡ Find aspect from cache first, then database
        $categoryType = AspectCacheService::getCategoryById($categoryAssessment->category_type_id);
        $templateId = $categoryType?->template_id;

        $aspect = null;
        if ($templateId) {
            $aspect = AspectCacheService::getByCode($templateId, $aspectCode);
        }

        // Fallback to database if not in cache
        if (! $aspect) {
            $aspect = Aspect::where('category_type_id', $categoryAssessment->category_type_id)
                ->where('code', $aspectCode)
                ->with('subAspects')
                ->firstOrFail();
        }

        // 2. ✅ DATA-DRIVEN: Determine standard_rating based on actual structure
        if ($aspect->subAspects->isNotEmpty()) {
            // Has sub-aspects: Calculate standard_rating from sub-aspects average
            $standardRating = (float) $aspect->subAspects->avg('standard_rating');
        } else {
            // No sub-aspects: Use aspect's own standard_rating
            $standardRating = (float) $aspect->standard_rating;
        }

        // 3. Create aspect assessment (values will be calculated later)
        return AspectAssessment::updateOrCreate(
            [
                'category_assessment_id' => $categoryAssessment->id,
                'aspect_id' => $aspect->id,
            ],
            [
                'participant_id' => $categoryAssessment->participant_id,
                'event_id' => $categoryAssessment->event_id,
                'batch_id' => $categoryAssessment->batch_id,
                'position_formation_id' => $categoryAssessment->position_formation_id,
                'standard_rating' => round($standardRating, 2),
                // Default values (will be updated by calculate methods)
                'standard_score' => 0,
                'individual_rating' => 0,
                'individual_score' => 0,
                'gap_rating' => 0,
                'gap_score' => 0,
                'percentage_score' => 0,
                'conclusion_code' => 'meets_standard',
                'conclusion_text' => 'Memenuhi Standard',
            ]
        );
    }

    /**
     * Determine conclusion based on gap rating
     */
    private function determineConclusion(float $gapRating): string
    {
        if ($gapRating < -0.5) {
            return 'below_standard';
        } elseif ($gapRating < 0.5) {
            return 'meets_standard';
        } else {
            return 'exceeds_standard';
        }
    }

    /**
     * Get conclusion text from code
     */
    private function getConclusionText(string $code): string
    {
        return match ($code) {
            'below_standard' => 'Kurang Memenuhi Standard',
            'meets_standard' => 'Memenuhi Standard',
            'exceeds_standard' => 'Melebihi Standard',
            default => 'Memenuhi Standard',
        };
    }
}
