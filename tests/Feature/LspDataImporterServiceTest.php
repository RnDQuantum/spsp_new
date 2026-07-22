<?php

namespace Tests\Feature;

use App\Models\AspectAssessment;
use App\Models\CategoryAssessment;
use App\Models\FinalAssessment;
use App\Models\Participant;
use App\Models\PsychologicalTest;
use App\Services\Lsp\LspDataImporterService;
use Tests\TestCase;

class LspDataImporterServiceTest extends TestCase
{
    /**
     * Test importing real LSP participant into SPSP native database tables
     */
    public function test_can_import_lsp_participant_to_spsp_tables(): void
    {
        $importer = app(LspDataImporterService::class);

        $kodeProyek = 'PR-A-313';
        $username = 'bntn01-001';

        $result = $importer->importProject($kodeProyek, $username);

        $this->assertIsArray($result);
        $this->assertEquals(1, $result['imported_count']);
        $this->assertEquals(0, $result['failed_count']);

        // Assert Participant record exists in SPSP
        $participant = Participant::where('username', $username)->first();
        $this->assertNotNull($participant);
        $this->assertEquals('dr. TAN ANDI, Sp.An-TI', $participant->name);
        $this->assertEquals('24400240110001036', $participant->skb_number);

        // Assert PsychologicalTest (MMPI)
        $mmpi = PsychologicalTest::where('participant_id', $participant->id)->first();
        $this->assertNotNull($mmpi);
        $this->assertStringContainsString('kurang akurat', $mmpi->validitas);

        // Assert CategoryAssessments (Potensi & Kompetensi)
        $categoryAssessments = CategoryAssessment::where('participant_id', $participant->id)->get();
        $this->assertCount(2, $categoryAssessments);

        // Assert AspectAssessments
        $aspectAssessments = AspectAssessment::where('participant_id', $participant->id)->get();
        $this->assertGreaterThan(5, $aspectAssessments->count());

        // Assert FinalAssessment
        $final = FinalAssessment::where('participant_id', $participant->id)->first();
        $this->assertNotNull($final);
        $this->assertEquals('MS', $final->conclusion_code);
        $this->assertEquals('MEMENUHI SYARAT (MS)', $final->conclusion_text);
    }
}
