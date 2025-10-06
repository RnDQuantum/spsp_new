<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AssessmentTemplate extends Model
{
    protected $fillable = [
        'code',
        'name',
        'description',
    ];

    public function categoryTypes(): HasMany
    {
        return $this->hasMany(CategoryType::class, 'template_id');
    }

    public function assessmentEvents(): HasMany
    {
        return $this->hasMany(AssessmentEvent::class, 'template_id');
    }
}
