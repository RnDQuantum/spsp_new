<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubAspectAssessment extends Model
{
    protected $fillable = [
        'aspect_assessment_id',
        'sub_aspect_id',
        'standard_rating',
        'individual_rating',
        'rating_label',
    ];

    protected function casts(): array
    {
        return [
            'standard_rating' => 'integer',
            'individual_rating' => 'integer',
        ];
    }

    public function aspectAssessment(): BelongsTo
    {
        return $this->belongsTo(AspectAssessment::class);
    }

    public function subAspect(): BelongsTo
    {
        return $this->belongsTo(SubAspect::class);
    }
}
