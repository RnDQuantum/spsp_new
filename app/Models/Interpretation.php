<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Interpretation extends Model
{
    use HasFactory;

    protected $fillable = [
        'participant_id',
        'event_id',
        'category_type_id',
        'interpretation_text',
    ];

    public function participant(): BelongsTo
    {
        return $this->belongsTo(Participant::class);
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(AssessmentEvent::class, 'event_id');
    }

    public function categoryType(): BelongsTo
    {
        return $this->belongsTo(CategoryType::class);
    }
}
