<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Participant extends Model
{
    use HasFactory;
    protected $fillable = [
        'event_id',
        'batch_id',
        'position_formation_id',
        'test_number',
        'skb_number',
        'name',
        'email',
        'phone',
        'gender',
        'photo_path',
        'assessment_date',
    ];

    protected function casts(): array
    {
        return [
            'assessment_date' => 'date',
        ];
    }

    public function assessmentEvent(): BelongsTo
    {
        return $this->belongsTo(AssessmentEvent::class, 'event_id');
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

    public function categoryAssessments(): HasMany
    {
        return $this->hasMany(CategoryAssessment::class);
    }

    public function interpretations(): HasMany
    {
        return $this->hasMany(Interpretation::class);
    }

    public function finalAssessment(): HasOne
    {
        return $this->hasOne(FinalAssessment::class);
    }

    public function psychologicalTest(): HasOne
    {
        return $this->hasOne(PsychologicalTest::class);
    }
}
