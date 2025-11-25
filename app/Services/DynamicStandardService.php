<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Aspect;
use App\Models\AssessmentTemplate;
use App\Models\CategoryType;
use App\Models\CustomStandard;
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

    // ========================================
    // CORE REFACTORED METHODS
    // ========================================

    /**
     * Get original value - prioritizes Custom Standard if selected, otherwise Quantum Default
     * This ensures session adjustments are compared against the correct baseline
     *
     * @param  string  $type  'category_weight', 'aspect_weight', 'aspect_rating', 'sub_aspect_rating', 'aspect_active', 'sub_aspect_active'
     * @return mixed
     */
    private function getOriginalValue(string $type, int $templateId, string $code)
    {
        // Check if custom standard is selected
        $customStandardId = Session::get("selected_standard.{$templateId}");

        if ($customStandardId) {
            $customStandard = CustomStandard::find($customStandardId);

            if ($customStandard) {
                // Return value from Custom Standard if it exists
                $value = match ($type) {
                    'category_weight' => $customStandard->category_weights[$code] ?? null,
                    'aspect_weight' => $customStandard->aspect_configs[$code]['weight'] ?? null,
                    'aspect_rating' => isset($customStandard->aspect_configs[$code]['rating'])
                        ? (float) $customStandard->aspect_configs[$code]['rating']
                        : null,
                    'sub_aspect_rating' => isset($customStandard->sub_aspect_configs[$code]['rating'])
                        ? (int) $customStandard->sub_aspect_configs[$code]['rating']
                        : null,
                    'aspect_active' => $customStandard->aspect_configs[$code]['active'] ?? null,
                    'sub_aspect_active' => $customStandard->sub_aspect_configs[$code]['active'] ?? null,
                    default => null,
                };

                // If custom standard has this value, use it as baseline
                if ($value !== null) {
                    return $value;
                }
            }
        }

        // Fallback to Quantum default from database
        return match ($type) {
            'category_weight' => $this->getOriginalCategoryWeight($templateId, $code),
            'aspect_weight' => $this->getOriginalAspectWeight($templateId, $code),
            'aspect_rating' => $this->getOriginalAspectRating($templateId, $code),
            'sub_aspect_rating' => $this->getOriginalSubAspectRating($templateId, $code),
            'aspect_active' => true, // Default active
            'sub_aspect_active' => true, // Default active
            default => null,
        };
    }

    /**
     * Get original category weight from database
     */
    private function getOriginalCategoryWeight(int $templateId, string $categoryCode): int
    {
        $category = CategoryType::where('template_id', $templateId)
            ->where('code', $categoryCode)
            ->first();

        return $category ? $category->weight_percentage : 0;
    }

    /**
     * Get original aspect weight from database
     */
    private function getOriginalAspectWeight(int $templateId, string $aspectCode): int
    {
        $aspect = Aspect::where('template_id', $templateId)
            ->where('code', $aspectCode)
            ->first();

        return $aspect ? $aspect->weight_percentage : 0;
    }

    /**
     * Get original aspect rating from database
     */
    private function getOriginalAspectRating(int $templateId, string $aspectCode): float
    {
        $aspect = Aspect::where('template_id', $templateId)
            ->where('code', $aspectCode)
            ->first();

        return $aspect ? (float) $aspect->standard_rating : 0.0;
    }

    /**
     * Get original sub-aspect rating from database
     */
    private function getOriginalSubAspectRating(int $templateId, string $subAspectCode): int
    {
        $subAspect = SubAspect::whereHas('aspect', function ($query) use ($templateId) {
            $query->where('template_id', $templateId);
        })->where('code', $subAspectCode)->first();

        return $subAspect ? $subAspect->standard_rating : 0;
    }

    /**
     * Save adjustment only if different from original
     *
     * @param  string  $adjustmentKey  'category_weights', 'aspect_weights', 'aspect_ratings', etc.
     * @param  string  $type  Type for getting original value
     * @param  mixed  $value
     * @return bool True if saved, false if skipped (same as original)
     */
    private function saveAdjustmentIfDifferent(
        string $adjustmentKey,
        string $type,
        int $templateId,
        string $code,
        $value
    ): bool {
        $originalValue = $this->getOriginalValue($type, $templateId, $code);

        // Get current adjustments
        $adjustments = $this->getAdjustments($templateId);

        // If value equals original, remove from session (if exists)
        if ($value === $originalValue) {
            if (isset($adjustments[$adjustmentKey][$code])) {
                unset($adjustments[$adjustmentKey][$code]);

                // Clean up empty arrays
                if (empty($adjustments[$adjustmentKey])) {
                    unset($adjustments[$adjustmentKey]);
                }

                $this->saveOrForgetAdjustments($templateId, $adjustments);
            }

            return false; // Not saved (same as original)
        }

        // Value is different, save it
        $adjustments[$adjustmentKey][$code] = $value;
        $adjustments['adjusted_at'] = now()->toDateTimeString();

        Session::put($this->getSessionKey($templateId), $adjustments);

        return true; // Saved
    }

    /**
     * Save adjustments or forget session if empty
     */
    private function saveOrForgetAdjustments(int $templateId, array $adjustments): void
    {
        // Check if any real adjustments exist
        $hasAdjustments = ! empty($adjustments['category_weights'])
            || ! empty($adjustments['aspect_weights'])
            || ! empty($adjustments['aspect_ratings'])
            || ! empty($adjustments['sub_aspect_ratings'])
            || ! empty($adjustments['active_aspects'])
            || ! empty($adjustments['active_sub_aspects']);

        if (! $hasAdjustments) {
            Session::forget($this->getSessionKey($templateId));
        } else {
            $adjustments['adjusted_at'] = now()->toDateTimeString();
            Session::put($this->getSessionKey($templateId), $adjustments);
        }
    }

    // ========================================
    // PUBLIC SAVE METHODS (REFACTORED)
    // ========================================

    /**
     * Save category weight adjustment
     */
    public function saveCategoryWeight(int $templateId, string $categoryCode, int $weight): void
    {
        $this->saveAdjustmentIfDifferent(
            'category_weights',
            'category_weight',
            $templateId,
            $categoryCode,
            $weight
        );
    }

    /**
     * Save both category weights (Potensi + Kompetensi) with validation
     */
    public function saveBothCategoryWeights(
        int $templateId,
        string $potensiCode,
        int $potensiWeight,
        string $kompetensiCode,
        int $kompetensiWeight
    ): void {
        // Validate total is 100
        $total = $potensiWeight + $kompetensiWeight;
        if ($total !== 100) {
            throw new \InvalidArgumentException("Total bobot kategori harus 100% (saat ini: {$total}%)");
        }

        // Check if both match original values
        $originalPotensi = $this->getOriginalValue('category_weight', $templateId, $potensiCode);
        $originalKompetensi = $this->getOriginalValue('category_weight', $templateId, $kompetensiCode);

        $adjustments = $this->getAdjustments($templateId);

        // If both equal original, remove from session
        if ($potensiWeight === $originalPotensi && $kompetensiWeight === $originalKompetensi) {
            if (isset($adjustments['category_weights'][$potensiCode])) {
                unset($adjustments['category_weights'][$potensiCode]);
            }
            if (isset($adjustments['category_weights'][$kompetensiCode])) {
                unset($adjustments['category_weights'][$kompetensiCode]);
            }

            if (empty($adjustments['category_weights'])) {
                unset($adjustments['category_weights']);
            }

            $this->saveOrForgetAdjustments($templateId, $adjustments);

            return;
        }

        // Save both
        $adjustments['category_weights'][$potensiCode] = $potensiWeight;
        $adjustments['category_weights'][$kompetensiCode] = $kompetensiWeight;
        $adjustments['adjusted_at'] = now()->toDateTimeString();

        Session::put($this->getSessionKey($templateId), $adjustments);
    }

    /**
     * Save aspect weight adjustment
     */
    public function saveAspectWeight(int $templateId, string $aspectCode, int $weight): void
    {
        $this->saveAdjustmentIfDifferent(
            'aspect_weights',
            'aspect_weight',
            $templateId,
            $aspectCode,
            $weight
        );
    }

    /**
     * Save aspect rating adjustment
     */
    public function saveAspectRating(int $templateId, string $aspectCode, float $rating): void
    {
        $this->saveAdjustmentIfDifferent(
            'aspect_ratings',
            'aspect_rating',
            $templateId,
            $aspectCode,
            $rating
        );
    }

    /**
     * Save sub-aspect rating adjustment
     */
    public function saveSubAspectRating(int $templateId, string $subAspectCode, int $rating): void
    {
        $this->saveAdjustmentIfDifferent(
            'sub_aspect_ratings',
            'sub_aspect_rating',
            $templateId,
            $subAspectCode,
            $rating
        );
    }

    /**
     * Set aspect active/inactive status
     */
    public function setAspectActive(int $templateId, string $aspectCode, bool $active): void
    {
        // Check if weight should be adjusted
        $adjustments = $this->getAdjustments($templateId);

        // If setting to active (true), remove from session (default is active)
        if ($active === true) {
            if (isset($adjustments['active_aspects'][$aspectCode])) {
                unset($adjustments['active_aspects'][$aspectCode]);
            }

            // Also remove the forced 0 weight if exists
            if (
                isset($adjustments['aspect_weights'][$aspectCode]) &&
                $adjustments['aspect_weights'][$aspectCode] === 0
            ) {

                $originalWeight = $this->getOriginalValue('aspect_weight', $templateId, $aspectCode);
                if ($originalWeight !== 0) {
                    unset($adjustments['aspect_weights'][$aspectCode]);
                }
            }

            if (empty($adjustments['active_aspects'])) {
                unset($adjustments['active_aspects']);
            }
            if (empty($adjustments['aspect_weights'])) {
                unset($adjustments['aspect_weights']);
            }

            $this->saveOrForgetAdjustments($templateId, $adjustments);

            return;
        }

        // Setting to inactive (false)
        $adjustments['active_aspects'][$aspectCode] = false;
        $adjustments['aspect_weights'][$aspectCode] = 0;
        $adjustments['adjusted_at'] = now()->toDateTimeString();

        Session::put($this->getSessionKey($templateId), $adjustments);
    }

    /**
     * Set sub-aspect active/inactive status
     */
    public function setSubAspectActive(int $templateId, string $subAspectCode, bool $active): void
    {
        // If setting to active (true), remove from session (default is active)
        if ($active === true) {
            $adjustments = $this->getAdjustments($templateId);

            if (isset($adjustments['active_sub_aspects'][$subAspectCode])) {
                unset($adjustments['active_sub_aspects'][$subAspectCode]);
            }

            if (empty($adjustments['active_sub_aspects'])) {
                unset($adjustments['active_sub_aspects']);
            }

            $this->saveOrForgetAdjustments($templateId, $adjustments);

            return;
        }

        // Setting to inactive (false)
        $adjustments = $this->getAdjustments($templateId);
        $adjustments['active_sub_aspects'][$subAspectCode] = false;
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
     * Save bulk selection (aspects, sub-aspects, and weights)
     * This version filters out default values before saving
     */
    public function saveBulkSelection(int $templateId, array $data): void
    {
        $adjustments = $this->getAdjustments($templateId);

        // Process active_aspects
        if (isset($data['active_aspects'])) {
            foreach ($data['active_aspects'] as $code => $isActive) {
                if ($isActive === true) {
                    // Active is default, remove from adjustments
                    if (isset($adjustments['active_aspects'][$code])) {
                        unset($adjustments['active_aspects'][$code]);
                    }
                } else {
                    // Inactive, need to save
                    $adjustments['active_aspects'][$code] = false;
                }
            }

            if (empty($adjustments['active_aspects'])) {
                unset($adjustments['active_aspects']);
            }
        }

        // Process active_sub_aspects
        if (isset($data['active_sub_aspects'])) {
            foreach ($data['active_sub_aspects'] as $code => $isActive) {
                if ($isActive === true) {
                    // Active is default, remove from adjustments
                    if (isset($adjustments['active_sub_aspects'][$code])) {
                        unset($adjustments['active_sub_aspects'][$code]);
                    }
                } else {
                    // Inactive, need to save
                    $adjustments['active_sub_aspects'][$code] = false;
                }
            }

            if (empty($adjustments['active_sub_aspects'])) {
                unset($adjustments['active_sub_aspects']);
            }
        }

        // Process aspect_weights (check against original)
        if (isset($data['aspect_weights'])) {
            foreach ($data['aspect_weights'] as $code => $weight) {
                $originalWeight = $this->getOriginalValue('aspect_weight', $templateId, $code);

                if ($weight === $originalWeight) {
                    // Same as original, remove from adjustments
                    if (isset($adjustments['aspect_weights'][$code])) {
                        unset($adjustments['aspect_weights'][$code]);
                    }
                } else {
                    // Different, need to save
                    $adjustments['aspect_weights'][$code] = $weight;
                }
            }

            if (empty($adjustments['aspect_weights'])) {
                unset($adjustments['aspect_weights']);
            }
        }

        $this->saveOrForgetAdjustments($templateId, $adjustments);
    }

    // ========================================
    // GETTER METHODS (NO CHANGES)
    // ========================================

    /**
     * Check if specific category has any adjustments
     */
    public function hasCategoryAdjustments(int $templateId, string $categoryCode): bool
    {
        $adjustments = $this->getAdjustments($templateId);

        if (empty($adjustments)) {
            return false;
        }

        // Load template to get aspect codes for this category
        $template = AssessmentTemplate::with([
            'categoryTypes' => fn ($q) => $q->where('code', $categoryCode)->with('aspects.subAspects'),
        ])->find($templateId);

        if (! $template) {
            return false;
        }

        $category = $template->categoryTypes->firstWhere('code', $categoryCode);
        if (! $category) {
            return false;
        }

        // Check category weight adjustment
        if (isset($adjustments['category_weights'][$categoryCode])) {
            return true;
        }

        // Check aspect-level adjustments for this category
        foreach ($category->aspects as $aspect) {
            // Check aspect weight
            if (isset($adjustments['aspect_weights'][$aspect->code])) {
                return true;
            }

            // Check aspect rating
            if (isset($adjustments['aspect_ratings'][$aspect->code])) {
                return true;
            }

            // Check aspect active status
            if (isset($adjustments['active_aspects'][$aspect->code])) {
                return true;
            }

            // Check sub-aspect adjustments (for Potensi)
            if ($categoryCode === 'potensi' && $aspect->subAspects) {
                foreach ($aspect->subAspects as $subAspect) {
                    // Check sub-aspect rating
                    if (isset($adjustments['sub_aspect_ratings'][$subAspect->code])) {
                        return true;
                    }

                    // Check sub-aspect active status
                    if (isset($adjustments['active_sub_aspects'][$subAspect->code])) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    /**
     * Get category weight (adjusted or original)
     * Priority: 1. Session adjustment, 2. Custom standard, 3. Quantum default
     */
    public function getCategoryWeight(int $templateId, string $categoryCode): int
    {
        // Priority 1: Session adjustment
        $adjustments = $this->getAdjustments($templateId);

        if (isset($adjustments['category_weights'][$categoryCode])) {
            return (int) $adjustments['category_weights'][$categoryCode];
        }

        // Priority 2: Custom standard
        $customStandardId = Session::get("selected_standard.{$templateId}");
        if ($customStandardId) {
            $customStandard = CustomStandard::find($customStandardId);
            if ($customStandard && isset($customStandard->category_weights[$categoryCode])) {
                return (int) $customStandard->category_weights[$categoryCode];
            }
        }

        // Priority 3: Quantum default
        return $this->getOriginalCategoryWeight($templateId, $categoryCode);
    }

    /**
     * Get aspect weight (adjusted or original)
     * Priority: 1. Session adjustment, 2. Custom standard, 3. Quantum default
     */
    public function getAspectWeight(int $templateId, string $aspectCode): int
    {
        // Priority 1: Session adjustment
        $adjustments = $this->getAdjustments($templateId);

        if (isset($adjustments['aspect_weights'][$aspectCode])) {
            return (int) $adjustments['aspect_weights'][$aspectCode];
        }

        // Priority 2: Custom standard
        $customStandardId = Session::get("selected_standard.{$templateId}");
        if ($customStandardId) {
            $customStandard = CustomStandard::find($customStandardId);
            if ($customStandard && isset($customStandard->aspect_configs[$aspectCode]['weight'])) {
                return (int) $customStandard->aspect_configs[$aspectCode]['weight'];
            }
        }

        // Priority 3: Quantum default
        return $this->getOriginalAspectWeight($templateId, $aspectCode);
    }

    /**
     * Get aspect standard rating (adjusted or original)
     * Priority: 1. Session adjustment, 2. Custom standard, 3. Quantum default
     */
    public function getAspectRating(int $templateId, string $aspectCode): float
    {
        // Priority 1: Session adjustment
        $adjustments = $this->getAdjustments($templateId);

        if (isset($adjustments['aspect_ratings'][$aspectCode])) {
            return (float) $adjustments['aspect_ratings'][$aspectCode];
        }

        // Priority 2: Custom standard
        $customStandardId = Session::get("selected_standard.{$templateId}");
        if ($customStandardId) {
            $customStandard = CustomStandard::find($customStandardId);
            if ($customStandard && isset($customStandard->aspect_configs[$aspectCode]['rating'])) {
                return (float) $customStandard->aspect_configs[$aspectCode]['rating'];
            }
        }

        // Priority 3: Quantum default
        return $this->getOriginalAspectRating($templateId, $aspectCode);
    }

    /**
     * Get sub-aspect standard rating (adjusted or original)
     * Priority: 1. Session adjustment, 2. Custom standard, 3. Quantum default
     */
    public function getSubAspectRating(int $templateId, string $subAspectCode): int
    {
        // Priority 1: Session adjustment
        $adjustments = $this->getAdjustments($templateId);

        if (isset($adjustments['sub_aspect_ratings'][$subAspectCode])) {
            return (int) $adjustments['sub_aspect_ratings'][$subAspectCode];
        }

        // Priority 2: Custom standard
        $customStandardId = Session::get("selected_standard.{$templateId}");
        if ($customStandardId) {
            $customStandard = CustomStandard::find($customStandardId);
            if ($customStandard && isset($customStandard->sub_aspect_configs[$subAspectCode]['rating'])) {
                return (int) $customStandard->sub_aspect_configs[$subAspectCode]['rating'];
            }
        }

        // Priority 3: Quantum default
        return $this->getOriginalSubAspectRating($templateId, $subAspectCode);
    }

    // ========================================
    // RESET METHODS (NO CHANGES)
    // ========================================

    /**
     * Reset only category weights (Potensi + Kompetensi)
     */
    public function resetCategoryWeights(int $templateId): void
    {
        $adjustments = $this->getAdjustments($templateId);

        if (empty($adjustments)) {
            return;
        }

        // Remove both category weight adjustments
        if (isset($adjustments['category_weights']['potensi'])) {
            unset($adjustments['category_weights']['potensi']);
        }

        if (isset($adjustments['category_weights']['kompetensi'])) {
            unset($adjustments['category_weights']['kompetensi']);
        }

        // If category_weights is now empty, remove the key
        if (isset($adjustments['category_weights']) && empty($adjustments['category_weights'])) {
            unset($adjustments['category_weights']);
        }

        $this->saveOrForgetAdjustments($templateId, $adjustments);
    }

    /**
     * Reset adjustments for a specific category only
     */
    public function resetCategoryAdjustments(int $templateId, string $categoryCode): void
    {
        $adjustments = $this->getAdjustments($templateId);

        if (empty($adjustments)) {
            return;
        }

        // Load original template to get aspect codes for this category
        $template = AssessmentTemplate::with([
            'categoryTypes' => fn ($q) => $q->where('code', $categoryCode)->with('aspects.subAspects'),
        ])->find($templateId);

        if (! $template) {
            return;
        }

        $category = $template->categoryTypes->firstWhere('code', $categoryCode);
        if (! $category) {
            return;
        }

        // Remove category weight adjustment
        if (isset($adjustments['category_weights'][$categoryCode])) {
            unset($adjustments['category_weights'][$categoryCode]);
        }

        // Remove aspect-level adjustments for this category
        foreach ($category->aspects as $aspect) {
            if (isset($adjustments['aspect_weights'][$aspect->code])) {
                unset($adjustments['aspect_weights'][$aspect->code]);
            }
            if (isset($adjustments['aspect_ratings'][$aspect->code])) {
                unset($adjustments['aspect_ratings'][$aspect->code]);
            }
            if (isset($adjustments['active_aspects'][$aspect->code])) {
                unset($adjustments['active_aspects'][$aspect->code]);
            }

            // Remove sub-aspect adjustments
            if ($categoryCode === 'potensi' && $aspect->subAspects) {
                foreach ($aspect->subAspects as $subAspect) {
                    if (isset($adjustments['sub_aspect_ratings'][$subAspect->code])) {
                        unset($adjustments['sub_aspect_ratings'][$subAspect->code]);
                    }
                    if (isset($adjustments['active_sub_aspects'][$subAspect->code])) {
                        unset($adjustments['active_sub_aspects'][$subAspect->code]);
                    }
                }
            }
        }

        $this->saveOrForgetAdjustments($templateId, $adjustments);
    }

    // ========================================
    // OTHER METHODS (NO CHANGES)
    // ========================================

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
    // SELECTIVE ASPECTS/SUB-ASPECTS METHODS
    // ========================================

    /**
     * Check if aspect is active (selected for analysis)
     * Priority: 1. Session adjustment, 2. Custom standard, 3. Default (true)
     */
    public function isAspectActive(int $templateId, string $aspectCode): bool
    {
        // Priority 1: Session adjustment
        $adjustments = $this->getAdjustments($templateId);

        if (isset($adjustments['active_aspects'][$aspectCode])) {
            return (bool) $adjustments['active_aspects'][$aspectCode];
        }

        // Priority 2: Custom standard
        $customStandardId = Session::get("selected_standard.{$templateId}");
        if ($customStandardId) {
            $customStandard = CustomStandard::find($customStandardId);
            if ($customStandard && isset($customStandard->aspect_configs[$aspectCode]['active'])) {
                return (bool) $customStandard->aspect_configs[$aspectCode]['active'];
            }
        }

        // Priority 3: Default is active (true)
        return true;
    }

    /**
     * Check if sub-aspect is active (selected for analysis)
     * Priority: 1. Session adjustment, 2. Custom standard, 3. Default (true)
     */
    public function isSubAspectActive(int $templateId, string $subAspectCode): bool
    {
        // Priority 1: Session adjustment
        $adjustments = $this->getAdjustments($templateId);

        if (isset($adjustments['active_sub_aspects'][$subAspectCode])) {
            return (bool) $adjustments['active_sub_aspects'][$subAspectCode];
        }

        // Priority 2: Custom standard
        $customStandardId = Session::get("selected_standard.{$templateId}");
        if ($customStandardId) {
            $customStandard = CustomStandard::find($customStandardId);
            if ($customStandard && isset($customStandard->sub_aspect_configs[$subAspectCode]['active'])) {
                return (bool) $customStandard->sub_aspect_configs[$subAspectCode]['active'];
            }
        }

        // Priority 3: Default is active (true)
        return true;
    }

    /**
     * Get all active aspects for a template
     */
    public function getActiveAspects(int $templateId): array
    {
        $adjustments = $this->getAdjustments($templateId);

        // If no active_aspects set, return all aspects as active
        if (! isset($adjustments['active_aspects'])) {
            $template = AssessmentTemplate::with('aspects')->findOrFail($templateId);

            return $template->aspects->pluck('code')->toArray();
        }

        // Return only aspects where value is true
        return array_keys(array_filter($adjustments['active_aspects'], fn ($active) => $active === true));
    }

    /**
     * Get all active sub-aspects for a template
     */
    public function getActiveSubAspects(int $templateId): array
    {
        $adjustments = $this->getAdjustments($templateId);

        // If no active_sub_aspects set, return all sub-aspects as active
        if (! isset($adjustments['active_sub_aspects'])) {
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
     * Validate selection (active aspects, sub-aspects, and weights)
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
            $hasPotensiData = false;
            $hasKompetensiData = false;

            foreach ($data['active_aspects'] as $aspectCode => $isActive) {
                if (isset($potensiAspects[$aspectCode])) {
                    $hasPotensiData = true;
                    if ($isActive) {
                        $potensiActiveCount++;
                    }
                } elseif (isset($kompetensiAspects[$aspectCode])) {
                    $hasKompetensiData = true;
                    if ($isActive) {
                        $kompetensiActiveCount++;
                    }
                }
            }

            if ($hasPotensiData && $potensiActiveCount < 3) {
                $errors[] = "Minimal 3 aspek Potensi harus aktif (saat ini: {$potensiActiveCount})";
            }

            if ($hasKompetensiData && $kompetensiActiveCount < 3) {
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
                        if (
                            isset($data['active_sub_aspects'][$subAspect->code]) &&
                            $data['active_sub_aspects'][$subAspect->code]
                        ) {
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

    /**
     * Get active aspect IDs for a category
     */
    public function getActiveAspectIds(int $templateId, string $categoryCode): array
    {
        $aspects = Aspect::where('template_id', $templateId)
            ->whereHas('categoryType', fn ($q) => $q->where('code', $categoryCode))
            ->get();

        $activeIds = [];
        foreach ($aspects as $aspect) {
            if ($this->isAspectActive($templateId, $aspect->code)) {
                $activeIds[] = $aspect->id;
            }
        }

        return $activeIds;
    }
}
