<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Mmpi extends Model
{
    use HasFactory;

    protected $table = 'mmpi';

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

    /**
     * Helper internal untuk mem-parsing psikogram array atau string JSON.
     *
     * @return array<string, mixed>|null
     */
    private function getDecodedPsikogram(): ?array
    {
        $value = $this->psikogram;
        if (is_string($value)) {
            $decoded = json_decode($value, true);
            if (is_array($decoded)) {
                return $decoded;
            }
        }

        return is_array($value) ? $value : null;
    }

    /**
     * Dapatkan skala validitas terstruktur (L, F, K, VRIN, TRIN).
     *
     * @return array<string, int|float>
     */
    public function getValidityScalesAttribute(): array
    {
        $psi = $this->getDecodedPsikogram();
        if (is_array($psi) && isset($psi['skala_validitas']) && is_array($psi['skala_validitas'])) {
            return $psi['skala_validitas'];
        }

        return [];
    }

    /**
     * Dapatkan skala klinis terstruktur (Hs, D, Hy, Pd, Pa, Pt, Sc, Ma, Si atau RC1..RC9).
     *
     * @return array<string, int|float>
     */
    public function getClinicalScalesAttribute(): array
    {
        $psi = $this->getDecodedPsikogram();
        if (is_array($psi) && isset($psi['skala_klinis']) && is_array($psi['skala_klinis'])) {
            return $psi['skala_klinis'];
        }

        return [];
    }

    /**
     * Dapatkan skala suplementer terstruktur (Es, A, WRK, TPA).
     *
     * @return array<string, int|float>
     */
    public function getSupplementaryScalesAttribute(): array
    {
        $psi = $this->getDecodedPsikogram();
        if (is_array($psi) && isset($psi['skala_suplementer']) && is_array($psi['skala_suplementer'])) {
            return $psi['skala_suplementer'];
        }

        return [];
    }

    /**
     * Dapatkan skala konten terstruktur (ANX, FRS, OBS, DEP, HEA, BIZ, ANG, CYN, ASP, TPA, LSE, SOD, FAM, WRK, TRT).
     *
     * @return array<string, int|float>
     */
    public function getContentScalesAttribute(): array
    {
        $psi = $this->getDecodedPsikogram();
        if (is_array($psi) && isset($psi['skala_konten']) && is_array($psi['skala_konten'])) {
            return $psi['skala_konten'];
        }

        return [];
    }

    /**
     * Dapatkan skala masalah spesifik terstruktur (E.1 / RF).
     *
     * @return array<string, int|float>
     */
    public function getSpecificProblemScalesAttribute(): array
    {
        $psi = $this->getDecodedPsikogram();
        if (is_array($psi) && isset($psi['skala_masalah_spesifik']) && is_array($psi['skala_masalah_spesifik'])) {
            return $psi['skala_masalah_spesifik'];
        }

        return [];
    }

    /**
     * Dapatkan map seluruh skor T-score dari semua skala lengkap.
     *
     * @return array<string, int|float>
     */
    public function getAllScalesAttribute(): array
    {
        $psi = $this->getDecodedPsikogram();
        if (is_array($psi) && isset($psi['semua_skala']) && is_array($psi['semua_skala'])) {
            return $psi['semua_skala'];
        }

        return array_merge(
            $this->validity_scales,
            $this->clinical_scales,
            $this->content_scales,
            $this->supplementary_scales,
            $this->specific_problem_scales
        );
    }

    /**
     * Dapatkan daftar skala dengan elevasi klinis (T-score >= 65).
     *
     * @return array<int, string>
     */
    public function getElevatedScalesAttribute(): array
    {
        $psi = $this->getDecodedPsikogram();
        if (is_array($psi) && isset($psi['elevated_scales']) && is_array($psi['elevated_scales'])) {
            return $psi['elevated_scales'];
        }

        $elevated = [];
        foreach ($this->all_scales as $code => $score) {
            if ($score >= 65) {
                $elevated[] = (string) $code;
            }
        }

        return array_values(array_unique($elevated));
    }

    /**
     * Hitung skor well-being pada skala 1.00 - 5.00 dari nilai_pq.
     */
    public function getWellbeingScoreAttribute(): float
    {
        $pq = (float) ($this->nilai_pq ?? 80.0);

        return round(max(1.0, min(5.0, $pq / 20.0)), 2);
    }

    /**
     * Kategori interpretasi well-being.
     */
    public function getWellbeingCategoryAttribute(): string
    {
        $score = $this->wellbeing_score;

        return match (true) {
            $score >= 4.20 => 'Sangat Baik / Prima',
            $score >= 3.50 => 'Baik / Sehat',
            $score >= 2.50 => 'Cukup / Perlu Perhatian',
            default => 'Kurang / Berisiko',
        };
    }

    public function getPsikogramFormattedAttribute(): string
    {
        $value = $this->psikogram;
        if (is_array($value)) {
            // Jika format terstruktur baru
            if (isset($value['skala_klinis']) || isset($value['skala_validitas'])) {
                $lines = [];
                if (! empty($value['skala_validitas'])) {
                    $vStr = collect($value['skala_validitas'])->map(fn ($v, $k) => "{$k}: {$v}")->implode(', ');
                    $lines[] = "Validitas: {$vStr}";
                }
                if (! empty($value['skala_klinis'])) {
                    $cStr = collect($value['skala_klinis'])->map(fn ($v, $k) => "{$k}: {$v}")->implode(', ');
                    $lines[] = "Klinis: {$cStr}";
                }

                return implode("\n", $lines);
            }

            $parts = [];
            foreach ($value as $k => $v) {
                if (is_numeric($k)) {
                    $parts[] = $v;
                } else {
                    $parts[] = "{$k}: {$v}";
                }
            }

            return implode("\n", $parts);
        }

        return (string) ($value ?? '-');
    }
}
