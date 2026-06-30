<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PsychologicalTest extends Model
{
    use HasFactory;

    protected $fillable = [
        'participant_id',
        'event_id',
        'no_test',
        'username',
        'validitas',
        'internal',
        'interpersonal',
        'kap_kerja',
        'klinik',
        'kesimpulan',
        'psikogram',
        'nilai_pq',
        'tingkat_stres',
    ];

    protected function casts(): array
    {
        return [
            'nilai_pq' => 'decimal:2',
            'psikogram' => 'array',
        ];
    }

    public function participant(): BelongsTo
    {
        return $this->belongsTo(Participant::class);
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(AssessmentEvent::class, 'event_id');
    }
}
