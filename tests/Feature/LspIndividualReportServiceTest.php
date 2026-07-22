<?php

namespace Tests\Feature;

use App\Services\Lsp\LspIndividualReportService;
use Tests\TestCase;

class LspIndividualReportServiceTest extends TestCase
{
    /**
     * Test fetching and calculating report data for a real participant in local LSP DB
     */
    public function test_can_generate_lsp_individual_report_from_local_db(): void
    {
        $service = new LspIndividualReportService;

        $username = 'bntn01-001';
        $kodeProyek = 'PR-A-313';

        $report = $service->getIndividualReport($username, $kodeProyek);

        $this->assertIsArray($report);
        $this->assertArrayHasKey('peserta', $report);
        $this->assertEquals('01-1-1-02-001', $report['peserta']['no_test']);
        $this->assertEquals('24400240110001036', $report['peserta']['no_kjg']);

        // Check header scores
        $this->assertArrayHasKey('header_scores', $report);
        $this->assertGreaterThan(0, $report['header_scores']['psikotest_percent']);

        // Check Potensi Profile
        $this->assertArrayHasKey('potensi', $report);
        $this->assertGreaterThan(0, count($report['potensi']['aspek_list']));

        // Check Kompetensi Profile
        $this->assertArrayHasKey('kompetensi', $report);
        $this->assertGreaterThan(0, count($report['kompetensi']['aspek_list']));

        // Check Final Recommendation
        $this->assertArrayHasKey('rekomendasi_akhir', $report);
        $this->assertNotEmpty($report['rekomendasi_akhir']['final_text']);

        // Check Qualitative Wawancara Data
        $this->assertArrayHasKey('wawancara', $report);
        $this->assertNotEmpty($report['wawancara']['nama_asesor_ta']);
    }
}
