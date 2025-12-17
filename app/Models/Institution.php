<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Institution extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'logo_path',
        'api_key',
    ];

    protected $hidden = [
        'api_key',
    ];

    public function assessmentEvents(): HasMany
    {
        return $this->hasMany(AssessmentEvent::class);
    }

    public function customStandards(): HasMany
    {
        return $this->hasMany(CustomStandard::class);
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(InstitutionCategory::class, 'category_institution')
            ->withPivot('is_primary')
            ->withTimestamps()
            ->orderBy('order');
    }

    public function primaryCategory(): BelongsToMany
    {
        return $this->categories()->wherePivot('is_primary', true);
    }
}
