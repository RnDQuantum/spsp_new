<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PositionFormation extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id',
        'institution_id',
        'template_id',
        'code',
        'name',
        'quota',
    ];

    protected static function booted()
    {
        static::addGlobalScope(new \App\Models\Scopes\InstitutionScope);
    }

    protected function casts(): array
    {
        return [
            'quota' => 'integer',
        ];
    }

    public function institution(): BelongsTo
    {
        return $this->belongsTo(Institution::class);
    }

    public function assessmentEvent(): BelongsTo
    {
        return $this->belongsTo(AssessmentEvent::class, 'event_id');
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(AssessmentTemplate::class);
    }

    public function participants(): HasMany
    {
        return $this->hasMany(Participant::class);
    }
}
