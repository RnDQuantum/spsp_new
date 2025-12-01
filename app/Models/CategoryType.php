<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CategoryType extends Model
{
    use HasFactory;

    protected $fillable = [
        'template_id',
        'code',
        'name',
        'weight_percentage',
        'order',
    ];

    protected function casts(): array
    {
        return [
            'weight_percentage' => 'integer',
            'order' => 'integer',
        ];
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(AssessmentTemplate::class, 'template_id');
    }

    public function aspects(): HasMany
    {
        return $this->hasMany(Aspect::class);
    }

    public function categoryAssessments(): HasMany
    {
        return $this->hasMany(CategoryAssessment::class);
    }

    public function interpretations(): HasMany
    {
        return $this->hasMany(Interpretation::class);
    }
}
