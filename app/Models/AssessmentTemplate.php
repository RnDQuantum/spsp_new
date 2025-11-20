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

    public function positionFormations(): HasMany
    {
        return $this->hasMany(PositionFormation::class, 'template_id');
    }

    public function aspects()
    {
        return $this->hasMany(Aspect::class, 'template_id');
    }

    public function customStandards(): HasMany
    {
        return $this->hasMany(CustomStandard::class, 'template_id');
    }
}
