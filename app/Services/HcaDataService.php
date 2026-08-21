<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\CategoryType;
use App\Models\Participant;
use Illuminate\Database\Eloquent\Collection;

/**
 * HcaDataService - Request-level memoized data access for HCA Report components
 *
 * Optimizes database queries by utilizing Laravel's once() helper to prevent
 * duplicate Participant with() and CategoryType queries during multi-component
 * rendering and PDF generation.
 */
class HcaDataService
{
    /**
     * Get participant with complete HCA relations (memoized per request)
     */
    public function getParticipant(?int $participantId): ?Participant
    {
        if (! $participantId) {
            return once(function () {
                return Participant::with([
                    'assessmentEvent.institution',
                    'positionFormation.template',
                    'batch',
                    'finalAssessment',
                    'mmpi',
                    'institution',
                    'personalProfile',
                    'careerHistories',
                    'performanceRecords',
                    'testResults',
                ])->first();
            });
        }

        return once(function () use ($participantId) {
            return Participant::with([
                'assessmentEvent.institution',
                'positionFormation.template',
                'batch',
                'finalAssessment',
                'mmpi',
                'institution',
                'personalProfile',
                'careerHistories',
                'performanceRecords',
                'testResults',
            ])->find($participantId);
        });
    }

    /**
     * Get all category types for a template (memoized per request)
     *
     * @return Collection<int, CategoryType>
     */
    public function getTemplateCategories(int $templateId): Collection
    {
        return once(function () use ($templateId) {
            return CategoryType::where('template_id', $templateId)->get();
        });
    }

    /**
     * Get a specific category type by code for a template
     */
    public function getCategoryByCode(int $templateId, string $code): ?CategoryType
    {
        $categories = $this->getTemplateCategories($templateId);

        return $categories->first(fn (CategoryType $cat) => $cat->code === $code);
    }
}
