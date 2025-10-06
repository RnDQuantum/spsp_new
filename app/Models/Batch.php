<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Batch extends Model
{
    protected $fillable = [
        'event_id',
        'code',
        'name',
        'location',
        'batch_number',
        'start_date',
        'end_date',
    ];

    protected function casts(): array
    {
        return [
            'batch_number' => 'integer',
            'start_date' => 'date',
            'end_date' => 'date',
        ];
    }

    public function assessmentEvent(): BelongsTo
    {
        return $this->belongsTo(AssessmentEvent::class, 'event_id');
    }

    public function participants(): HasMany
    {
        return $this->hasMany(Participant::class);
    }
}
