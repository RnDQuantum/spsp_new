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
