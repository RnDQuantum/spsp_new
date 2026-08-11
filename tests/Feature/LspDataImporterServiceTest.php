<?php

namespace Tests\Feature;

use App\Models\AspectAssessment;
use App\Models\CategoryAssessment;
use App\Models\FinalAssessment;
use App\Models\Mmpi;
use App\Models\Participant;
use App\Models\TestResult;
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
        $this->assertEmpty($result['errors'], json_encode($result['errors']));
        $this->assertEquals(1, $result['imported_count']);
        $this->assertEquals(0, $result['failed_count']);

        // Assert Participant record exists in SPSP
        $participant = Participant::where('username', $username)->first();
        $this->assertNotNull($participant);
        $this->assertEquals('dr. TAN ANDI, Sp.An-TI', $participant->name);
        $this->assertEquals('24400240110001036', $participant->skb_number);

        // Assert Master Data: Project & Institution
        $event = $participant->event;
        $this->assertNotNull($event);
        $this->assertEquals('PR-A-313', $event->code);
        $this->assertNotNull($event->project);
        $this->assertEquals('AP-085', $event->project->code);
        $this->assertNotNull($event->institution);
        $this->assertEquals('kp-110', $event->institution->code);

        // Assert Mmpi
        $mmpi = Mmpi::where('participant_id', $participant->id)->first();
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

        // Assert Raw TestResults saved from LSP DB
        $testResults = TestResult::where('participant_id', $participant->id)->get();
        $this->assertGreaterThan(0, $testResults->count());
        $this->assertEquals('lsp_db', $testResults->first()->source);
    }
}
