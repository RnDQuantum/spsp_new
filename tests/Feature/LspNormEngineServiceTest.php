<?php

namespace Tests\Feature;

use App\Services\Lsp\LspNormEngineService;
use Tests\TestCase;

class LspNormEngineServiceTest extends TestCase
{
    public function test_can_load_norm_data_files(): void
    {
        $service = new LspNormEngineService;
        $normData = $service->loadNormData();

        $this->assertIsArray($normData);
        $this->assertArrayHasKey('ist', $normData);
        $this->assertArrayHasKey('kostik', $normData);
        $this->assertArrayHasKey('personality', $normData);
    }

    public function test_can_process_ist_raw_scores(): void
    {
        $service = new LspNormEngineService;
        $rawIst = '12,15,10,14,11,13,9,8,10';

        $result = $service->processIstNorms($rawIst, 'S1', 28);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('iq', $result);
        $this->assertGreaterThan(0, $result['iq']);
        $this->assertArrayHasKey('kategori', $result);
        $this->assertArrayHasKey('scores', $result);
    }

    public function test_can_process_kostik_raw_scores(): void
    {
        $service = new LspNormEngineService;
        $rawKostik = '5,4,6,7,3,4,5,6,2,3,4,5,6,7,8,4,3,2,5,6';

        $result = $service->processKostikNorms($rawKostik);

        $this->assertIsArray($result);
        $this->assertCount(20, $result);
        $this->assertArrayHasKey('A', $result);
        $this->assertEquals(5, $result['A']);
    }

    public function test_can_process_16pf_raw_scores(): void
    {
        $service = new LspNormEngineService;
        $rawPersonality = '5,8,9,7,6,5,4,7,8,9,6,5,4,7,8,9,6';

        $result = $service->process16pfNorms($rawPersonality, 25);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('A', $result);
        $this->assertGreaterThanOrEqual(1, $result['A']);
        $this->assertLessThanOrEqual(10, $result['A']);
    }
}
