<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\AssessmentTemplate;
use App\Models\CustomStandard;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Session;

class CustomStandardService
{
    /**
     * Session key prefix for selected standard
     */
    private const SESSION_PREFIX = 'selected_standard';

    /**
     * Get all custom standards for an institution and template
     */
    public function getForInstitution(int $institutionId, int $templateId): Collection
    {
        return CustomStandard::where('institution_id', $institutionId)
            ->where('template_id', $templateId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
    }

    /**
     * Get all custom standards for an institution (all templates)
     */
    public function getAllForInstitution(int $institutionId): Collection
    {
        return CustomStandard::where('institution_id', $institutionId)
            ->where('is_active', true)
            ->with('template')
            ->orderBy('name')
            ->get();
    }

    /**
     * Create a new custom standard
     */
    public function create(array $data): CustomStandard
    {
        return CustomStandard::create([
            'institution_id' => $data['institution_id'],
            'template_id' => $data['template_id'],
            'code' => $data['code'],
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'category_weights' => $data['category_weights'],
            'aspect_configs' => $data['aspect_configs'],
            'sub_aspect_configs' => $data['sub_aspect_configs'],
            'created_by' => $data['created_by'] ?? auth()->id(),
        ]);
    }

    /**
     * Update an existing custom standard
     */
    public function update(CustomStandard $customStandard, array $data): CustomStandard
    {
        $customStandard->update([
            'code' => $data['code'] ?? $customStandard->code,
            'name' => $data['name'] ?? $customStandard->name,
            'description' => $data['description'] ?? $customStandard->description,
            'category_weights' => $data['category_weights'] ?? $customStandard->category_weights,
            'aspect_configs' => $data['aspect_configs'] ?? $customStandard->aspect_configs,
            'sub_aspect_configs' => $data['sub_aspect_configs'] ?? $customStandard->sub_aspect_configs,
        ]);

        return $customStandard->fresh();
    }

    /**
     * Delete a custom standard
     */
    public function delete(CustomStandard $customStandard): bool
    {
        return $customStandard->delete() ?: false;
    }

    /**
     * Get default values from template (for form initialization)
     *
     * Logic (DATA-DRIVEN):
     * - All categories get weights
     * - All aspects get weights + active status
     * - Aspects WITHOUT sub-aspects get rating field
     * - All sub-aspects get rating + active status
     */
    public function getTemplateDefaults(int $templateId): array
    {
        $template = AssessmentTemplate::with([
            'categoryTypes.aspects.subAspects',
        ])->findOrFail($templateId);

        $categoryWeights = [];
        $aspectConfigs = [];
        $subAspectConfigs = [];

        foreach ($template->categoryTypes as $category) {
            // Category weights (all categories)
            $categoryWeights[$category->code] = $category->weight_percentage;

            foreach ($category->aspects as $aspect) {
                // Base aspect config (all aspects)
                $aspectConfigs[$aspect->code] = [
                    'weight' => $aspect->weight_percentage,
                    'active' => true,
                ];

                // ✅ DATA-DRIVEN: Add rating only if aspect has NO sub-aspects
                if ($aspect->subAspects->isEmpty()) {
                    $aspectConfigs[$aspect->code]['rating'] = (float) $aspect->standard_rating;
                }

                // Sub-aspect configs (if aspect has sub-aspects)
                foreach ($aspect->subAspects as $subAspect) {
                    $subAspectConfigs[$subAspect->code] = [
                        'rating' => $subAspect->standard_rating,
                        'active' => true,
                    ];
                }
            }
        }

        return [
            'template' => $template,
            'category_weights' => $categoryWeights,
            'aspect_configs' => $aspectConfigs,
            'sub_aspect_configs' => $subAspectConfigs,
        ];
    }

    /**
     * Select a custom standard (store in session)
     */
    public function select(int $templateId, ?int $customStandardId): void
    {
        Session::put(self::SESSION_PREFIX.".{$templateId}", $customStandardId);

        // Reset dynamic adjustments when switching standard
        Session::forget("standard_adjustment.{$templateId}");
    }

    /**
     * Get currently selected custom standard ID
     */
    public function getSelected(int $templateId): ?int
    {
        return Session::get(self::SESSION_PREFIX.".{$templateId}");
    }

    /**
     * Get currently selected custom standard model
     */
    public function getSelectedStandard(int $templateId): ?CustomStandard
    {
        $id = $this->getSelected($templateId);

        if ($id === null) {
            return null;
        }

        return CustomStandard::find($id);
    }

    /**
     * Clear selection (revert to Quantum default)
     */
    public function clearSelection(int $templateId): void
    {
        Session::forget(self::SESSION_PREFIX.".{$templateId}");
        Session::forget("standard_adjustment.{$templateId}");
    }

    /**
     * Get aspect weight from custom standard
     */
    public function getAspectWeight(int $customStandardId, string $aspectCode): ?int
    {
        $customStandard = CustomStandard::find($customStandardId);

        if (! $customStandard) {
            return null;
        }

        return $customStandard->aspect_configs[$aspectCode]['weight'] ?? null;
    }

    /**
     * Get aspect rating from custom standard
     */
    public function getAspectRating(int $customStandardId, string $aspectCode): ?float
    {
        $customStandard = CustomStandard::find($customStandardId);

        if (! $customStandard) {
            return null;
        }

        return isset($customStandard->aspect_configs[$aspectCode]['rating'])
            ? (float) $customStandard->aspect_configs[$aspectCode]['rating']
            : null;
    }

    /**
     * Get sub-aspect rating from custom standard
     */
    public function getSubAspectRating(int $customStandardId, string $subAspectCode): ?int
    {
        $customStandard = CustomStandard::find($customStandardId);

        if (! $customStandard) {
            return null;
        }

        return $customStandard->sub_aspect_configs[$subAspectCode]['rating'] ?? null;
    }

    /**
     * Get category weight from custom standard
     */
    public function getCategoryWeight(int $customStandardId, string $categoryCode): ?int
    {
        $customStandard = CustomStandard::find($customStandardId);

        if (! $customStandard) {
            return null;
        }

        return $customStandard->category_weights[$categoryCode] ?? null;
    }

    /**
     * Check if aspect is active in custom standard
     */
    public function isAspectActive(int $customStandardId, string $aspectCode): bool
    {
        $customStandard = CustomStandard::find($customStandardId);

        if (! $customStandard) {
            return true;
        }

        return $customStandard->aspect_configs[$aspectCode]['active'] ?? true;
    }

    /**
     * Check if sub-aspect is active in custom standard
     */
    public function isSubAspectActive(int $customStandardId, string $subAspectCode): bool
    {
        $customStandard = CustomStandard::find($customStandardId);

        if (! $customStandard) {
            return true;
        }

        return $customStandard->sub_aspect_configs[$subAspectCode]['active'] ?? true;
    }

    /**
     * Validate custom standard data
     */
    public function validate(array $data): array
    {
        $errors = [];

        // Validate category weights sum to 100
        if (isset($data['category_weights'])) {
            $total = array_sum($data['category_weights']);
            if ($total !== 100) {
                $errors['category_weights'] = "Total bobot kategori harus 100% (saat ini: {$total}%)";
            }
        }

        // Validate aspect configs
        if (isset($data['aspect_configs'])) {
            foreach ($data['aspect_configs'] as $code => $config) {
                // Validate rating range if exists
                if (isset($config['rating']) && ($config['rating'] < 1 || $config['rating'] > 5)) {
                    $errors["aspect_configs.{$code}.rating"] = 'Rating harus antara 1-5';
                }
            }
        }

        // Validate sub-aspect configs
        if (isset($data['sub_aspect_configs'])) {
            foreach ($data['sub_aspect_configs'] as $code => $config) {
                // Validate rating range
                if (isset($config['rating']) && ($config['rating'] < 1 || $config['rating'] > 5)) {
                    $errors["sub_aspect_configs.{$code}.rating"] = 'Rating harus antara 1-5';
                }
            }
        }

        return $errors;
    }

    /**
     * Check if code is unique for institution
     */
    public function isCodeUnique(int $institutionId, string $code, ?int $excludeId = null): bool
    {
        $query = CustomStandard::where('institution_id', $institutionId)
            ->where('code', $code);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return ! $query->exists();
    }

    /**
     * Get available templates for institution
     * Only returns templates that are used by the institution's events
     */
    public function getAvailableTemplatesForInstitution(int $institutionId): \Illuminate\Support\Collection
    {
        return AssessmentTemplate::whereHas('positionFormations.assessmentEvent', function ($query) use ($institutionId) {
            $query->where('institution_id', $institutionId);
        })
            ->distinct()
            ->orderBy('name')
            ->get();
    }
}
