<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AssessmentEvent extends Model
{
    protected $fillable = [
        'institution_id',
        'template_id',
        'code',
        'name',
        'description',
        'year',
        'start_date',
        'end_date',
        'status',
        'last_synced_at',
    ];

    protected function casts(): array
    {
        return [
            'year' => 'integer',
            'start_date' => 'date',
            'end_date' => 'date',
            'last_synced_at' => 'datetime',
        ];
    }

    public function institution(): BelongsTo
    {
        return $this->belongsTo(Institution::class);
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(AssessmentTemplate::class, 'template_id');
    }

    public function batches(): HasMany
    {
        return $this->hasMany(Batch::class, 'event_id');
    }

    public function positionFormations(): HasMany
    {
        return $this->hasMany(PositionFormation::class, 'event_id');
    }

    public function participants(): HasMany
    {
        return $this->hasMany(Participant::class, 'event_id');
    }
}
