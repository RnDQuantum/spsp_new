<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Aspect;
use App\Models\AssessmentTemplate;
use App\Models\CategoryType;
use App\Models\SubAspect;
use Illuminate\Support\Facades\Session;

class DynamicStandardService
{
    /**
     * Session key prefix
     */
    private const SESSION_PREFIX = 'standard_adjustment';

    /**
     * Get session key for template
     */
    private function getSessionKey(int $templateId): string
    {
        return self::SESSION_PREFIX.".{$templateId}";
    }

    /**
     * Get all adjustments for a template
     */
    public function getAdjustments(int $templateId): array
    {
        return Session::get($this->getSessionKey($templateId), []);
    }

    /**
     * Check if template has any adjustments
     */
    public function hasAdjustments(int $templateId): bool
    {
        return Session::has($this->getSessionKey($templateId));
    }

    /**
     * Get category weight (adjusted or original)
     */
    public function getCategoryWeight(int $templateId, string $categoryCode): int
    {
        $adjustments = $this->getAdjustments($templateId);

        if (isset($adjustments['category_weights'][$categoryCode])) {
            return (int) $adjustments['category_weights'][$categoryCode];
        }

        // Get original from database
        $category = CategoryType::where('template_id', $templateId)
            ->where('code', $categoryCode)
            ->first();

        return $category ? $category->weight_percentage : 0;
    }

    /**
     * Get aspect weight (adjusted or original)
     */
    public function getAspectWeight(int $templateId, string $aspectCode): int
    {
        $adjustments = $this->getAdjustments($templateId);

        if (isset($adjustments['aspect_weights'][$aspectCode])) {
            return (int) $adjustments['aspect_weights'][$aspectCode];
        }

        // Get original from database
        $aspect = Aspect::where('template_id', $templateId)
            ->where('code', $aspectCode)
            ->first();

        return $aspect ? $aspect->weight_percentage : 0;
    }

    /**
     * Get aspect standard rating (adjusted or original)
     */
    public function getAspectRating(int $templateId, string $aspectCode): float
    {
        $adjustments = $this->getAdjustments($templateId);

        if (isset($adjustments['aspect_ratings'][$aspectCode])) {
            return (float) $adjustments['aspect_ratings'][$aspectCode];
        }

        // Get original from database
        $aspect = Aspect::where('template_id', $templateId)
            ->where('code', $aspectCode)
            ->first();

        return $aspect ? (float) $aspect->standard_rating : 0.0;
    }

    /**
     * Get sub-aspect standard rating (adjusted or original)
     */
    public function getSubAspectRating(int $templateId, string $subAspectCode): int
    {
        $adjustments = $this->getAdjustments($templateId);

        if (isset($adjustments['sub_aspect_ratings'][$subAspectCode])) {
            return (int) $adjustments['sub_aspect_ratings'][$subAspectCode];
        }

        // Get original from database
        $subAspect = SubAspect::whereHas('aspect', function ($query) use ($templateId) {
            $query->where('template_id', $templateId);
        })->where('code', $subAspectCode)->first();

        return $subAspect ? $subAspect->standard_rating : 0;
    }

    /**
     * Save category weight adjustment
     */
    public function saveCategoryWeight(int $templateId, string $categoryCode, int $weight): void
    {
        $adjustments = $this->getAdjustments($templateId);
        $adjustments['category_weights'][$categoryCode] = $weight;
        $adjustments['adjusted_at'] = now()->toDateTimeString();

        Session::put($this->getSessionKey($templateId), $adjustments);
    }

    /**
     * Save aspect weight adjustment
     */
    public function saveAspectWeight(int $templateId, string $aspectCode, int $weight): void
    {
        $adjustments = $this->getAdjustments($templateId);
        $adjustments['aspect_weights'][$aspectCode] = $weight;
        $adjustments['adjusted_at'] = now()->toDateTimeString();

        Session::put($this->getSessionKey($templateId), $adjustments);
    }

    /**
     * Save aspect rating adjustment
     */
    public function saveAspectRating(int $templateId, string $aspectCode, float $rating): void
    {
        $adjustments = $this->getAdjustments($templateId);
        $adjustments['aspect_ratings'][$aspectCode] = $rating;
        $adjustments['adjusted_at'] = now()->toDateTimeString();

        Session::put($this->getSessionKey($templateId), $adjustments);
    }

    /**
     * Save sub-aspect rating adjustment
     */
    public function saveSubAspectRating(int $templateId, string $subAspectCode, int $rating): void
    {
        $adjustments = $this->getAdjustments($templateId);
        $adjustments['sub_aspect_ratings'][$subAspectCode] = $rating;
        $adjustments['adjusted_at'] = now()->toDateTimeString();

        Session::put($this->getSessionKey($templateId), $adjustments);
    }

    /**
     * Save bulk adjustments
     */
    public function saveBulkAdjustments(int $templateId, array $adjustments): void
    {
        $adjustments['adjusted_at'] = now()->toDateTimeString();
        Session::put($this->getSessionKey($templateId), $adjustments);
    }

    /**
     * Reset all adjustments for a template
     */
    public function resetAdjustments(int $templateId): void
    {
        Session::forget($this->getSessionKey($templateId));
    }

    /**
     * Get original (unadjusted) template data
     */
    public function getOriginalTemplateData(int $templateId): array
    {
        $template = AssessmentTemplate::with([
            'categoryTypes',
            'aspects.subAspects',
        ])->findOrFail($templateId);

        $data = [
            'template' => $template,
            'category_weights' => [],
            'potensi_aspects' => [],
            'kompetensi_aspects' => [],
        ];

        foreach ($template->categoryTypes as $category) {
            $data['category_weights'][$category->code] = $category->weight_percentage;

            $aspects = $category->aspects->map(function ($aspect) {
                return [
                    'id' => $aspect->id,
                    'code' => $aspect->code,
                    'name' => $aspect->name,
                    'weight_percentage' => $aspect->weight_percentage,
                    'standard_rating' => $aspect->standard_rating,
                    'sub_aspects' => $aspect->subAspects->map(function ($subAspect) {
                        return [
                            'id' => $subAspect->id,
                            'code' => $subAspect->code,
                            'name' => $subAspect->name,
                            'standard_rating' => $subAspect->standard_rating,
                        ];
                    })->toArray(),
                ];
            })->toArray();

            if ($category->code === 'potensi') {
                $data['potensi_aspects'] = $aspects;
            } else {
                $data['kompetensi_aspects'] = $aspects;
            }
        }

        return $data;
    }

    /**
     * Validate adjustments
     */
    public function validateAdjustments(array $adjustments): array
    {
        $errors = [];

        // Validate category weights sum to 100
        if (isset($adjustments['category_weights'])) {
            $total = array_sum($adjustments['category_weights']);
            if ($total !== 100) {
                $errors['category_weights'] = "Total bobot kategori harus 100% (saat ini: {$total}%)";
            }
        }

        // Validate rating ranges
        if (isset($adjustments['aspect_ratings'])) {
            foreach ($adjustments['aspect_ratings'] as $code => $rating) {
                if ($rating < 1 || $rating > 5) {
                    $errors["aspect_ratings.{$code}"] = 'Rating harus antara 1-5';
                }
            }
        }

        if (isset($adjustments['sub_aspect_ratings'])) {
            foreach ($adjustments['sub_aspect_ratings'] as $code => $rating) {
                if ($rating < 1 || $rating > 5) {
                    $errors["sub_aspect_ratings.{$code}"] = 'Rating harus antara 1-5';
                }
            }
        }

        return $errors;
    }

    // ========================================
    // PHASE 2A: FEATURE 4 - SELECTIVE ASPECTS/SUB-ASPECTS
    // ========================================

    /**
     * Check if aspect is active (selected for analysis)
     */
    public function isAspectActive(int $templateId, string $aspectCode): bool
    {
        $adjustments = $this->getAdjustments($templateId);

        // If not set in session, default is active (true)
        if (!isset($adjustments['active_aspects'][$aspectCode])) {
            return true;
        }

        return (bool) $adjustments['active_aspects'][$aspectCode];
    }

    /**
     * Check if sub-aspect is active (selected for analysis)
     */
    public function isSubAspectActive(int $templateId, string $subAspectCode): bool
    {
        $adjustments = $this->getAdjustments($templateId);

        // If not set in session, default is active (true)
        if (!isset($adjustments['active_sub_aspects'][$subAspectCode])) {
            return true;
        }

        return (bool) $adjustments['active_sub_aspects'][$subAspectCode];
    }

    /**
     * Set aspect active/inactive status
     */
    public function setAspectActive(int $templateId, string $aspectCode, bool $active): void
    {
        $adjustments = $this->getAdjustments($templateId);
        $adjustments['active_aspects'][$aspectCode] = $active;
        $adjustments['adjusted_at'] = now()->toDateTimeString();

        // If aspect is disabled, set weight to 0
        if (!$active) {
            $adjustments['aspect_weights'][$aspectCode] = 0;
        }

        Session::put($this->getSessionKey($templateId), $adjustments);
    }

    /**
     * Set sub-aspect active/inactive status
     */
    public function setSubAspectActive(int $templateId, string $subAspectCode, bool $active): void
    {
        $adjustments = $this->getAdjustments($templateId);
        $adjustments['active_sub_aspects'][$subAspectCode] = $active;
        $adjustments['adjusted_at'] = now()->toDateTimeString();

        Session::put($this->getSessionKey($templateId), $adjustments);
    }

    /**
     * Get all active aspects for a template
     *
     * @return array Array of aspect codes that are active
     */
    public function getActiveAspects(int $templateId): array
    {
        $adjustments = $this->getAdjustments($templateId);

        // If no active_aspects set, return all aspects as active
        if (!isset($adjustments['active_aspects'])) {
            $template = AssessmentTemplate::with('aspects')->findOrFail($templateId);

            return $template->aspects->pluck('code')->toArray();
        }

        // Return only aspects where value is true
        return array_keys(array_filter($adjustments['active_aspects'], fn ($active) => $active === true));
    }

    /**
     * Get all active sub-aspects for a template
     *
     * @return array Array of sub-aspect codes that are active
     */
    public function getActiveSubAspects(int $templateId): array
    {
        $adjustments = $this->getAdjustments($templateId);

        // If no active_sub_aspects set, return all sub-aspects as active
        if (!isset($adjustments['active_sub_aspects'])) {
            $template = AssessmentTemplate::with('aspects.subAspects')->findOrFail($templateId);
            $subAspects = [];

            foreach ($template->aspects as $aspect) {
                foreach ($aspect->subAspects as $subAspect) {
                    $subAspects[] = $subAspect->code;
                }
            }

            return $subAspects;
        }

        // Return only sub-aspects where value is true
        return array_keys(array_filter($adjustments['active_sub_aspects'], fn ($active) => $active === true));
    }

    /**
     * Save bulk selection (aspects, sub-aspects, and weights)
     *
     * @param  array  $data  Contains: active_aspects, active_sub_aspects, aspect_weights
     */
    public function saveBulkSelection(int $templateId, array $data): void
    {
        $adjustments = $this->getAdjustments($templateId);

        // Merge new data
        if (isset($data['active_aspects'])) {
            $adjustments['active_aspects'] = array_merge(
                $adjustments['active_aspects'] ?? [],
                $data['active_aspects']
            );
        }

        if (isset($data['active_sub_aspects'])) {
            $adjustments['active_sub_aspects'] = array_merge(
                $adjustments['active_sub_aspects'] ?? [],
                $data['active_sub_aspects']
            );
        }

        if (isset($data['aspect_weights'])) {
            $adjustments['aspect_weights'] = array_merge(
                $adjustments['aspect_weights'] ?? [],
                $data['aspect_weights']
            );
        }

        $adjustments['adjusted_at'] = now()->toDateTimeString();

        Session::put($this->getSessionKey($templateId), $adjustments);
    }

    /**
     * Validate selection (active aspects, sub-aspects, and weights)
     *
     * @return array ['valid' => bool, 'errors' => array]
     */
    public function validateSelection(int $templateId, array $data): array
    {
        $errors = [];

        // Get template data for validation
        $template = AssessmentTemplate::with([
            'categoryTypes',
            'aspects.subAspects',
        ])->findOrFail($templateId);

        // Group aspects by category
        $potensiAspects = [];
        $kompetensiAspects = [];

        foreach ($template->aspects as $aspect) {
            if ($aspect->categoryType->code === 'potensi') {
                $potensiAspects[$aspect->code] = $aspect;
            } else {
                $kompetensiAspects[$aspect->code] = $aspect;
            }
        }

        // Rule 1: Minimum 3 aspects per category must be active
        if (isset($data['active_aspects'])) {
            $potensiActiveCount = 0;
            $kompetensiActiveCount = 0;

            foreach ($data['active_aspects'] as $aspectCode => $isActive) {
                if ($isActive) {
                    if (isset($potensiAspects[$aspectCode])) {
                        $potensiActiveCount++;
                    } elseif (isset($kompetensiAspects[$aspectCode])) {
                        $kompetensiActiveCount++;
                    }
                }
            }

            if ($potensiActiveCount > 0 && $potensiActiveCount < 3) {
                $errors[] = "Minimal 3 aspek Potensi harus aktif (saat ini: {$potensiActiveCount})";
            }

            if ($kompetensiActiveCount > 0 && $kompetensiActiveCount < 3) {
                $errors[] = "Minimal 3 aspek Kompetensi harus aktif (saat ini: {$kompetensiActiveCount})";
            }
        }

        // Rule 2: Total weight per category must = 100%
        if (isset($data['aspect_weights'])) {
            $potensiWeightTotal = 0;
            $kompetensiWeightTotal = 0;

            foreach ($data['aspect_weights'] as $aspectCode => $weight) {
                if (isset($potensiAspects[$aspectCode])) {
                    $potensiWeightTotal += $weight;
                } elseif (isset($kompetensiAspects[$aspectCode])) {
                    $kompetensiWeightTotal += $weight;
                }
            }

            if ($potensiWeightTotal > 0 && $potensiWeightTotal !== 100) {
                $errors[] = "Total bobot Potensi harus 100% (saat ini: {$potensiWeightTotal}%)";
            }

            if ($kompetensiWeightTotal > 0 && $kompetensiWeightTotal !== 100) {
                $errors[] = "Total bobot Kompetensi harus 100% (saat ini: {$kompetensiWeightTotal}%)";
            }
        }

        // Rule 3: Each active aspect (Potensi only) must have ≥1 active sub-aspect
        if (isset($data['active_aspects']) && isset($data['active_sub_aspects'])) {
            foreach ($data['active_aspects'] as $aspectCode => $isActive) {
                if ($isActive && isset($potensiAspects[$aspectCode])) {
                    $aspect = $potensiAspects[$aspectCode];
                    $activeSubCount = 0;

                    foreach ($aspect->subAspects as $subAspect) {
                        if (isset($data['active_sub_aspects'][$subAspect->code]) &&
                            $data['active_sub_aspects'][$subAspect->code]) {
                            $activeSubCount++;
                        }
                    }

                    if ($aspect->subAspects->count() > 0 && $activeSubCount < 1) {
                        $errors[] = "Aspek {$aspect->name} harus memiliki minimal 1 sub-aspek aktif";
                    }
                }
            }
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
        ];
    }

    /**
     * Get count of active aspects per category
     *
     * @return array ['potensi' => int, 'kompetensi' => int]
     */
    public function getActiveAspectsCount(int $templateId): array
    {
        $template = AssessmentTemplate::with(['categoryTypes', 'aspects'])->findOrFail($templateId);
        $adjustments = $this->getAdjustments($templateId);

        $counts = ['potensi' => 0, 'kompetensi' => 0];

        foreach ($template->aspects as $aspect) {
            $isActive = $adjustments['active_aspects'][$aspect->code] ?? true;

            if ($isActive) {
                $categoryCode = $aspect->categoryType->code;
                $counts[$categoryCode]++;
            }
        }

        return $counts;
    }

    /**
     * Get total aspects count per category
     *
     * @return array ['potensi' => int, 'kompetensi' => int]
     */
    public function getTotalAspectsCount(int $templateId): array
    {
        $template = AssessmentTemplate::with(['categoryTypes', 'aspects'])->findOrFail($templateId);

        $counts = ['potensi' => 0, 'kompetensi' => 0];

        foreach ($template->aspects as $aspect) {
            $categoryCode = $aspect->categoryType->code;
            $counts[$categoryCode]++;
        }

        return $counts;
    }
}
