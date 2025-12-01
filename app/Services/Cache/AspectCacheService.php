<?php

declare(strict_types=1);

namespace App\Services\Cache;

use App\Models\Aspect;
use App\Models\CategoryType;
use App\Models\SubAspect;
use Illuminate\Support\Collection;

/**
 * AspectCacheService - In-Memory Cache for Aspect Queries
 *
 * Purpose: Eliminate N+1 queries by caching aspect/sub-aspect lookups during request lifecycle
 *
 * Usage:
 * - Cache is per-request only (cleared automatically after request)
 * - Preload data with preloadByTemplate() at the start of calculations
 * - Use getByCode(), getSubAspectByCode() for lookups
 */
class AspectCacheService
{
    /**
     * Cache for aspects by template_id:code
     */
    private static array $aspectCache = [];

    /**
     * Cache for sub-aspects by template_id:code
     */
    private static array $subAspectCache = [];

    /**
     * Cache for category types by template_id:code
     */
    private static array $categoryCache = [];

    /**
     * Cache for category types by ID
     */
    private static array $categoryByIdCache = [];

    /**
     * Cache for aspects by ID
     */
    private static array $aspectByIdCache = [];

    /**
     * Cache tracking for preloaded templates
     */
    private static array $preloadedTemplates = [];

    /**
     * Preload all aspects and sub-aspects for a template
     *
     * This method should be called ONCE at the start of calculations
     * to load all data into memory and avoid N+1 queries
     *
     * @param  int  $templateId  Template ID
     */
    public static function preloadByTemplate(int $templateId): void
    {
        // Skip if already preloaded for this template
        if (isset(self::$preloadedTemplates[$templateId])) {
            return;
        }

        // Load all aspects with sub-aspects in one query
        $aspects = Aspect::where('template_id', $templateId)
            ->with(['subAspects', 'categoryType'])
            ->get();

        // Cache aspects by code and ID
        foreach ($aspects as $aspect) {
            $key = "{$templateId}:{$aspect->code}";
            self::$aspectCache[$key] = $aspect;
            self::$aspectByIdCache[$aspect->id] = $aspect;

            // Cache sub-aspects
            foreach ($aspect->subAspects as $subAspect) {
                $subKey = "{$templateId}:{$subAspect->code}";
                self::$subAspectCache[$subKey] = $subAspect;
            }
        }

        // Load category types
        $categories = CategoryType::where('template_id', $templateId)->get();
        foreach ($categories as $category) {
            $catKey = "{$templateId}:{$category->code}";
            self::$categoryCache[$catKey] = $category;
            self::$categoryByIdCache[$category->id] = $category;
        }

        // Mark this template as preloaded
        self::$preloadedTemplates[$templateId] = true;
    }

    /**
     * Get aspect by template ID and code
     *
     * @param  int  $templateId  Template ID
     * @param  string  $code  Aspect code
     */
    public static function getByCode(int $templateId, string $code): ?Aspect
    {
        $key = "{$templateId}:{$code}";

        if (! isset(self::$aspectCache[$key])) {
            // Cache miss - load from database
            $aspect = Aspect::where('template_id', $templateId)
                ->where('code', $code)
                ->with(['subAspects', 'categoryType'])
                ->first();

            self::$aspectCache[$key] = $aspect;

            if ($aspect) {
                self::$aspectByIdCache[$aspect->id] = $aspect;
            }
        }

        return self::$aspectCache[$key];
    }

    /**
     * Get aspect by ID
     *
     * @param  int  $id  Aspect ID
     */
    public static function getById(int $id): ?Aspect
    {
        if (! isset(self::$aspectByIdCache[$id])) {
            $aspect = Aspect::with(['subAspects', 'categoryType'])->find($id);
            self::$aspectByIdCache[$id] = $aspect;

            if ($aspect) {
                $key = "{$aspect->template_id}:{$aspect->code}";
                self::$aspectCache[$key] = $aspect;
            }
        }

        return self::$aspectByIdCache[$id];
    }

    /**
     * Get sub-aspect by template ID and code
     *
     * @param  int  $templateId  Template ID
     * @param  string  $code  Sub-aspect code
     */
    public static function getSubAspectByCode(int $templateId, string $code): ?SubAspect
    {
        $key = "{$templateId}:{$code}";

        if (! isset(self::$subAspectCache[$key])) {
            // Cache miss - load from database
            $subAspect = SubAspect::whereHas('aspect', function ($query) use ($templateId) {
                $query->where('template_id', $templateId);
            })
                ->where('code', $code)
                ->with('aspect')
                ->first();

            self::$subAspectCache[$key] = $subAspect;
        }

        return self::$subAspectCache[$key];
    }

    /**
     * Get category type by template ID and code
     *
     * @param  int  $templateId  Template ID
     * @param  string  $code  Category code
     */
    public static function getCategoryByCode(int $templateId, string $code): ?CategoryType
    {
        $key = "{$templateId}:{$code}";

        if (! isset(self::$categoryCache[$key])) {
            $category = CategoryType::where('template_id', $templateId)
                ->where('code', $code)
                ->first();

            self::$categoryCache[$key] = $category;

            if ($category) {
                self::$categoryByIdCache[$category->id] = $category;
            }
        }

        return self::$categoryCache[$key];
    }

    /**
     * Get category type by ID
     *
     * @param  int  $id  Category ID
     */
    public static function getCategoryById(int $id): ?CategoryType
    {
        if (! isset(self::$categoryByIdCache[$id])) {
            $category = CategoryType::find($id);
            self::$categoryByIdCache[$id] = $category;

            if ($category) {
                $key = "{$category->template_id}:{$category->code}";
                self::$categoryCache[$key] = $category;
            }
        }

        return self::$categoryByIdCache[$id];
    }

    /**
     * Get all aspects for a template by category code
     *
     * @param  int  $templateId  Template ID
     * @param  string  $categoryCode  Category code
     */
    public static function getAspectsByCategory(int $templateId, string $categoryCode): Collection
    {
        // Ensure data is preloaded
        if (empty(self::$aspectCache)) {
            self::preloadByTemplate($templateId);
        }

        // Filter aspects by category code
        return collect(self::$aspectCache)
            ->filter(function ($aspect, $key) use ($templateId, $categoryCode) {
                if (! $aspect) {
                    return false;
                }

                return $aspect->template_id === $templateId
                    && $aspect->categoryType
                    && $aspect->categoryType->code === $categoryCode;
            })
            ->values();
    }

    /**
     * Clear all caches
     *
     * Use this when data changes during the request
     * (e.g., after saving adjustments)
     */
    public static function clearCache(): void
    {
        self::$aspectCache = [];
        self::$subAspectCache = [];
        self::$categoryCache = [];
        self::$categoryByIdCache = [];
        self::$aspectByIdCache = [];
        self::$preloadedTemplates = [];
    }

    /**
     * Get cache statistics (for debugging)
     */
    public static function getCacheStats(): array
    {
        return [
            'aspects_cached' => count(self::$aspectCache),
            'sub_aspects_cached' => count(self::$subAspectCache),
            'categories_cached' => count(self::$categoryCache),
            'categories_by_id_cached' => count(self::$categoryByIdCache),
            'aspects_by_id_cached' => count(self::$aspectByIdCache),
            'preloaded_templates' => array_keys(self::$preloadedTemplates),
        ];
    }
}
