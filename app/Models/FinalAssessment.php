<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FinalAssessment extends Model
{
    protected $fillable = [
        'participant_id',
        'potensi_weight',
        'potensi_standard_score',
        'potensi_individual_score',
        'kompetensi_weight',
        'kompetensi_standard_score',
        'kompetensi_individual_score',
        'total_standard_score',
        'total_individual_score',
        'achievement_percentage',
        'conclusion_code',
        'conclusion_text',
    ];

    protected function casts(): array
    {
        return [
            'potensi_weight' => 'integer',
            'potensi_standard_score' => 'decimal:2',
            'potensi_individual_score' => 'decimal:2',
            'kompetensi_weight' => 'integer',
            'kompetensi_standard_score' => 'decimal:2',
            'kompetensi_individual_score' => 'decimal:2',
            'total_standard_score' => 'decimal:2',
            'total_individual_score' => 'decimal:2',
            'achievement_percentage' => 'decimal:2',
        ];
    }

    public function participant(): BelongsTo
    {
        return $this->belongsTo(Participant::class);
    }
}
