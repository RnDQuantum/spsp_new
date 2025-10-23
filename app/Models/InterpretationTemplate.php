<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class InterpretationTemplate extends Model
{
    protected $fillable = [
        'interpretable_type',
        'interpretable_id',
        'rating_value',
        'template_text',
        'tone',
        'category',
        'version',
        'is_active',
    ];

    protected $casts = [
        'rating_value' => 'integer',
        'is_active' => 'boolean',
    ];

    /**
     * Polymorphic relation to SubAspect or Aspect
     */
    public function interpretable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Scope: Get active templates only
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: Get by rating value
     */
    public function scopeForRating($query, int $rating)
    {
        return $query->where('rating_value', $rating);
    }
}
