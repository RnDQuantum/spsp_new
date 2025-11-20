<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Institution extends Model
{
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
}
