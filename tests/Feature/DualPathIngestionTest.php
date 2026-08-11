<?php

namespace Tests\Feature;

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
}
