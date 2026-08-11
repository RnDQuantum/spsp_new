<?php

namespace App\Services;

use App\Models\TestResult;
use App\Services\Lsp\LspNormEngineService;
use Exception;

/**
 * TestReportService - Service penyedia Laporan Alat Tes (Detail per Instrumen Tes)
 *
 * Bertanggung jawab menyusun laporan detail per alat tes (IST, PAPI Kostik, 16PF, CFIT, Kraepelin, dll.)
 * baik dari data mentah SPSP (tabel test_results) maupun dari norm engine.
 */
class TestReportService
{
    public function __construct(
        protected LspNormEngineService $normEngine
    ) {}

    /**
     * Ambil data laporan detail alat tes tertentu untuk seorang peserta di event tertentu.
     */
    public function getTestReport(int $participantId, int $eventId, string $testCode): array
    {
        $testResult = TestResult::where('participant_id', $participantId)
            ->where('event_id', $eventId)
            ->where('test_code', $testCode)
            ->first();

        if (! $testResult) {
            throw new Exception("Data alat tes dengan kode '{$testCode}' tidak ditemukan untuk peserta ID {$participantId}.");
        }

        return [
            'test_code' => $testResult->test_code,
            'test_name' => $testResult->test_name,
            'test_category' => $testResult->test_category,
            'status' => $testResult->status,
            'test_started_at' => $testResult->test_started_at,
            'summary_data' => $testResult->summary_data,
            'interpretation_data' => $testResult->interpretation_data,
        ];
    }

    /**
     * Ambil seluruh laporan detail alat tes yang dimiliki peserta pada event tertentu.
     */
    public function getParticipantAllTestReports(int $participantId, int $eventId): array
    {
        $testResults = TestResult::where('participant_id', $participantId)
            ->where('event_id', $eventId)
            ->get();

        $reports = [];
        foreach ($testResults as $tr) {
            $reports[$tr->test_code] = [
                'test_code' => $tr->test_code,
                'test_name' => $tr->test_name,
                'test_category' => $tr->test_category,
                'status' => $tr->status,
                'source' => $tr->source,
                'summary_data' => $tr->summary_data,
                'interpretation_data' => $tr->interpretation_data,
                'formatted' => $this->formatTestDataForDisplay($tr),
            ];
        }

        return $reports;
    }

    /**
     * Format payload summary_data untuk kebutuhan tampilan UI Laporan Alat Tes.
     */
    public function formatTestDataForDisplay(TestResult $tr): array
    {
        $data = $tr->summary_data ?? [];

        // Format per kode tes
        return match ($tr->test_code) {
            'A.1', 'A.2', 'A.5' => [
                'iq' => $data['iq'] ?? $data['index_kecerdasan_umum'] ?? '100',
                'kategori' => $data['hasil_kategori'] ?? $data['kategori'] ?? 'Rata-rata',
                'subtests' => $data['label_values'] ?? $data['hasil_sub'] ?? [],
            ],
            'B.1', 'D.1' => [
                'factors' => $data['nilaiAspek'] ?? [],
                'labels' => $data['labels_aspek'] ?? [],
                'narratives' => array_filter($data, fn ($k) => str_contains((string) $k, '_1') || str_contains((string) $k, '_2') || str_contains((string) $k, '_3'), ARRAY_FILTER_USE_KEY),
            ],
            'B.2' => [
                'sten_scores' => $data['nilaiAspek'] ?? [],
                'md_score' => $data['MDStenScore'] ?? 5,
            ],
            'D.2' => [
                'pspeed' => $data['pspeed'] ?? $data['kecepatan'] ?? 0,
                'pacc' => $data['pacc'] ?? $data['ketelitian'] ?? 0,
                'pstab' => $data['pstab'] ?? $data['kestabilan'] ?? 0,
                'pstn' => $data['pstn'] ?? $data['ketahanan'] ?? 0,
            ],
            'F.1' => [
                'eq_score' => $data['eq_score'] ?? $data['skor_eq'] ?? 0,
                'kategori' => $data['kategori'] ?? 'Cukup',
                'aspects' => $data['labels_aspek'] ?? $data['aspek'] ?? [],
            ],
            'G.1' => [
                'most' => $data['most'] ?? [],
                'least' => $data['least'] ?? [],
                'change' => $data['change'] ?? [],
            ],
            default => [
                'raw_payload' => $data,
            ],
        };
    }

    /**
     * Hitung norma IST secara langsung dari raw score string.
     */
    public function evaluateIstNorms(?string $rawIst, string $pendidikan = 'S1', int $usia = 25): array
    {
        return $this->normEngine->processIstNorms($rawIst, $pendidikan, $usia);
    }

    /**
     * Hitung norma PAPI Kostik secara langsung dari raw score string.
     */
    public function evaluateKostikNorms(?string $rawKostik): array
    {
        return $this->normEngine->processKostikNorms($rawKostik);
    }

    /**
     * Hitung norma 16PF secara langsung dari raw score string.
     */
    public function evaluate16pfNorms(?string $rawPersonality, int $usia = 25): array
    {
        return $this->normEngine->process16pfNorms($rawPersonality, $usia);
    }
}
