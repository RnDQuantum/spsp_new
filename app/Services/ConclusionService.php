<?php

declare(strict_types=1);

namespace App\Services;

/**
 * ConclusionService - Single Source of Truth for Conclusion Logic
 *
 * This service centralizes all conclusion categorization logic and styling:
 * 1. Gap-Based Conclusion (used in all reports except RingkasanAssessment)
 * 2. Potensial-Based Conclusion (used only in RingkasanAssessment)
 *
 * Benefits:
 * - Single source of truth for conclusion logic
 * - Centralized color and styling configuration
 * - Easy to maintain and test
 * - Consistent across all components
 */
class ConclusionService
{
    // ========================================
    // 1. GAP-BASED CONCLUSION (Standard System-wide)
    // ========================================

    /**
     * Conclusion configuration for gap-based logic
     * Used in ALL reports except RingkasanAssessment:
     * - Individual Reports: GeneralPsyMapping, GeneralMcMapping, GeneralMapping
     * - Ranking Reports: RankingPsyMapping, RankingMcMapping, RekapRankingAssessment
     */
    private const GAP_CONCLUSION_CONFIG = [
        'Di Atas Standar' => [
            'chartColor' => '#16a34a',      // green-600
            'tailwindClass' => 'bg-green-600 text-white',
            'rangeDescription' => 'Original Gap ≥ 0',
        ],
        'Memenuhi Standar' => [
            'chartColor' => '#facc15',      // yellow-400
            'tailwindClass' => 'bg-yellow-400 text-gray-900',
            'rangeDescription' => 'Adjusted Gap ≥ 0',
        ],
        'Di Bawah Standar' => [
            'chartColor' => '#dc2626',      // red-600
            'tailwindClass' => 'bg-red-600 text-white',
            'rangeDescription' => 'Adjusted Gap < 0',
        ],
    ];

    /**
     * Get gap-based conclusion text
     *
     * Logic:
     * - If original gap >= 0: "Di Atas Standar"
     * - Else if adjusted gap >= 0: "Memenuhi Standar"
     * - Else: "Di Bawah Standar"
     *
     * Used for:
     * - Aspect-level conclusions
     * - Category-level conclusions (Potensi, Kompetensi)
     * - Combined conclusions (Potensi + Kompetensi weighted)
     * - Ranking conclusions
     *
     * @param  float  $originalGap  Gap score at 0% tolerance
     * @param  float  $adjustedGap  Gap score with tolerance applied
     * @return string Conclusion text
     */
    public static function getGapBasedConclusion(float $originalGap, float $adjustedGap): string
    {
        if ($originalGap >= 0) {
            return 'Di Atas Standar';
        } elseif ($adjustedGap >= 0) {
            return 'Memenuhi Standar';
        } else {
            return 'Di Bawah Standar';
        }
    }

    /**
     * Get gap conclusion configuration
     *
     * @return array Configuration array with colors and styles
     */
    public static function getGapConclusionConfig(): array
    {
        return self::GAP_CONCLUSION_CONFIG;
    }

    // ========================================
    // 2. POTENSIAL-BASED CONCLUSION (RingkasanAssessment Only)
    // ========================================

    /**
     * Conclusion configuration for potensial-based logic
     * Used ONLY in RingkasanAssessment (final summary report)
     */
    private const POTENSIAL_CONCLUSION_CONFIG = [
        'Sangat Potensial' => [
            'chartColor' => '#16a34a',      // green-600
            'tailwindClass' => 'bg-green-600 text-white',
            'mappedFrom' => 'Di Atas Standar',
        ],
        'Potensial' => [
            'chartColor' => '#facc15',      // yellow-400
            'tailwindClass' => 'bg-yellow-400 text-gray-900',
            'mappedFrom' => 'Memenuhi Standar',
        ],
        'Kurang Potensial' => [
            'chartColor' => '#dc2626',      // red-600
            'tailwindClass' => 'bg-red-600 text-white',
            'mappedFrom' => 'Di Bawah Standar',
        ],
    ];

    /**
     * Map gap-based conclusion to potensial conclusion
     *
     * Mapping:
     * - "Di Atas Standar" → "Sangat Potensial"
     * - "Memenuhi Standar" → "Potensial"
     * - "Di Bawah Standar" → "Kurang Potensial"
     *
     * Used ONLY in RingkasanAssessment for user-friendly display
     *
     * @param  string  $gapConclusion  Gap-based conclusion text
     * @return string Potensial conclusion text
     */
    public static function getPotensialConclusion(string $gapConclusion): string
    {
        return match ($gapConclusion) {
            'Di Atas Standar' => 'Sangat Potensial',
            'Memenuhi Standar' => 'Potensial',
            'Di Bawah Standar' => 'Kurang Potensial',
            default => 'Kurang Potensial',
        };
    }

    /**
     * Get potensial conclusion configuration
     *
     * @return array Configuration array with colors and styles
     */
    public static function getPotensialConclusionConfig(): array
    {
        return self::POTENSIAL_CONCLUSION_CONFIG;
    }

    // ========================================
    // 3. GENERIC HELPERS
    // ========================================

    /**
     * Get chart color for a conclusion
     *
     * @param  string  $conclusionText  Conclusion text
     * @param  string  $type  Type: 'gap' or 'potensial'
     * @return string Hex color code
     */
    public static function getChartColor(string $conclusionText, string $type = 'gap'): string
    {
        $config = $type === 'potensial'
            ? self::POTENSIAL_CONCLUSION_CONFIG
            : self::GAP_CONCLUSION_CONFIG;

        return $config[$conclusionText]['chartColor'] ?? '#6b7280'; // gray-500 fallback
    }

    /**
     * Get Tailwind CSS class for a conclusion
     *
     * @param  string  $conclusionText  Conclusion text
     * @param  string  $type  Type: 'gap' or 'potensial'
     * @return string Tailwind CSS classes
     */
    public static function getTailwindClass(string $conclusionText, string $type = 'gap'): string
    {
        $config = $type === 'potensial'
            ? self::POTENSIAL_CONCLUSION_CONFIG
            : self::GAP_CONCLUSION_CONFIG;

        return $config[$conclusionText]['tailwindClass'] ?? 'bg-gray-500 text-white';
    }

    /**
     * Get range description for a conclusion
     *
     * @param  string  $conclusionText  Conclusion text
     * @param  string  $type  Type: 'gap' or 'potensial'
     * @return string Range description text
     */
    public static function getRangeDescription(string $conclusionText, string $type = 'gap'): string
    {
        $config = $type === 'potensial'
            ? self::POTENSIAL_CONCLUSION_CONFIG
            : self::GAP_CONCLUSION_CONFIG;

        return $config[$conclusionText]['rangeDescription'] ?? '';
    }

    /**
     * Get all conclusion types for a given type
     *
     * @param  string  $type  Type: 'gap' or 'potensial'
     * @return array Array of conclusion types
     */
    public static function getConclusionTypes(string $type = 'gap'): array
    {
        $config = $type === 'potensial'
            ? self::POTENSIAL_CONCLUSION_CONFIG
            : self::GAP_CONCLUSION_CONFIG;

        return array_keys($config);
    }

    /**
     * Get configuration for a specific conclusion
     *
     * @param  string  $conclusionText  Conclusion text
     * @param  string  $type  Type: 'gap' or 'potensial'
     * @return array|null Configuration array or null if not found
     */
    public static function getConfigFor(string $conclusionText, string $type = 'gap'): ?array
    {
        $config = $type === 'potensial'
            ? self::POTENSIAL_CONCLUSION_CONFIG
            : self::GAP_CONCLUSION_CONFIG;

        return $config[$conclusionText] ?? null;
    }
}
