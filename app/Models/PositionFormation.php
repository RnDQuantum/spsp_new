<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PositionFormation extends Model
{
    protected $fillable = [
        'event_id',
        'code',
        'name',
        'quota',
    ];

    protected function casts(): array
    {
        return [
            'quota' => 'integer',
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
