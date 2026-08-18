<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ParticipantPersonalProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'participant_id',
        'blood_type',
        'hobbies',
        'sports',
        'zodiac',
        'chinese_zodiac',
        'weton',
        'medical_notes',
        'cultural_notes',
        'motto_or_values',
    ];

    public function participant(): BelongsTo
    {
        return $this->belongsTo(Participant::class);
    }
}
