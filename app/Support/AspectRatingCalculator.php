<?php

declare(strict_types=1);

namespace App\Support;

class AspectRatingCalculator
{
    /**
     * Calculate average rating from active sub-aspects
     *
     * @param  iterable  $subAspects  Collection of sub-aspects
     * @param  callable  $getRating  Callback to get rating of a sub-aspect
     * @param  callable  $isActive  Callback to check if sub-aspect is active
     * @param  float|null  $fallback  Fallback value if no active sub-aspects exist
     * @return float Calculated average rating, or fallback
     */
    public static function averageFromSubAspects(
        iterable $subAspects,
        callable $getRating,
        callable $isActive,
        ?float $fallback = 0.0
    ): float {
        $sum = 0;
        $count = 0;

        foreach ($subAspects as $subAspect) {
            if (! $isActive($subAspect)) {
                continue;
            }
            $sum += (float) $getRating($subAspect);
            $count++;
        }

        return $count > 0 ? round($sum / $count, 2) : (float) $fallback;
    }
}
