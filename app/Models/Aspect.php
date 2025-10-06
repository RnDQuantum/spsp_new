<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Aspect extends Model
{
    protected $fillable = [
        'template_id',
        'category_type_id',
        'code',
        'name',
        'weight_percentage',
        'standard_rating',
        'order',
    ];

    protected function casts(): array
    {
        return [
            'weight_percentage' => 'integer',
            'standard_rating' => 'decimal:2',
            'order' => 'integer',
        ];
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(AssessmentTemplate::class);
    }

    public function categoryType(): BelongsTo
    {
        return $this->belongsTo(CategoryType::class);
    }

    public function subAspects(): HasMany
    {
        return $this->hasMany(SubAspect::class);
    }

    public function aspectAssessments(): HasMany
    {
        return $this->hasMany(AspectAssessment::class);
    }
}
