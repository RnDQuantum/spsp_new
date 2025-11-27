<?php

namespace App\Services;

use App\Models\AspectAssessment;
use App\Models\CategoryType;
use App\Models\Interpretation;
use App\Models\Participant;
use App\Models\SubAspectAssessment;
use Illuminate\Support\Facades\DB;

class InterpretationGeneratorService
{
    public function __construct(
        protected InterpretationTemplateService $templateService,
        protected DynamicStandardService $dynamicService
    ) {}

    /**
     * Generate interpretations for 1 participant (all categories)
     *
     * @return array Keyed by category code ['potensi' => string, 'kompetensi' => string, ...]
     */
    public function generateForParticipant(Participant $participant): array
    {
        $results = [];

        DB::transaction(function () use ($participant, &$results) {
            // Load participant with template
            $participant->loadMissing('positionFormation.template');
            $template = $participant->positionFormation->template;

            // ✅ UNIFIED: Generate for all categories dynamically
            foreach ($template->categoryTypes as $categoryType) {
                $results[$categoryType->code] = $this->generateCategoryInterpretation(
                    $participant,
                    $categoryType
                );
            }
        });

        return $results;
    }

    /**
     * Generate category interpretation (UNIFIED for all category types)
     *
     * Logic (DATA-DRIVEN):
     * - For each aspect in category:
     *   - If aspect has sub-aspect assessments: generate from sub-aspects
     *   - If aspect has no sub-aspect assessments: generate from aspect directly
     * - Combine into category interpretation
     * - Save to database
     */
    protected function generateCategoryInterpretation(
        Participant $participant,
        CategoryType $categoryType
    ): string {
        // Get all aspects for this category (sorted by order)
        $aspects = $categoryType->aspects()->orderBy('order')->get();

        $paragraphs = [];

        foreach ($aspects as $aspect) {
            $aspectParagraph = $this->buildAspectParagraph($participant, $aspect);
            if ($aspectParagraph) {
                $paragraphs[] = $aspectParagraph;
            }
        }

        // Combine all paragraphs with double line break
        $finalText = implode("\n\n", $paragraphs);

        // Save to interpretations table
        Interpretation::updateOrCreate(
            [
                'participant_id' => $participant->id,
                'category_type_id' => $categoryType->id,
            ],
            [
                'event_id' => $participant->event_id,
                'interpretation_text' => $finalText,
            ]
        );

        return $finalText;
    }

    /**
     * Build paragraph for 1 aspect (DATA-DRIVEN)
     *
     * Logic:
     * - If aspect has sub-aspect assessments: aggregate from sub-aspects
     * - If aspect has no sub-aspect assessments: use aspect assessment directly
     */
    protected function buildAspectParagraph(Participant $participant, $aspect): string
    {
        // Check if this aspect has sub-aspect assessments
        $subAssessments = SubAspectAssessment::whereHas('aspectAssessment', function ($q) use ($aspect) {
            $q->where('aspect_id', $aspect->id);
        })
            ->where('participant_id', $participant->id)
            ->with('subAspect')
            ->get()
            ->sortBy('subAspect.order');

        // DATA-DRIVEN: Check if aspect has sub-aspect assessments
        if ($subAssessments->isNotEmpty()) {
            // Has sub-aspects: build from sub-aspects
            $sentences = [];

            foreach ($subAssessments as $subAssessment) {
                // Get template by name (more flexible across different templates)
                $template = $this->templateService->getTemplateByName(
                    'sub_aspect',
                    $subAssessment->subAspect->name,
                    $subAssessment->individual_rating
                );

                // Fallback to default if template not found
                if (! $template) {
                    $template = $this->templateService->getDefaultTemplate(
                        $subAssessment->individual_rating
                    );
                }

                $sentences[] = $template;
            }

            // Combine sentences into flowing paragraph
            return implode(' ', $sentences);
        }

        // No sub-aspects: use aspect assessment directly
        $aspectAssessment = AspectAssessment::where('participant_id', $participant->id)
            ->where('aspect_id', $aspect->id)
            ->first();

        if (! $aspectAssessment) {
            return '';
        }

        // Cast individual_rating to integer for template lookup
        $ratingValue = (int) round($aspectAssessment->individual_rating);

        // Get template by name
        $template = $this->templateService->getTemplateByName(
            'aspect',
            $aspect->name,
            $ratingValue
        );

        // Fallback to default if template not found
        if (! $template) {
            $template = $this->templateService->getDefaultTemplate($ratingValue);
        }

        return $template;
    }

    /**
     * Get existing interpretation for a participant and category
     *
     * @param  string  $categoryCode  'potensi' or 'kompetensi'
     */
    public function getInterpretation(int $participantId, string $categoryCode): ?string
    {
        $interpretation = Interpretation::whereHas('categoryType', function ($q) use ($categoryCode) {
            $q->where('code', $categoryCode);
        })
            ->where('participant_id', $participantId)
            ->first();

        return $interpretation?->interpretation_text;
    }

    /**
     * Check if interpretations exist for a participant
     */
    public function hasInterpretations(int $participantId): bool
    {
        return Interpretation::where('participant_id', $participantId)->exists();
    }

    /**
     * Delete interpretations for a participant
     */
    public function deleteInterpretations(int $participantId): void
    {
        Interpretation::where('participant_id', $participantId)->delete();
    }

    /**
     * Regenerate interpretations for a participant
     * (Useful when templates are updated)
     */
    public function regenerateInterpretations(Participant $participant): array
    {
        // Delete existing
        $this->deleteInterpretations($participant->id);

        // Clear template cache
        $this->templateService->clearCache();

        // Generate new
        return $this->generateForParticipant($participant);
    }

    // ========================================
    // ON-THE-FLY GENERATION (NO DATABASE SAVE)
    // ========================================

    /**
     * Generate interpretations for DISPLAY only (no database save)
     * Respects selected custom standard and session adjustments
     *
     * @return array Keyed by category code ['potensi' => string, 'kompetensi' => string, ...]
     */
    public function generateForDisplay(Participant $participant): array
    {
        $results = [];

        // Load participant with template
        $participant->loadMissing('positionFormation.template');
        $template = $participant->positionFormation->template;

        // ✅ UNIFIED: Generate for all categories dynamically
        foreach ($template->categoryTypes as $categoryType) {
            $results[$categoryType->code] = $this->generateCategoryInterpretationForDisplay(
                $participant,
                $categoryType
            );
        }

        return $results;
    }

    /**
     * Generate category interpretation for display (UNIFIED)
     *
     * Respects active aspects from DynamicStandardService (session adjustments)
     * Does NOT save to database
     */
    protected function generateCategoryInterpretationForDisplay(
        Participant $participant,
        CategoryType $categoryType
    ): string {
        $template = $participant->positionFormation->template;

        // Get all aspects (sorted by order)
        $aspects = $categoryType->aspects()->orderBy('order')->get();

        // ✅ FILTER by active status (from DynamicStandardService)
        $aspects = $aspects->filter(function ($aspect) use ($template) {
            return $this->dynamicService->isAspectActive($template->id, $aspect->code);
        });

        $paragraphs = [];

        foreach ($aspects as $aspect) {
            $aspectParagraph = $this->buildAspectParagraphForDisplay($participant, $aspect);
            if ($aspectParagraph) {
                $paragraphs[] = $aspectParagraph;
            }
        }

        // Combine all paragraphs with double line break
        return implode("\n\n", $paragraphs);
    }

    /**
     * Build paragraph for 1 aspect - Display version (DATA-DRIVEN)
     *
     * Respects active sub-aspects from DynamicStandardService
     */
    protected function buildAspectParagraphForDisplay(Participant $participant, $aspect): string
    {
        $template = $participant->positionFormation->template;

        // Check if this aspect has sub-aspect assessments
        $subAssessments = SubAspectAssessment::whereHas('aspectAssessment', function ($q) use ($aspect) {
            $q->where('aspect_id', $aspect->id);
        })
            ->where('participant_id', $participant->id)
            ->with('subAspect')
            ->get()
            ->sortBy('subAspect.order');

        // DATA-DRIVEN: Check if aspect has sub-aspect assessments
        if ($subAssessments->isNotEmpty()) {
            // Has sub-aspects: filter by active status and build from sub-aspects
            $subAssessments = $subAssessments->filter(function ($subAssessment) use ($template) {
                return $this->dynamicService->isSubAspectActive($template->id, $subAssessment->subAspect->code);
            });

            if ($subAssessments->isEmpty()) {
                return '';
            }

            $sentences = [];

            foreach ($subAssessments as $subAssessment) {
                // Get template by name
                $interpretationTemplate = $this->templateService->getTemplateByName(
                    'sub_aspect',
                    $subAssessment->subAspect->name,
                    $subAssessment->individual_rating
                );

                // Fallback to default if template not found
                if (! $interpretationTemplate) {
                    $interpretationTemplate = $this->templateService->getDefaultTemplate(
                        $subAssessment->individual_rating
                    );
                }

                $sentences[] = $interpretationTemplate;
            }

            // Combine sentences into flowing paragraph
            return implode(' ', $sentences);
        }

        // No sub-aspects: use aspect assessment directly
        $aspectAssessment = AspectAssessment::where('participant_id', $participant->id)
            ->where('aspect_id', $aspect->id)
            ->first();

        if (! $aspectAssessment) {
            return '';
        }

        // Cast individual_rating to integer for template lookup
        $ratingValue = (int) round($aspectAssessment->individual_rating);

        // Get template by name
        $interpretationTemplate = $this->templateService->getTemplateByName(
            'aspect',
            $aspect->name,
            $ratingValue
        );

        // Fallback to default if template not found
        if (! $interpretationTemplate) {
            $interpretationTemplate = $this->templateService->getDefaultTemplate($ratingValue);
        }

        return $interpretationTemplate;
    }
}
