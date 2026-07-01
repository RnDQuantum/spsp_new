<?php

declare(strict_types=1);

namespace App\Support;

class WeightValidator
{
    /**
     * Validate total weight equals expected value
     *
     * @param  array  $weights  Array of weights to validate
     * @param  int  $expected  Expected total sum (default: 100)
     * @return string|null Error message, or null if valid
     */
    public static function validateTotal(array $weights, int $expected = 100): ?string
    {
        $total = array_sum($weights);
        if ($total !== $expected) {
            return "Total bobot harus {$expected}% (saat ini: {$total}%)";
        }
        return null;
    }
}
