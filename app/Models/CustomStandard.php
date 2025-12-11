<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomStandard extends Model
{
    use HasFactory;
    protected $fillable = [
        'institution_id',
        'template_id',
        'code',
        'name',
        'description',
        'category_weights',
        'aspect_configs',
        'sub_aspect_configs',
        'is_active',
        'created_by',
    ];

    protected $attributes = [
        'is_active' => true,
    ];

    protected function casts(): array
    {
        return [
            'category_weights' => 'array',
            'aspect_configs' => 'array',
            'sub_aspect_configs' => 'array',
            'is_active' => 'boolean',
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

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
