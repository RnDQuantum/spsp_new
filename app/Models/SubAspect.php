<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SubAspect extends Model
{
    use HasFactory;

    protected $fillable = [
        'aspect_id',
        'code',
        'name',
        'description',
        'standard_rating',
        'order',
    ];

    protected function casts(): array
    {
        return [
            'standard_rating' => 'integer',
            'order' => 'integer',
        ];
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
