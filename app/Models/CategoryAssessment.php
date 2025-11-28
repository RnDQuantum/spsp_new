<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CategoryAssessment extends Model
{
    use HasFactory;
    protected $fillable = [
        'participant_id',
        'event_id',
        'batch_id',
        'position_formation_id',
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
            'total_standard_rating' => 'float',
            'total_standard_score' => 'float',
            'total_individual_rating' => 'float',
            'total_individual_score' => 'float',
            'gap_rating' => 'float',
            'gap_score' => 'float',
        ];
    }

    public function participant(): BelongsTo
    {
        return $this->belongsTo(Participant::class);
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(AssessmentEvent::class, 'event_id');
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class);
    }

    public function positionFormation(): BelongsTo
    {
        return $this->belongsTo(PositionFormation::class);
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
