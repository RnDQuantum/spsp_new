<?php

namespace Tests\Feature;

use App\Models\Mmpi;
use App\Services\Api\ApiDataTransformerService;
use App\Services\Api\QuantumApiClient;
use App\Services\Lsp\LspDataImporterService;
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
}
