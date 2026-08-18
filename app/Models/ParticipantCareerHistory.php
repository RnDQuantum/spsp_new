<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * App\Models\ParticipantCareerHistory
 *
 * @property int $id
 * @property int $participant_id
 * @property string $position_title
 * @property string $company_or_institution
 * @property int $start_year
 * @property int|null $end_year
 * @property bool $is_current
 * @property array|null $achievements
 * @property int $order_index
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Participant $participant
 */
class ParticipantCareerHistory extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'participant_career_histories';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'participant_id',
        'position_title',
        'company_or_institution',
        'start_year',
        'end_year',
        'is_current',
        'achievements',
        'order_index',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'participant_id' => 'integer',
        'start_year' => 'integer',
        'end_year' => 'integer',
        'is_current' => 'boolean',
        'achievements' => 'array',
        'order_index' => 'integer',
    ];

    /**
     * Get the participant that owns the career history.
     */
    public function participant(): BelongsTo
    {
        return $this->belongsTo(Participant::class, 'participant_id');
    }
}
