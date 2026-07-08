<?php

namespace App\Services;

use App\Models\TestResult;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TestResultImportService
{
    /**
     * Hasil import untuk tracking/reporting.
     */
    protected int $imported = 0;
    protected int $skipped = 0;
    protected int $failed = 0;
    protected array $errors = [];

    /**
     * Import semua tes milik satu peserta dari response API Quantum.
     *
     * @param  int    $participantId  ID peserta di tabel `participants`
     * @param  int    $eventId        ID event di tabel `assessment_events`
     * @param  array  $tesData        Data tes dari API: ["A.1" => {...}, "B.2" => {...}, ...]
     * @return array  Ringkasan hasil import
     */
    public function importParticipantTests(int $participantId, int $eventId, array $tesData): array
    {
        $this->resetCounters();

        foreach ($tesData as $testCode => $testData) {
            try {
                $this->importSingleTest($participantId, $eventId, $testCode, $testData);
            } catch (\Throwable $e) {
                $this->failed++;
                $this->errors[] = [
                    'test_code' => $testCode,
                    'message' => $e->getMessage(),
                ];

                Log::warning("TestResultImport: Failed to import {$testCode} for participant {$participantId}", [
                    'error' => $e->getMessage(),
                    'participant_id' => $participantId,
                    'event_id' => $eventId,
                ]);
            }
        }

        return $this->getSummary();
    }

    /**
     * Import satu alat tes untuk satu peserta.
     */
    protected function importSingleTest(int $participantId, int $eventId, string $testCode, array $testData): void
    {
        // Skip alat tes yang dikecualikan (MMPI)
        if (TestResult::isExcluded($testCode)) {
            $this->skipped++;
            return;
        }

        // Skip jika data kosong (hanya berisi nama_alat_tes / status saja)
        if ($this->isDataEmpty($testData)) {
            $this->skipped++;
            return;
        }

        // Parse data sesuai jenis instrumen
        $parsed = $this->parseTestData($testCode, $testData);

        // Upsert ke database (idempotent)
        TestResult::updateOrCreate(
            [
                'participant_id' => $participantId,
                'event_id' => $eventId,
                'test_code' => $testCode,
            ],
            [
                'test_name' => $testData['nama_alat_tes'] ?? "Tidak diketahui ({$testCode})",
                'test_category' => TestResult::getCategoryForCode($testCode),
                'status' => ($testData['status'] ?? true) ? 'completed' : 'incomplete',
                'test_started_at' => $this->parseTimestamp($testData['mulai_tes'] ?? null),
                'summary_data' => $parsed['summary_data'],
                'interpretation_data' => $parsed['interpretation_data'],
                'raw_response' => $parsed['raw_response'],
                'conversion_status' => 'pending',
            ]
        );

        $this->imported++;
    }

    // ─── Per-Instrument Parsers ────────────────────────────────

    /**
     * Dispatch ke parser yang sesuai berdasarkan kode tes.
     */
    protected function parseTestData(string $testCode, array $testData): array
    {
        return match ($testCode) {
            'A.1', 'A.2' => $this->parseCFIT($testData),
            'A.5'        => $this->parseIST($testData),
            'B.1'        => $this->parseKompetensiKarakter($testData),
            'B.2'        => $this->parse16PF($testData),
            'D.2'        => $this->parseKraeplin($testData),
            'F.1'        => $this->parseEQ($testData),
            'G.1'        => $this->parseBehaviorTendencies($testData),
            'H.1'        => $this->parseRMIB($testData),
            default      => $this->parseGeneric($testData),
        };
    }

    /**
     * A.1 (CFIT 3A) & A.2 (CFIT 3B) — Tes Kecerdasan / IQ.
     *
     * Sub-tes sudah memiliki rating 1-5.
     * Fields: iq, kategori, hasil_sub (4 subtest dgn rating), index_kecerdasan_umum
     */
    protected function parseCFIT(array $data): array
    {
        return [
            'summary_data' => [
                'iq' => $data['iq'] ?? null,
                'kategori' => $data['kategori'] ?? null,
                'total' => $data['total'] ?? null,
                'index_kecerdasan_umum' => $data['index_kecerdasan_umum'] ?? null,
                'umur_format' => $data['umur_format'] ?? null,
                'index_umur' => $data['index_umur'] ?? null,
                'umur_asli' => $data['umur_asli'] ?? null,
                'versi' => $data['versi'] ?? null,
                'hasil_sub' => $data['hasil_sub'] ?? null,
            ],
            'interpretation_data' => $this->extractInterpretation($data),
            'raw_response' => $this->cleanRaw($data),
        ];
    }

    /**
     * A.5 (IST) — Intelligenz-Struktur-Test.
     *
     * Skala IQ (80-120+), 9 subtest, perlu konversi di tahap 2.
     * Fields: iq, rs, kategori, hasil_ist, label_values (SE/WA/AN/GE/ME/RA/ZR/FA/WU)
     */
    protected function parseIST(array $data): array
    {
        return [
            'summary_data' => [
                'iq' => $data['iq'] ?? null,
                'rs' => $data['rs'] ?? null,
                'index' => $data['index'] ?? null,
                'kategori' => $data['kategori'] ?? null,
                'hasil_kategori' => $data['hasil_kategori'] ?? null,
                'umur' => $data['umur'] ?? null,
                'hasil_ist' => $data['hasil_ist'] ?? null,
                'label_values' => $data['label_values'] ?? null,
            ],
            'interpretation_data' => null,
            'raw_response' => $this->cleanRaw($data),
        ];
    }

    /**
     * B.1 (Kompetensi Karakter) — Tes Kepribadian/Karakter.
     *
     * 20 aspek hasil_* sudah dalam skala 1-5. labels_aspek berisi mapping ke nama aspek.
     * Juga berisi narasi deskriptif per aspek (arah_kerja, gaya_kerja, dll).
     */
    protected function parseKompetensiKarakter(array $data): array
    {
        // Ekstrak semua field hasil_* (skor 1-5) + labels
        $hasilFields = [];
        $narasiFields = [];

        foreach ($data as $key => $value) {
            if (str_starts_with($key, 'hasil_')) {
                $hasilFields[$key] = $value;
            } elseif (in_array($key, [
                'arah_kerja_1', 'arah_kerja_2', 'arah_kerja_3',
                'gaya_kerja_1', 'gaya_kerja_2', 'gaya_kerja_3',
                'activity_1', 'activity_2',
                'followership_1', 'followership_2',
                'leadership_1', 'leadership_2', 'leadership_3',
                'social_1', 'social_2', 'social_3', 'social_4',
                'temprament_1', 'temprament_2', 'temprament_3',
            ])) {
                $narasiFields[$key] = $value;
            }
        }

        return [
            'summary_data' => [
                'hasil' => $hasilFields,
                'labels_aspek' => $data['labels_aspek'] ?? null,
            ],
            'interpretation_data' => !empty($narasiFields) ? $narasiFields : null,
            'raw_response' => $this->cleanRaw($data),
        ];
    }

    /**
     * B.2 (16PF) — Sixteen Personality Factor Questionnaire.
     *
     * 16 faktor kepribadian, skala sten 1-10. Perlu konversi ke 1-5 di tahap 2.
     * Fields: aspekList, nilaiAspek, standart_final, MDStenScore, deskripsi_aspek
     */
    protected function parse16PF(array $data): array
    {
        return [
            'summary_data' => [
                'kode' => $data['kode'] ?? null,
                'kategori' => $data['kategori'] ?? null,
                'aspekList' => $data['aspekList'] ?? null,
                'nilaiAspek' => $data['nilaiAspek'] ?? null,
                'standart_final' => $data['standart_final'] ?? null,
                'MDStenScore' => $data['MDStenScore'] ?? null,
                'nilai' => $data['nilai'] ?? null,
                'WS' => $data['WS'] ?? null,
            ],
            'interpretation_data' => isset($data['deskripsi_aspek']) ? [
                'deskripsi_aspek' => $data['deskripsi_aspek'],
            ] : null,
            'raw_response' => $this->cleanRaw($data),
        ];
    }

    /**
     * D.2 (Kraeplin) — Tes Sikap Kerja.
     *
     * PENTING: Field `detail` (per-soal, 200KB+) DIKECUALIKAN sesuai keputusan.
     * Hanya simpan kesimpulan dan statistik ringkas.
     */
    protected function parseKraeplin(array $data): array
    {
        $summary = [
            'kesimpulan' => $data['kesimpulan'] ?? null,
            'kesimpulan_akhir' => $data['kesimpulan_akhir'] ?? null,
            'pendidikan' => $data['pendidikan'] ?? null,
            'kesimpulan_SMA' => $data['kesimpulan_SMA'] ?? null,
            'kesimpulan_D3' => $data['kesimpulan_D3'] ?? null,
            'kesimpulan_S1' => $data['kesimpulan_S1'] ?? null,
        ];

        // Tambah statistik ringkas jika ada
        foreach (['skor_jalur', 'jumlah_X', 'jumlah_XX', 'jumlah_Y', 'jumlah_XY',
                   'MAX_benar', 'MIN_benar', 'jumlah_salah', 'jumlah_terloncati',
                   'skor_b', 'skor_a', 'skor_X45', 'skor_X0'] as $field) {
            if (isset($data[$field])) {
                $summary[$field] = $data[$field];
            }
        }

        // raw_response juga tanpa detail
        $rawWithoutDetail = $data;
        unset($rawWithoutDetail['detail']);

        return [
            'summary_data' => $summary,
            'interpretation_data' => null,
            'raw_response' => $this->cleanRaw($rawWithoutDetail),
        ];
    }

    /**
     * F.1 (EQ) — Tes Kecerdasan Emosional.
     *
     * 14 skala dengan skor dan nama. hasil_akhir sudah rating 1-4.
     * Fields: hasil_skala, hasil_akhir, skor_akhir, kategori
     */
    protected function parseEQ(array $data): array
    {
        return [
            'summary_data' => [
                'skor_akhir' => $data['skor_akhir'] ?? null,
                'kategori' => $data['kategori'] ?? null,
                'hasil_awal' => $data['hasil_awal'] ?? null,
                'hasil_akhir' => $data['hasil_akhir'] ?? null,
                'hasil_skala' => $data['hasil_skala'] ?? null,
            ],
            'interpretation_data' => null,
            'raw_response' => $this->cleanRaw($data, ['hasil_konversi']),
        ];
    }

    /**
     * G.1 (Behavior Tendencies) — Kecenderungan Perilaku.
     *
     * Skor absolut (iman, pikiran, perasaan) + hasil kecenderungan + narasi.
     */
    protected function parseBehaviorTendencies(array $data): array
    {
        return [
            'summary_data' => [
                'iman' => $data['iman'] ?? null,
                'pikiran' => $data['pikiran'] ?? null,
                'perasaan' => $data['perasaan'] ?? null,
                'hasil_kecenderungan' => $data['hasil_kecenderungan'] ?? null,
            ],
            'interpretation_data' => isset($data['interpretasi_kebiasaan']) ? [
                'interpretasi_kebiasaan' => $data['interpretasi_kebiasaan'],
            ] : null,
            'raw_response' => $this->cleanRaw($data),
        ];
    }

    /**
     * H.1 (RMIB) — Rothwell-Miller Interest Blank.
     *
     * Data kategoris (minat jabatan), bukan rating numerik.
     */
    protected function parseRMIB(array $data): array
    {
        return [
            'summary_data' => [
                'nilai_1' => $data['nilai_1'] ?? null,
                'nilai_2' => $data['nilai_2'] ?? null,
                'nilai_3' => $data['nilai_3'] ?? null,
                'nilai' => $data['nilai'] ?? null,
            ],
            'interpretation_data' => null,
            'raw_response' => $this->cleanRaw($data),
        ];
    }

    /**
     * Fallback parser untuk instrumen yang belum dikenali.
     * Simpan semua data apa adanya ke summary_data.
     */
    protected function parseGeneric(array $data): array
    {
        return [
            'summary_data' => $this->cleanRaw($data),
            'interpretation_data' => $this->extractInterpretation($data),
            'raw_response' => $this->cleanRaw($data),
        ];
    }

    // ─── Utility Methods ───────────────────────────────────────

    /**
     * Ekstrak field interpretasi & saran dari response API (jika ada).
     */
    protected function extractInterpretation(array $data): ?array
    {
        $interpretation = [];

        if (isset($data['INTERPRETASI_HASIL'])) {
            $interpretation['interpretasi_hasil'] = $data['INTERPRETASI_HASIL'];
        }

        if (isset($data['SARAN_PENGEMBANGAN'])) {
            $interpretation['saran_pengembangan'] = $data['SARAN_PENGEMBANGAN'];
        }

        return !empty($interpretation) ? $interpretation : null;
    }

    /**
     * Bersihkan response untuk penyimpanan raw.
     * Hapus field administratif yang tidak perlu disimpan 2x.
     *
     * @param  array    $data     Response data
     * @param  array    $exclude  Field tambahan yang perlu dikecualikan
     */
    protected function cleanRaw(array $data, array $exclude = []): array
    {
        $defaultExclude = ['nama_alat_tes', 'status'];
        $allExclude = array_merge($defaultExclude, $exclude);

        return array_diff_key($data, array_flip($allExclude));
    }

    /**
     * Cek apakah data tes kosong (hanya field administratif tanpa isi substansial).
     */
    protected function isDataEmpty(array $data): bool
    {
        $substantiveKeys = array_diff(array_keys($data), ['nama_alat_tes', 'status']);

        if (empty($substantiveKeys)) {
            return true;
        }

        foreach ($substantiveKeys as $key) {
            $value = $data[$key];
            if ($value !== null && $value !== '' && $value !== [] && $value !== (object) []) {
                return false;
            }
        }

        return true;
    }

    /**
     * Parse timestamp dari format API ke Carbon-compatible format.
     */
    protected function parseTimestamp(?string $timestamp): ?string
    {
        if (empty($timestamp)) {
            return null;
        }

        try {
            return \Carbon\Carbon::parse($timestamp)->toDateTimeString();
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * Reset counters sebelum import batch baru.
     */
    protected function resetCounters(): void
    {
        $this->imported = 0;
        $this->skipped = 0;
        $this->failed = 0;
        $this->errors = [];
    }

    /**
     * Ambil ringkasan hasil import.
     */
    public function getSummary(): array
    {
        return [
            'imported' => $this->imported,
            'skipped' => $this->skipped,
            'failed' => $this->failed,
            'errors' => $this->errors,
        ];
    }
}
