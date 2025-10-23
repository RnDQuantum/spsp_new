<?php

namespace App\Services;

use App\Models\InterpretationTemplate;
use Illuminate\Support\Facades\Cache;

class InterpretationTemplateService
{
    /**
     * Get template text for specific sub-aspect/aspect + rating
     *
     * @param string $type 'sub_aspect' or 'aspect'
     * @param string $name The name of sub_aspect or aspect
     * @param int $rating Rating value 1-5
     * @return string|null
     */
    public function getTemplateByName(string $type, string $name, int $rating): ?string
    {
        // Cache key for performance
        $cacheKey = "interpretation_template_{$type}_" . md5($name) . "_{$rating}";

        $templateText = Cache::remember($cacheKey, now()->addDay(), function () use ($type, $name, $rating) {
            // Try to get template by name first
            $template = InterpretationTemplate::where('interpretable_type', $type)
                ->where('interpretable_name', $name)
                ->where('rating_value', $rating)
                ->where('is_active', true)
                ->first();

            if ($template) {
                return $template->template_text;
            }

            // Fallback to generic template (interpretable_name = null or empty)
            $genericTemplate = InterpretationTemplate::where('interpretable_type', $type)
                ->whereNull('interpretable_name')
                ->where('rating_value', $rating)
                ->where('is_active', true)
                ->first();

            return $genericTemplate?->template_text;
        });

        // Replace placeholder [nama aspek] with actual aspect name
        if ($templateText) {
            $templateText = str_replace('[nama aspek]', $name, $templateText);
        }

        return $templateText;
    }

    /**
     * Legacy method - kept for backward compatibility
     * @deprecated Use getTemplateByName instead
     */
    public function getTemplate(string $type, int $id, int $rating): ?string
    {
        // Cache key for performance
        $cacheKey = "interpretation_template_{$type}_{$id}_{$rating}";

        return Cache::remember($cacheKey, now()->addDay(), function () use ($type, $id, $rating) {
            // Try to get specific template first
            $template = InterpretationTemplate::where('interpretable_type', $type)
                ->where('interpretable_id', $id)
                ->where('rating_value', $rating)
                ->where('is_active', true)
                ->first();

            if ($template) {
                return $template->template_text;
            }

            // Fallback to generic template (interpretable_id = 0)
            $genericTemplate = InterpretationTemplate::where('interpretable_type', $type)
                ->where('interpretable_id', 0)
                ->where('rating_value', $rating)
                ->where('is_active', true)
                ->first();

            return $genericTemplate?->template_text;
        });
    }

    /**
     * Get hardcoded default template if no template found in database
     * This is a last resort fallback
     *
     * @param int $rating
     * @return string
     */
    public function getDefaultTemplate(int $rating): string
    {
        return match ($rating) {
            1 => 'Kemampuan dalam aspek ini tergolong sangat kurang dan memerlukan pengembangan intensif.',
            2 => 'Kemampuan dalam aspek ini masih kurang memadai dan perlu ditingkatkan.',
            3 => 'Kemampuan dalam aspek ini cukup memadai dan sesuai dengan ekspektasi umum.',
            4 => 'Kemampuan dalam aspek ini tergolong baik dan dapat diandalkan.',
            5 => 'Kemampuan dalam aspek ini sangat baik dan menjadi kekuatan utama individu.',
            default => 'Data tidak tersedia.',
        };
    }

    /**
     * Clear template cache
     */
    public function clearCache(): void
    {
        Cache::flush();
    }

    /**
     * Get all templates for a specific interpretable
     *
     * @param string $type
     * @param int $id
     * @return \Illuminate\Support\Collection
     */
    public function getTemplatesForInterpretable(string $type, int $id)
    {
        return InterpretationTemplate::where('interpretable_type', $type)
            ->where('interpretable_id', $id)
            ->where('is_active', true)
            ->orderBy('rating_value')
            ->get();
    }
}
