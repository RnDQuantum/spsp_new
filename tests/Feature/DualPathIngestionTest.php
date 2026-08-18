<?php

namespace Tests\Feature;

use App\Models\Mmpi;
use App\Models\TestResult;
use App\Services\Api\ApiDataTransformerService;
use App\Services\Api\QuantumApiClient;
use App\Services\Lsp\LspDataImporterService;
use App\Services\TestReportService;
use Tests\TestCase;

class DualPathIngestionTest extends TestCase
{
    /**
     * Test detection of Jalur A (< PR-A-338) vs Jalur B (>= PR-A-338)
     */
    public function test_can_distinguish_jalur_a_and_jalur_b_project_codes(): void
    {
        $importer = app(LspDataImporterService::class);

        $this->assertTrue($importer->isLegacyProject('PR-A-313'));
        $this->assertTrue($importer->isLegacyProject('PR-A-307'));
        $this->assertTrue($importer->isLegacyProject('PR-B-290'));

        $this->assertFalse($importer->isLegacyProject('PR-A-338'));
        $this->assertFalse($importer->isLegacyProject('PR-A-385'));
        $this->assertFalse($importer->isLegacyProject('PR-B-400'));
    }

    /**
     * Test QuantumApiClient configuration and fallback mechanism
     */
    public function test_quantum_api_client_can_fetch_mock_data(): void
    {
        $apiClient = app(QuantumApiClient::class);

        $mockTests = $apiClient->fetchParticipantTests('test_user');
        $this->assertIsArray($mockTests);
        $this->assertArrayHasKey('A.1', $mockTests);
        $this->assertArrayHasKey('B.2', $mockTests);
        $this->assertEquals(115, $mockTests['A.1']['iq']);
    }

    /**
     * Test API Data Transformer extracts MMPI data for Mmpi model saving
     */
    public function test_api_transformer_extracts_mmpi_data(): void
    {
        $apiTransformer = app(ApiDataTransformerService::class);

        $samplePesertaData = [
            'username' => 'user_api_test',
            'nama' => 'Peserta API Test',
            'tes' => [
                'E.2' => [
                    'validitas' => 'Valid & Konsisten',
                    'internal' => 'Stabil',
                    'interpersonal' => 'Baik',
                    'kapasitas_kerja' => 'Optimal',
                    'klinis' => 'Normal',
                    'kesimpulan' => 'Bebas dari Gejala Klinik Berat',
                    'psikogram' => ['Stres' => 'Rendah'],
                    'pq' => 85.5,
                    'stres' => 'Normal',
                ],
            ],
        ];

        $dto = $apiTransformer->transformSingleParticipant('PR-A-338', 'user_api_test', $samplePesertaData);

        $this->assertNotNull($dto);
        $this->assertArrayHasKey('mmpi', $dto);
        $this->assertEquals('Valid & Konsisten', $dto['mmpi']['validitas']);
        $this->assertEquals(85.5, $dto['mmpi']['nilai_pq']);
        $this->assertEquals('Normal', $dto['mmpi']['tingkat_stres']);
    }

    /**
     * Test API Data Transformer extracts CFIT 3B (A.2), PAPI Kostik (B.1) raw factors, and 16PF (B.2)
     */
    public function test_api_transformer_extracts_cfit_kostik_and_16pf_data(): void
    {
        $apiTransformer = app(ApiDataTransformerService::class);

        $samplePesertaData = [
            'username' => 'cfit_kostik_user',
            'nama' => 'Peserta Tes Lengkap',
            'tes' => [
                'A.2' => [
                    'iq' => 110,
                    'kategori' => 'Rata-rata Atas',
                    'hasil_sub' => [
                        'sub1' => ['nilai' => 8, 'rating' => 4],
                        'sub2' => ['nilai' => 7, 'rating' => 3],
                    ],
                ],
                'B.1' => [
                    'hasil_G' => 7,
                    'hasil_L' => 5,
                    'hasil_I' => 4,
                    'hasil_T' => 6,
                    'hasil_V' => 5,
                    'hasil_A' => 6,
                ],
                'B.2' => [
                    'standart_final' => [
                        'A' => 6, 'B' => 5, 'C' => 7, 'E' => 6,
                    ],
                    'MDStenScore' => 7,
                ],
            ],
        ];

        $dto = $apiTransformer->transformSingleParticipant('PR-A-341', 'cfit_kostik_user', $samplePesertaData);

        $this->assertNotNull($dto);
        // Verify IQ extracted from A.2
        $this->assertEquals(110, $dto['raw_scores']['ist_components']['iq']);
        $this->assertEquals('Rata-rata Atas', $dto['raw_scores']['ist_components']['kategori']);

        // Verify 16PF sten scores & MD score
        $this->assertEquals(7, $dto['raw_scores']['pf16_components']['md']);
        $this->assertEquals(6, $dto['raw_scores']['pf16_components']['sten']['A']);

        // Verify raw scores api_full
        $this->assertArrayHasKey('api_full', $dto['raw_scores']);
        $this->assertArrayHasKey('A.2', $dto['raw_scores']['api_full']);
        $this->assertArrayHasKey('B.1', $dto['raw_scores']['api_full']);
    }

    /**
     * Test TestReportService formats Kraepelin (D.2), EQ (F.1), Behavior (G.1), and RMIB (H.1) accurately
     */
    public function test_test_report_service_formats_instruments_accurately(): void
    {
        $reportService = app(TestReportService::class);

        // 1. Kraepelin Test Result Mock
        $kraepelinTr = new TestResult([
            'test_code' => 'D.2',
            'summary_data' => [
                'kesimpulan_akhir' => [
                    'panker' => 4,
                    'janker_average' => 3,
                    'janker_range' => 2,
                    'hanker' => 4,
                    'tianker' => 3,
                ],
            ],
        ]);
        $fmtKraepelin = $reportService->formatTestDataForDisplay($kraepelinTr);
        $this->assertEquals(4, $fmtKraepelin['pspeed']);
        $this->assertEquals(3, $fmtKraepelin['pacc']);
        $this->assertEquals(4, $fmtKraepelin['pstab']);
        $this->assertEquals(3, $fmtKraepelin['pstn']);

        // 2. EQ Test Result Mock
        $eqTr = new TestResult([
            'test_code' => 'F.1',
            'summary_data' => [
                'skor_akhir' => 346,
                'kategori' => 'Istimewa',
                'dimensi' => [
                    '4' => ['nama' => 'Kesadaran Emosi Diri', 'skor' => 28],
                ],
                'hasil_akhir' => ['4' => 3],
            ],
        ]);
        $fmtEq = $reportService->formatTestDataForDisplay($eqTr);
        $this->assertEquals(346, $fmtEq['eq_score']);
        $this->assertEquals('Istimewa', $fmtEq['kategori']);
        $this->assertArrayHasKey('4', $fmtEq['dimensions']);

        // 3. Behavior Tendencies Test Result Mock
        $behaviorTr = new TestResult([
            'test_code' => 'G.1',
            'summary_data' => [
                'hasil_kecenderungan' => 'ILMUWAN',
                'iman' => 17,
                'pikiran' => 36,
                'perasaan' => 14,
                'interpretasi_kebiasaan' => 'Pola pikir analitis',
            ],
        ]);
        $fmtBehavior = $reportService->formatTestDataForDisplay($behaviorTr);
        $this->assertEquals('ILMUWAN', $fmtBehavior['tipe']);
        $this->assertEquals(17, $fmtBehavior['iman']);
        $this->assertEquals(36, $fmtBehavior['pikiran']);
        $this->assertEquals(14, $fmtBehavior['perasaan']);

        // 4. RMIB Test Result Mock
        $rmibTr = new TestResult([
            'test_code' => 'H.1',
            'summary_data' => [
                'nilai_1' => 'Clerical',
                'nilai_2' => 'Musical',
                'nilai_3' => 'Computational',
                'nilai' => '10,8,3',
            ],
        ]);
        $fmtRmib = $reportService->formatTestDataForDisplay($rmibTr);
        $this->assertContains('Clerical', $fmtRmib['top_interests']);
        $this->assertContains('Musical', $fmtRmib['top_interests']);
        $this->assertContains('Computational', $fmtRmib['top_interests']);
    }
}
