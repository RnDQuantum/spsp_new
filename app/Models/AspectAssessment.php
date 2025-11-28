<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AspectAssessment extends Model
{
    use HasFactory;
    protected $fillable = [
        'category_assessment_id',
        'participant_id',
        'event_id',
        'batch_id',
        'position_formation_id',
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

    public function aspect(): BelongsTo
    {
        return $this->belongsTo(Aspect::class);
    }

    public function subAspectAssessments(): HasMany
    {
        return $this->hasMany(SubAspectAssessment::class);
    }
}
