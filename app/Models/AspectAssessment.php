<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AspectAssessment extends Model
{
    protected $fillable = [
        'category_assessment_id',
        'aspect_id',
        'standard_rating',
        'standard_score',
        'individual_rating',
        'individual_score',
        'gap_rating',
        'gap_score',
        'percentage_score',
        'conclusion_code',
        'conclusion_text',
        'description_text',
    ];

    protected function casts(): array
    {
        return [
            'standard_rating' => 'decimal:2',
            'standard_score' => 'decimal:2',
            'individual_rating' => 'decimal:2',
            'individual_score' => 'decimal:2',
            'gap_rating' => 'decimal:2',
            'gap_score' => 'decimal:2',
            'percentage_score' => 'integer',
        ];
    }

    public function categoryAssessment(): BelongsTo
    {
        return $this->belongsTo(CategoryAssessment::class);
    }

    public function aspect(): BelongsTo
    {
        return $this->belongsTo(Aspect::class);
    }

    public function subAspectAssessments(): HasMany
    {
        return $this->hasMany(SubAspectAssessment::class);
    }
}
