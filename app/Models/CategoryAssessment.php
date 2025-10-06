<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CategoryAssessment extends Model
{
    protected $fillable = [
        'participant_id',
        'category_type_id',
        'total_standard_rating',
        'total_standard_score',
        'total_individual_rating',
        'total_individual_score',
        'gap_rating',
        'gap_score',
        'conclusion_code',
        'conclusion_text',
    ];

    protected function casts(): array
    {
        return [
            'total_standard_rating' => 'decimal:2',
            'total_standard_score' => 'decimal:2',
            'total_individual_rating' => 'decimal:2',
            'total_individual_score' => 'decimal:2',
            'gap_rating' => 'decimal:2',
            'gap_score' => 'decimal:2',
        ];
    }

    public function participant(): BelongsTo
    {
        return $this->belongsTo(Participant::class);
    }

    public function categoryType(): BelongsTo
    {
        return $this->belongsTo(CategoryType::class);
    }

    public function aspectAssessments(): HasMany
    {
        return $this->hasMany(AspectAssessment::class);
    }
}
