<?php

namespace App\Models;

use App\Models\Scopes\InstitutionScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Participant extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id',
        'institution_id',
        'batch_id',
        'position_formation_id',
        'username',
        'test_number',
        'skb_number',
        'name',
        'tempat_lahir',
        'tanggal_lahir',
        'gelar_depan',
        'gelar_belakang',
        'pendidikan',
        'agama',
        'status_perkawinan',
        'email',
        'phone',
        'gender',
        'photo_path',
        'assessment_date',
        'nik',
        'no_kjg',
        'jabatan_pelaksana',
        'jbt_fungsional',
        'jbt_struktural',
        'pangkat',
        'golongan',
        'status_kepegawaian',
        'unit_kerja',
        'minat_penempatan',
        'pengalaman_kerja',
    ];

    protected static function booted()
    {
        static::addGlobalScope(new InstitutionScope);
    }

    protected function casts(): array
    {
        return [
            'assessment_date' => 'date',
            'tanggal_lahir' => 'date',
        ];
    }

    public function institution(): BelongsTo
    {
        return $this->belongsTo(Institution::class);
    }

    public function assessmentEvent(): BelongsTo
    {
        return $this->belongsTo(AssessmentEvent::class, 'event_id');
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(AssessmentEvent::class, 'event_id');
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class);
    }

    public function positionFormation(): BelongsTo
    {
        return $this->belongsTo(PositionFormation::class);
    }

    public function categoryAssessments(): HasMany
    {
        return $this->hasMany(CategoryAssessment::class);
    }

    public function interpretations(): HasMany
    {
        return $this->hasMany(Interpretation::class);
    }

    public function finalAssessment(): HasOne
    {
        return $this->hasOne(FinalAssessment::class);
    }

    public function mmpi(): HasOne
    {
        return $this->hasOne(Mmpi::class);
    }

    public function testResults(): HasMany
    {
        return $this->hasMany(TestResult::class);
    }

    public function careerHistories(): HasMany
    {
        return $this->hasMany(ParticipantCareerHistory::class)->orderBy('order_index');
    }

    public function performanceRecords(): HasMany
    {
        return $this->hasMany(ParticipantPerformanceRecord::class)->orderBy('year', 'asc');
    }

    public function personalProfile(): HasOne
    {
        return $this->hasOne(ParticipantPersonalProfile::class);
    }
}
