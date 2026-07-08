<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class TestResult extends Model
{
    use HasFactory;

    protected $fillable = [
        'participant_id',
        'event_id',
        'test_code',
        'test_name',
        'test_category',
        'status',
        'test_started_at',
        'summary_data',
        'interpretation_data',
        'raw_response',
        'conversion_status',
        'converted_at',
    ];

    protected function casts(): array
    {
        return [
            'summary_data' => 'array',
            'interpretation_data' => 'array',
            'raw_response' => 'array',
            'test_started_at' => 'datetime',
            'converted_at' => 'datetime',
        ];
    }

    // ─── Alat tes yang diproses (in-scope) ─────────────────────
    // MMPI (E.1, E.2) dikecualikan — ditangani terpisah di PsychologicalTest

    /**
     * Kode tes yang secara eksplisit di-skip saat import.
     */
    public const EXCLUDED_TEST_CODES = ['E.1', 'E.2'];

    /**
     * Mapping kode tes → kategori, untuk normalisasi data API.
     */
    public const TEST_CATEGORIES = [
        'A.1'   => 'Kecerdasan / IQ',
        'A.2'   => 'Kecerdasan / IQ',
        'A.5'   => 'Kecerdasan / IQ',
        'B.1'   => 'Kepribadian / Karakter',
        'B.2'   => 'Kepribadian / Psikometri',
        'D.2'   => 'Sikap Kerja',
        'F.1'   => 'Kecerdasan Emosional (EQ)',
        'G.1'   => 'Kecenderungan Perilaku',
        'H.1'   => 'Minat Jabatan',
    ];

    // ─── Relationships ─────────────────────────────────────────

    public function participant(): BelongsTo
    {
        return $this->belongsTo(Participant::class);
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(AssessmentEvent::class, 'event_id');
    }

    // ─── Scopes ────────────────────────────────────────────────

    public function scopeByTestCode(Builder $query, string $testCode): Builder
    {
        return $query->where('test_code', $testCode);
    }

    public function scopeByCategory(Builder $query, string $category): Builder
    {
        return $query->where('test_category', $category);
    }

    public function scopeForEvent(Builder $query, int $eventId): Builder
    {
        return $query->where('event_id', $eventId);
    }

    public function scopePendingConversion(Builder $query): Builder
    {
        return $query->where('conversion_status', 'pending');
    }

    public function scopeConverted(Builder $query): Builder
    {
        return $query->where('conversion_status', 'converted');
    }

    // ─── Helpers ───────────────────────────────────────────────

    /**
     * Cek apakah kode tes ini dikecualikan dari import.
     */
    public static function isExcluded(string $testCode): bool
    {
        return in_array($testCode, self::EXCLUDED_TEST_CODES, true);
    }

    /**
     * Ambil kategori berdasarkan kode tes.
     * Fallback ke 'Lainnya' jika kode tidak dikenal.
     */
    public static function getCategoryForCode(string $testCode): string
    {
        return self::TEST_CATEGORIES[$testCode] ?? 'Lainnya';
    }

    /**
     * Tandai record ini sebagai sudah dikonversi ke rating SPSP.
     */
    public function markAsConverted(): void
    {
        $this->update([
            'conversion_status' => 'converted',
            'converted_at' => now(),
        ]);
    }

    /**
     * Tandai record ini sebagai di-skip (tidak perlu konversi).
     */
    public function markAsSkipped(): void
    {
        $this->update([
            'conversion_status' => 'skipped',
        ]);
    }
}
