<?php

declare(strict_types=1);

namespace Database\Seeders\Support;

use App\Models\AssessmentEvent;
use App\Models\Participant;
use App\Models\TestResult;
use DateTimeInterface;

/**
 * TestResultGenerator - Generator Riwayat Hasil Tes Psikometri Otentik
 *
 * Menghasilkan riwayat alat tes (IST, CFIT, 16PF, PAPI Kostik, Kraepelin, EQ, Behavior, RMIB)
 * yang selaras dengan level performa peserta (Nine-Box) dan skema API online / legacy DB.
 */
class TestResultGenerator
{
    /**
     * Format assessment_date safely into standard MySQL datetime string: 'Y-m-d H:i:s'.
     */
    private static function formatTestDateTime(mixed $date, string $defaultTime): string
    {
        if (empty($date)) {
            return now()->format('Y-m-d').' '.$defaultTime;
        }

        if ($date instanceof DateTimeInterface) {
            return $date->format('Y-m-d').' '.$defaultTime;
        }

        $dateStr = substr((string) $date, 0, 10);

        return $dateStr.' '.$defaultTime;
    }

    /**
     * Generate seluruh data test_results untuk 1 peserta.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function generateForParticipant(
        Participant $participant,
        AssessmentEvent $event,
        string $boxCategory
    ): array {
        $now = now();
        $isLegacy = $event->code === 'PR-A-313' || str_starts_with($event->code, 'PR-A-30') || str_starts_with($event->code, 'PR-A-31');

        $performanceLevel = match ($boxCategory) {
            'K-7', 'K-8', 'K-9' => 'high',
            'K-1', 'K-3' => 'low',
            default => 'medium',
        };

        if ($isLegacy) {
            return self::generateLegacyTestResults($participant, $event, $performanceLevel, $now);
        }

        return self::generateApiOnlineTestResults($participant, $event, $performanceLevel, $now);
    }

    /**
     * Generate riwayat tes untuk Proyek Legacy (Jalur A - DB LSP).
     */
    private static function generateLegacyTestResults(
        Participant $participant,
        AssessmentEvent $event,
        string $performanceLevel,
        $now
    ): array {
        $source = 'lsp_db';
        $results = [];

        // 1. IST (A.5)
        [$iq, $kategoriIq] = match ($performanceLevel) {
            'high' => [fake()->numberBetween(115, 128), 'Rata-rata Atas'],
            'medium' => [fake()->numberBetween(96, 114), 'Rata-rata'],
            'low' => [fake()->numberBetween(82, 95), 'Rata-rata Bawah'],
        };
        $swBase = (int) round($iq / 10);
        $istTime = self::formatTestDateTime($participant->assessment_date, '08:30:00');
        $results[] = [
            'participant_id' => $participant->id,
            'event_id' => $event->id,
            'test_code' => 'A.5',
            'test_name' => 'Typical IST',
            'test_category' => TestResult::getCategoryForCode('A.5'),
            'status' => 'completed',
            'source' => $source,
            'test_started_at' => $istTime,
            'summary_data' => json_encode([
                'iq' => $iq,
                'kategori' => $kategoriIq,
                'hasil_kategori' => ['IQ' => $kategoriIq],
                'label_values' => [
                    'SE' => $swBase + fake()->numberBetween(-1, 2),
                    'WA' => $swBase + fake()->numberBetween(-1, 2),
                    'AN' => $swBase + fake()->numberBetween(-2, 1),
                    'GE' => $swBase + fake()->numberBetween(-1, 1),
                    'ME' => $swBase + fake()->numberBetween(0, 3),
                    'RA' => $swBase + fake()->numberBetween(-2, 1),
                    'ZR' => $swBase + fake()->numberBetween(-1, 2),
                    'FA' => $swBase + fake()->numberBetween(-1, 1),
                    'WU' => $swBase + fake()->numberBetween(-2, 1),
                ],
                'rs' => (int) round($iq * 0.75),
            ]),
            'interpretation_data' => null,
            'raw_response' => json_encode(['raw' => '9,12,7,7,5,4,8,3,16']),
            'conversion_status' => 'converted',
            'converted_at' => $now,
            'created_at' => $now,
            'updated_at' => $now,
        ];

        // 2. PAPI Kostik (D.1)
        $kBase = match ($performanceLevel) {
            'high' => 6,
            'medium' => 4,
            'low' => 2,
        };
        $kostikFactors = [
            'G' => max(1, min(9, $kBase + fake()->numberBetween(-1, 2))),
            'L' => max(1, min(9, $kBase + fake()->numberBetween(-1, 2))),
            'I' => max(1, min(9, $kBase + fake()->numberBetween(-2, 1))),
            'T' => max(1, min(9, $kBase + fake()->numberBetween(-1, 2))),
            'V' => max(1, min(9, $kBase + fake()->numberBetween(-1, 1))),
            'S' => max(1, min(9, $kBase + fake()->numberBetween(0, 2))),
            'R' => max(1, min(9, $kBase + fake()->numberBetween(-2, 1))),
            'D' => max(1, min(9, $kBase + fake()->numberBetween(-1, 2))),
            'C' => max(1, min(9, $kBase + fake()->numberBetween(-1, 1))),
            'E' => max(1, min(9, $kBase + fake()->numberBetween(-1, 2))),
            'N' => max(1, min(9, $kBase + fake()->numberBetween(-1, 1))),
            'A' => max(1, min(9, $kBase + fake()->numberBetween(0, 2))),
            'P' => max(1, min(9, $kBase + fake()->numberBetween(-1, 2))),
            'X' => max(1, min(9, $kBase + fake()->numberBetween(-1, 1))),
            'B' => max(1, min(9, $kBase + fake()->numberBetween(-2, 1))),
            'O' => max(1, min(9, $kBase + fake()->numberBetween(0, 2))),
            'Z' => max(1, min(9, $kBase + fake()->numberBetween(-1, 1))),
            'K' => max(1, min(9, $kBase + fake()->numberBetween(-1, 2))),
            'F' => max(1, min(9, $kBase + fake()->numberBetween(-2, 1))),
            'W' => max(1, min(9, $kBase + fake()->numberBetween(0, 2))),
        ];
        $papiTime = self::formatTestDateTime($participant->assessment_date, '10:00:00');
        $results[] = [
            'participant_id' => $participant->id,
            'event_id' => $event->id,
            'test_code' => 'D.1',
            'test_name' => 'PAPI Kostik',
            'test_category' => TestResult::getCategoryForCode('B.1'),
            'status' => 'completed',
            'source' => $source,
            'test_started_at' => $papiTime,
            'summary_data' => json_encode([
                'nilaiAspek' => $kostikFactors,
                'labels_aspek' => [
                    'G' => 'Peran Pekerja Keras', 'L' => 'Peran Kepemimpinan', 'I' => 'Pengambilan Keputusan',
                    'T' => 'Tipe Sibuk', 'V' => 'Penuh Semangat', 'S' => 'Hubungan Sosial',
                ],
            ]),
            'interpretation_data' => null,
            'raw_response' => json_encode(['raw' => implode(',', array_values($kostikFactors))]),
            'conversion_status' => 'converted',
            'converted_at' => $now,
            'created_at' => $now,
            'updated_at' => $now,
        ];

        // 3. 16PF (B.2)
        $stenBase = match ($performanceLevel) {
            'high' => 7,
            'medium' => 5,
            'low' => 4,
        };
        $sten16pf = [
            'A' => max(1, min(10, $stenBase + fake()->numberBetween(-1, 2))),
            'B' => max(1, min(10, $stenBase + fake()->numberBetween(-1, 1))),
            'C' => max(1, min(10, $stenBase + fake()->numberBetween(0, 2))),
            'E' => max(1, min(10, $stenBase + fake()->numberBetween(-1, 1))),
            'F' => max(1, min(10, $stenBase + fake()->numberBetween(-2, 1))),
            'G' => max(1, min(10, $stenBase + fake()->numberBetween(0, 2))),
            'H' => max(1, min(10, $stenBase + fake()->numberBetween(-1, 1))),
            'I' => max(1, min(10, $stenBase + fake()->numberBetween(-1, 2))),
            'L' => max(1, min(10, $stenBase + fake()->numberBetween(-2, 1))),
            'M' => max(1, min(10, $stenBase + fake()->numberBetween(-1, 1))),
            'N' => max(1, min(10, $stenBase + fake()->numberBetween(0, 2))),
            'O' => max(1, min(10, $stenBase + fake()->numberBetween(-1, 1))),
            'Q1' => max(1, min(10, $stenBase + fake()->numberBetween(-1, 2))),
            'Q2' => max(1, min(10, $stenBase + fake()->numberBetween(-1, 1))),
            'Q3' => max(1, min(10, $stenBase + fake()->numberBetween(0, 2))),
            'Q4' => max(1, min(10, $stenBase + fake()->numberBetween(-2, 1))),
        ];
        $pfTime = self::formatTestDateTime($participant->assessment_date, '11:15:00');
        $results[] = [
            'participant_id' => $participant->id,
            'event_id' => $event->id,
            'test_code' => 'B.2',
            'test_name' => 'Typical 16PF',
            'test_category' => TestResult::getCategoryForCode('B.2'),
            'status' => 'completed',
            'source' => $source,
            'test_started_at' => $pfTime,
            'summary_data' => json_encode([
                'standart_final' => $sten16pf,
                'MDStenScore' => fake()->numberBetween(5, 7),
                'nilaiAspek' => $sten16pf,
            ]),
            'interpretation_data' => null,
            'raw_response' => json_encode(['raw' => implode(',', array_values($sten16pf))]),
            'conversion_status' => 'converted',
            'converted_at' => $now,
            'created_at' => $now,
            'updated_at' => $now,
        ];

        return $results;
    }

    /**
     * Generate riwayat tes untuk Proyek REST API Online (Jalur B ≥ PR-A-338).
     */
    private static function generateApiOnlineTestResults(
        Participant $participant,
        AssessmentEvent $event,
        string $performanceLevel,
        $now
    ): array {
        $source = 'api';
        $results = [];

        // 1. CFIT 3A (A.1)
        [$iq, $kategoriIq] = match ($performanceLevel) {
            'high' => [fake()->numberBetween(115, 130), 'Istimewa'],
            'medium' => [fake()->numberBetween(95, 114), 'Rata-rata'],
            'low' => [fake()->numberBetween(70, 94), 'Borderline'],
        };
        $sub1Rating = match ($performanceLevel) {
            'high' => 4, 'medium' => 3, 'low' => 2
        };
        $sub2Rating = match ($performanceLevel) {
            'high' => 4, 'medium' => 3, 'low' => 1
        };
        $sub3Rating = match ($performanceLevel) {
            'high' => 5, 'medium' => 3, 'low' => 2
        };
        $sub4Rating = match ($performanceLevel) {
            'high' => 4, 'medium' => 2, 'low' => 1
        };

        $cfitTime = self::formatTestDateTime($participant->assessment_date, '08:15:00');
        $results[] = [
            'participant_id' => $participant->id,
            'event_id' => $event->id,
            'test_code' => 'A.1',
            'test_name' => 'Typical CFIT3A',
            'test_category' => TestResult::getCategoryForCode('A.1'),
            'status' => 'completed',
            'source' => $source,
            'test_started_at' => $cfitTime,
            'summary_data' => json_encode([
                'status' => true,
                'mulai_tes' => $cfitTime,
                'total' => (int) round($iq / 6),
                'iq' => $iq,
                'kategori' => $kategoriIq,
                'umur_format' => '24_0',
                'index_kecerdasan_umum' => $sub1Rating,
                'hasil_sub' => [
                    'sub1' => ['nilai' => $sub1Rating * 3, 'total_soal' => 13, 'persentase' => round(($sub1Rating / 5) * 100, 1), 'rating' => $sub1Rating, 'deskripsi' => $sub1Rating >= 4 ? 'Baik' : ($sub1Rating >= 3 ? 'Cukup' : 'Kurang')],
                    'sub2' => ['nilai' => $sub2Rating * 3, 'total_soal' => 14, 'persentase' => round(($sub2Rating / 5) * 100, 1), 'rating' => $sub2Rating, 'deskripsi' => $sub2Rating >= 4 ? 'Baik' : ($sub2Rating >= 3 ? 'Cukup' : 'Kurang')],
                    'sub3' => ['nilai' => $sub3Rating * 2, 'total_soal' => 13, 'persentase' => round(($sub3Rating / 5) * 100, 1), 'rating' => $sub3Rating, 'deskripsi' => $sub3Rating >= 4 ? 'Baik' : ($sub3Rating >= 3 ? 'Cukup' : 'Kurang')],
                    'sub4' => ['nilai' => $sub4Rating * 2, 'total_soal' => 10, 'persentase' => round(($sub4Rating / 5) * 100, 1), 'rating' => $sub4Rating, 'deskripsi' => $sub4Rating >= 4 ? 'Baik' : ($sub4Rating >= 3 ? 'Cukup' : 'Kurang')],
                ],
                'INTERPRETASI_HASIL' => [
                    'Kecerdasan Umum' => $kategoriIq === 'Istimewa' ? 'Memiliki daya tangkap yang sangat cepat dan pemikiran abstrak yang tajam.' : 'Memiliki kemampuan intelektual yang cukup memadai dalam menyelesaikan tugas kerja umum.',
                    'Series' => 'Mampu memahami alur dan keterkaitan informasi secara sistematis.',
                    'Classification' => 'Mampu membedakan elemen dan klasifikasi pola dengan teliti.',
                ],
                'SARAN_PENGEMBANGAN' => [
                    'Tingkatkan pemecahan masalah dengan simulasi studi kasus kompleks.',
                    'Biasakan menyusun alternatif solusi sebelum mengambil keputusan strategis.',
                ],
                'nama_alat_tes' => 'Typical CFIT3A',
            ]),
            'interpretation_data' => null,
            'raw_response' => json_encode(['status' => true, 'iq' => $iq]),
            'conversion_status' => 'converted',
            'converted_at' => $now,
            'created_at' => $now,
            'updated_at' => $now,
        ];

        // 2. KOMPETENSI KARAKTER (B.1)
        $fVal = match ($performanceLevel) {
            'high' => 4, 'medium' => 3, 'low' => 2
        };
        $kostikData = [
            'status' => true,
            'hasil_A' => max(1, min(5, $fVal + fake()->numberBetween(-1, 1))),
            'hasil_G' => max(1, min(5, $fVal + fake()->numberBetween(0, 1))),
            'hasil_N' => max(1, min(5, $fVal + fake()->numberBetween(-1, 1))),
            'hasil_R' => max(1, min(5, $fVal + fake()->numberBetween(-1, 1))),
            'hasil_C' => max(1, min(5, $fVal + fake()->numberBetween(0, 1))),
            'hasil_D' => max(1, min(5, $fVal + fake()->numberBetween(-1, 1))),
            'hasil_T' => max(1, min(5, $fVal + fake()->numberBetween(0, 1))),
            'hasil_V' => max(1, min(5, $fVal + fake()->numberBetween(-1, 1))),
            'hasil_F' => max(1, min(5, $fVal + fake()->numberBetween(-1, 1))),
            'hasil_W' => max(1, min(5, $fVal + fake()->numberBetween(0, 1))),
            'hasil_L' => max(1, min(5, $fVal + fake()->numberBetween(-1, 1))),
            'hasil_P' => max(1, min(5, $fVal + fake()->numberBetween(0, 1))),
            'hasil_I' => max(1, min(5, $fVal + fake()->numberBetween(-1, 1))),
            'hasil_S' => max(1, min(5, $fVal + fake()->numberBetween(0, 1))),
            'hasil_O' => max(1, min(5, $fVal + fake()->numberBetween(-1, 1))),
            'hasil_B' => max(1, min(5, $fVal + fake()->numberBetween(-1, 1))),
            'hasil_X' => max(1, min(5, $fVal + fake()->numberBetween(0, 1))),
            'hasil_E' => max(1, min(5, $fVal + fake()->numberBetween(-1, 1))),
            'hasil_K' => max(1, min(5, $fVal + fake()->numberBetween(0, 1))),
            'hasil_Z' => max(1, min(5, $fVal + fake()->numberBetween(-1, 1))),
            'arah_kerja_1' => 'Menunjukkan semangat kerja dan orientasi penyelesaian target yang konsisten.',
            'gaya_kerja_1' => 'Menyeimbangkan aspek praktikal dan keteraturan kerja dengan baik.',
            'leadership_1' => 'Mampu mengoordinasikan tim dan membagi tugas secara proporsional.',
            'social_1' => 'Menjalin hubungan interpersonal yang positif dan kooperatif dengan rekan kerja.',
            'temprament_1' => 'Mampu mengendalikan emosi dengan stabil di bawah tekanan kerja.',
            'labels_aspek' => [
                'hasil_A' => 'Prestasi', 'hasil_G' => 'Kerja Keras', 'hasil_N' => 'Ketekunan',
                'hasil_L' => 'Kepemimpinan', 'hasil_P' => 'Mengontrol Orang', 'hasil_I' => 'Keputusan',
            ],
            'nama_alat_tes' => 'KOMPETENSI KARAKTER',
        ];
        $papiApiTime = self::formatTestDateTime($participant->assessment_date, '09:45:00');
        $results[] = [
            'participant_id' => $participant->id,
            'event_id' => $event->id,
            'test_code' => 'B.1',
            'test_name' => 'KOMPETENSI KARAKTER',
            'test_category' => TestResult::getCategoryForCode('B.1'),
            'status' => 'completed',
            'source' => $source,
            'test_started_at' => $papiApiTime,
            'summary_data' => json_encode($kostikData),
            'interpretation_data' => null,
            'raw_response' => json_encode(['status' => true]),
            'conversion_status' => 'converted',
            'converted_at' => $now,
            'created_at' => $now,
            'updated_at' => $now,
        ];

        // 3. 16PF (B.2)
        $stenBase = match ($performanceLevel) {
            'high' => 7, 'medium' => 5, 'low' => 4
        };
        $sten16pf = [
            'A' => max(1, min(10, $stenBase + fake()->numberBetween(-1, 2))),
            'B' => max(1, min(10, $stenBase + fake()->numberBetween(-1, 1))),
            'C' => max(1, min(10, $stenBase + fake()->numberBetween(0, 2))),
            'E' => max(1, min(10, $stenBase + fake()->numberBetween(-1, 1))),
            'F' => max(1, min(10, $stenBase + fake()->numberBetween(-2, 1))),
            'G' => max(1, min(10, $stenBase + fake()->numberBetween(0, 2))),
            'H' => max(1, min(10, $stenBase + fake()->numberBetween(-1, 1))),
            'I' => max(1, min(10, $stenBase + fake()->numberBetween(-1, 2))),
            'L' => max(1, min(10, $stenBase + fake()->numberBetween(-2, 1))),
            'M' => max(1, min(10, $stenBase + fake()->numberBetween(-1, 1))),
            'N' => max(1, min(10, $stenBase + fake()->numberBetween(0, 2))),
            'O' => max(1, min(10, $stenBase + fake()->numberBetween(-1, 1))),
            'Q1' => max(1, min(10, $stenBase + fake()->numberBetween(-1, 2))),
            'Q2' => max(1, min(10, $stenBase + fake()->numberBetween(-1, 1))),
            'Q3' => max(1, min(10, $stenBase + fake()->numberBetween(0, 2))),
            'Q4' => max(1, min(10, $stenBase + fake()->numberBetween(-2, 1))),
        ];
        $pfApiTime = self::formatTestDateTime($participant->assessment_date, '10:30:00');
        $results[] = [
            'participant_id' => $participant->id,
            'event_id' => $event->id,
            'test_code' => 'B.2',
            'test_name' => 'Typical 16PF',
            'test_category' => TestResult::getCategoryForCode('B.2'),
            'status' => 'completed',
            'source' => $source,
            'test_started_at' => $pfApiTime,
            'summary_data' => json_encode([
                'status' => true,
                'kode' => '16PF',
                'kategori' => 'Normal',
                'standart_final' => $sten16pf,
                'MDStenScore' => fake()->numberBetween(5, 7),
                'nilaiAspek' => $sten16pf,
                'nama_alat_tes' => 'Typical 16PF',
            ]),
            'interpretation_data' => null,
            'raw_response' => json_encode(['status' => true]),
            'conversion_status' => 'converted',
            'converted_at' => $now,
            'created_at' => $now,
            'updated_at' => $now,
        ];

        // 4. Kraepelin (D.2)
        [$panker, $janker, $hanker, $tianker] = match ($performanceLevel) {
            'high' => [8.2, 1.4, 3.8, 68],
            'medium' => [6.5, 2.5, 3.0, 55],
            'low' => [4.2, 4.8, 1.8, 38],
        };
        $kRating = match ($performanceLevel) {
            'high' => 4, 'medium' => 3, 'low' => 2
        };
        $kraeplinTime = self::formatTestDateTime($participant->assessment_date, '11:15:00');
        $results[] = [
            'participant_id' => $participant->id,
            'event_id' => $event->id,
            'test_code' => 'D.2',
            'test_name' => 'Typical Kraeplin',
            'test_category' => TestResult::getCategoryForCode('D.2'),
            'status' => 'completed',
            'source' => $source,
            'test_started_at' => $kraeplinTime,
            'summary_data' => json_encode([
                'status' => true,
                'mulai_tes' => $kraeplinTime,
                'pendidikan' => $participant->pendidikan ?? 'S1',
                'kesimpulan' => [
                    'panker' => $panker,
                    'janker_average' => $janker,
                    'janker_range' => fake()->numberBetween(4, 8),
                    'hanker' => $hanker,
                    'tianker' => $tianker,
                ],
                'kesimpulan_akhir' => [
                    'panker' => $kRating,
                    'janker_average' => $kRating,
                    'janker_range' => 3,
                    'hanker' => $kRating,
                    'tianker' => $kRating,
                ],
                'nama_alat_tes' => 'Typical Kraeplin',
            ]),
            'interpretation_data' => null,
            'raw_response' => json_encode(['status' => true]),
            'conversion_status' => 'converted',
            'converted_at' => $now,
            'created_at' => $now,
            'updated_at' => $now,
        ];

        // 5. Typical EQ (F.1)
        [$eqScore, $eqCat] = match ($performanceLevel) {
            'high' => [fake()->numberBetween(340, 375), 'Istimewa'],
            'medium' => [fake()->numberBetween(290, 339), 'Tinggi'],
            'low' => [fake()->numberBetween(220, 289), 'Cukup'],
        };
        $eqTime = self::formatTestDateTime($participant->assessment_date, '13:00:00');
        $results[] = [
            'participant_id' => $participant->id,
            'event_id' => $event->id,
            'test_code' => 'F.1',
            'test_name' => 'Typical EQ',
            'test_category' => TestResult::getCategoryForCode('F.1'),
            'status' => 'completed',
            'source' => $source,
            'test_started_at' => $eqTime,
            'summary_data' => json_encode([
                'status' => true,
                'skor_akhir' => $eqScore,
                'kategori' => $eqCat,
                'dimensi' => [
                    '4' => ['nama' => 'Kesadaran Emosi Diri', 'skor' => (int) round($eqScore / 13)],
                    '5' => ['nama' => 'Pengungkapan Emosi', 'skor' => (int) round($eqScore / 14)],
                    '6' => ['nama' => 'Kesadaran Emosi Orang Lain', 'skor' => (int) round($eqScore / 13)],
                    '7' => ['nama' => 'Keluwesan', 'skor' => (int) round($eqScore / 12)],
                    '8' => ['nama' => 'Kemandirian', 'skor' => (int) round($eqScore / 14)],
                    '9' => ['nama' => 'Penghargaan Diri', 'skor' => (int) round($eqScore / 13)],
                    '10' => ['nama' => 'Hubungan Antar Pribadi', 'skor' => (int) round($eqScore / 12)],
                    '11' => ['nama' => 'Tanggung Jawab Sosial', 'skor' => (int) round($eqScore / 14)],
                    '12' => ['nama' => 'Penyelesaian Masalah', 'skor' => (int) round($eqScore / 12)],
                    '13' => ['nama' => 'Uji Realitas', 'skor' => (int) round($eqScore / 11)],
                    '14' => ['nama' => 'Pengendalian Dorongan Hati', 'skor' => (int) round($eqScore / 15)],
                    '15' => ['nama' => 'Ketahanan Terhadap Stres', 'skor' => (int) round($eqScore / 13)],
                    '16' => ['nama' => 'Daya Pribadi', 'skor' => (int) round($eqScore / 12)],
                    '17' => ['nama' => 'Integritas', 'skor' => (int) round($eqScore / 15)],
                ],
                'hasil_akhir' => [
                    '4' => 3, '5' => 3, '6' => 3, '7' => 4, '8' => 3, '9' => 3, '10' => 3,
                    '11' => 3, '12' => 4, '13' => 3, '14' => 2, '15' => 3, '16' => 3, '17' => 3,
                ],
                'nama_alat_tes' => 'Typical EQ',
            ]),
            'interpretation_data' => null,
            'raw_response' => json_encode(['status' => true]),
            'conversion_status' => 'converted',
            'converted_at' => $now,
            'created_at' => $now,
            'updated_at' => $now,
        ];

        // 6. Behavior Tendencies (G.1)
        $tipeBehavior = fake()->randomElement(['ILMUWAN', 'PEMIMPIN', 'PENGAYOM', 'PELAKSANA']);
        $behaviorTime = self::formatTestDateTime($participant->assessment_date, '14:00:00');
        $results[] = [
            'participant_id' => $participant->id,
            'event_id' => $event->id,
            'test_code' => 'G.1',
            'test_name' => 'Typical Behavior Tendencies',
            'test_category' => TestResult::getCategoryForCode('G.1'),
            'status' => 'completed',
            'source' => $source,
            'test_started_at' => $behaviorTime,
            'summary_data' => json_encode([
                'status' => true,
                'iman' => fake()->numberBetween(15, 25),
                'pikiran' => fake()->numberBetween(25, 45),
                'perasaan' => fake()->numberBetween(15, 25),
                'hasil_kecenderungan' => $tipeBehavior,
                'interpretasi_kebiasaan' => 'Kecenderungan perilaku bertipe <strong>'.$tipeBehavior.'</strong> ditandai dengan pola pikir logis, analitis, dan kemampuan beradaptasi secara objektif terhadap tuntutan lingkungan kerja.',
                'nama_alat_tes' => 'Typical Behavior Tendencies',
            ]),
            'interpretation_data' => null,
            'raw_response' => json_encode(['status' => true]),
            'conversion_status' => 'converted',
            'converted_at' => $now,
            'created_at' => $now,
            'updated_at' => $now,
        ];

        // 7. RMIB (H.1)
        $minatPool = ['Clerical', 'Scientific', 'Computational', 'Executive', 'Social Service', 'Persuasive', 'Literary', 'Musical', 'Practical', 'Medical'];
        $selectedMinat = fake()->randomElements($minatPool, 3);
        $rmibTime = self::formatTestDateTime($participant->assessment_date, '14:45:00');
        $results[] = [
            'participant_id' => $participant->id,
            'event_id' => $event->id,
            'test_code' => 'H.1',
            'test_name' => 'RMIB',
            'test_category' => TestResult::getCategoryForCode('H.1'),
            'status' => 'completed',
            'source' => $source,
            'test_started_at' => $rmibTime,
            'summary_data' => json_encode([
                'status' => true,
                'nilai_1' => $selectedMinat[0],
                'nilai_2' => $selectedMinat[1],
                'nilai_3' => $selectedMinat[2],
                'nilai' => '10,8,3',
                'nama_alat_tes' => 'RMIB',
            ]),
            'interpretation_data' => null,
            'raw_response' => json_encode(['status' => true]),
            'conversion_status' => 'converted',
            'converted_at' => $now,
            'created_at' => $now,
            'updated_at' => $now,
        ];

        return $results;
    }
}
