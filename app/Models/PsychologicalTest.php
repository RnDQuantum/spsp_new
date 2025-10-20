<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PsychologicalTest extends Model
{
    use HasFactory;

    protected $fillable = [
        'participant_id',
        'event_id',
        'raw_score',
        'iq_score',
        'validity_status',
        'internal_status',
        'interpersonal_status',
        'work_capacity_status',
        'clinical_status',
        'conclusion_code',
        'conclusion_text',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'raw_score' => 'decimal:2',
            'iq_score' => 'integer',
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
}
