<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * App\Models\ParticipantPerformanceRecord
 *
 * @property int $id
 * @property int $participant_id
 * @property int $year
 * @property float $kpi_score
 * @property float $target_score
 * @property float $benchmark_score
 * @property string $performance_rating
 * @property array|null $kpi_breakdown
 * @property array|null $achievements
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Participant $participant
 */
class ParticipantPerformanceRecord extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'participant_performance_records';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'participant_id',
        'year',
        'kpi_score',
        'target_score',
        'benchmark_score',
        'performance_rating',
        'kpi_breakdown',
        'achievements',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'participant_id' => 'integer',
        'year' => 'integer',
        'kpi_score' => 'float',
        'target_score' => 'float',
        'benchmark_score' => 'float',
        'kpi_breakdown' => 'array',
        'achievements' => 'array',
    ];

    /**
     * Get the participant that owns the performance record.
     */
    public function participant(): BelongsTo
    {
        return $this->belongsTo(Participant::class, 'participant_id');
    }
}
