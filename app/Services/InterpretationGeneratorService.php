<?php

namespace App\Services;

use App\Models\Participant;
use App\Models\CategoryType;
use App\Models\SubAspectAssessment;
use App\Models\AspectAssessment;
use App\Models\Interpretation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class InterpretationGeneratorService
{
    public function __construct(
        protected InterpretationTemplateService $templateService
    ) {}

    /**
     * Generate interpretations for 1 participant (Potensi + Kompetensi)
     *
     * @param Participant $participant
     * @return array ['potensi' => string, 'kompetensi' => string]
     */
    public function generateForParticipant(Participant $participant): array
    {
        $results = [];

        DB::transaction(function () use ($participant, &$results) {
            // 1. Generate Potensi Interpretation
            $results['potensi'] = $this->generatePotensiInterpretation($participant);

            // 2. Generate Kompetensi Interpretation
            $results['kompetensi'] = $this->generateKompetensiInterpretation($participant);
        });

        return $results;
    }

    /**
     * Generate POTENSI interpretation (berbasis sub-aspects)
     *
     * @param Participant $participant
     * @return string
     */
    protected function generatePotensiInterpretation(Participant $participant): string
    {
        // Load participant with necessary relations
        $participant->loadMissing('positionFormation.template');

        // Get template & category
        $template = $participant->positionFormation->template;
        $potensiCategory = CategoryType::where('template_id', $template->id)
            ->where('code', 'potensi')
            ->first();

        if (!$potensiCategory) {
            Log::warning("Potensi category not found for template {$template->id}");
            return '';
        }

        // Get all aspects for Potensi (sorted by order)
        $aspects = $potensiCategory->aspects()->orderBy('order')->get();

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
                'category_type_id' => $potensiCategory->id,
            ],
            [
                'event_id' => $participant->event_id,
                'interpretation_text' => $finalText,
            ]
        );

        return $finalText;
    }

    /**
     * Build paragraph untuk 1 aspect (aggregate dari sub-aspects)
     *
     * @param Participant $participant
     * @param $aspect
     * @return string
     */
    protected function buildAspectParagraph(Participant $participant, $aspect): string
    {
        // Get all sub-aspect assessments for this aspect
        $subAssessments = SubAspectAssessment::whereHas('aspectAssessment', function ($q) use ($aspect) {
            $q->where('aspect_id', $aspect->id);
        })
            ->where('participant_id', $participant->id)
            ->with('subAspect')
            ->get()
            ->sortBy('subAspect.order');

        if ($subAssessments->isEmpty()) {
            return '';
        }

        $sentences = [];

        foreach ($subAssessments as $subAssessment) {
            // Get template by name (more flexible across different templates)
            $template = $this->templateService->getTemplateByName(
                'sub_aspect',
                $subAssessment->subAspect->name,
                $subAssessment->individual_rating
            );

            // Fallback ke default jika template tidak ada
            if (!$template) {
                $template = $this->templateService->getDefaultTemplate(
                    $subAssessment->individual_rating
                );
            }

            $sentences[] = $template;
        }

        // Combine sentences into flowing paragraph
        return implode(' ', $sentences);
    }

    /**
     * Generate KOMPETENSI interpretation (berbasis aspects, no sub-aspects)
     *
     * @param Participant $participant
     * @return string
     */
    protected function generateKompetensiInterpretation(Participant $participant): string
    {
        // Load participant with necessary relations
        $participant->loadMissing('positionFormation.template');

        // Get template & category
        $template = $participant->positionFormation->template;
        $kompetensiCategory = CategoryType::where('template_id', $template->id)
            ->where('code', 'kompetensi')
            ->first();

        if (!$kompetensiCategory) {
            Log::warning("Kompetensi category not found for template {$template->id}");
            return '';
        }

        // Get all aspect assessments for Kompetensi
        $aspectAssessments = AspectAssessment::whereHas('aspect', function ($q) use ($kompetensiCategory) {
            $q->where('category_type_id', $kompetensiCategory->id);
        })
            ->where('participant_id', $participant->id)
            ->with('aspect')
            ->get()
            ->sortBy('aspect.order');

        $paragraphs = [];

        foreach ($aspectAssessments as $assessment) {
            // Cast individual_rating to integer for template lookup
            $ratingValue = (int) round($assessment->individual_rating);

            // Get template by name (more flexible across different templates)
            $template = $this->templateService->getTemplateByName(
                'aspect',
                $assessment->aspect->name,
                $ratingValue
            );

            // Fallback
            if (!$template) {
                $template = $this->templateService->getDefaultTemplate($ratingValue);
            }

            $paragraphs[] = $template;
        }

        // Combine all paragraphs (each aspect = 1 paragraph)
        $finalText = implode("\n\n", $paragraphs);

        // Save to interpretations table
        Interpretation::updateOrCreate(
            [
                'participant_id' => $participant->id,
                'category_type_id' => $kompetensiCategory->id,
            ],
            [
                'event_id' => $participant->event_id,
                'interpretation_text' => $finalText,
            ]
        );

        return $finalText;
    }

    /**
     * Get existing interpretation for a participant and category
     *
     * @param int $participantId
     * @param string $categoryCode 'potensi' or 'kompetensi'
     * @return string|null
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
     *
     * @param int $participantId
     * @return bool
     */
    public function hasInterpretations(int $participantId): bool
    {
        return Interpretation::where('participant_id', $participantId)->exists();
    }

    /**
     * Delete interpretations for a participant
     *
     * @param int $participantId
     * @return void
     */
    public function deleteInterpretations(int $participantId): void
    {
        Interpretation::where('participant_id', $participantId)->delete();
    }

    /**
     * Regenerate interpretations for a participant
     * (Useful when templates are updated)
     *
     * @param Participant $participant
     * @return array
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
}
