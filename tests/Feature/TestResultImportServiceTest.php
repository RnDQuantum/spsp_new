<?php

namespace Tests\Feature;

use App\Models\AssessmentEvent;
use App\Models\AssessmentTemplate;
use App\Models\Batch;
use App\Models\Institution;
use App\Models\Participant;
use App\Models\PositionFormation;
use App\Models\TestResult;
use App\Services\Api\QuantumApiClient;
use App\Services\TestResultImportService;
use Tests\TestCase;

class TestResultImportServiceTest extends TestCase
{
    public function test_can_import_test_results_from_api_client(): void
    {
        $apiClient = app(QuantumApiClient::class);
        $importService = app(TestResultImportService::class);

        $institution = Institution::firstOrCreate(
            ['code' => 'test-inst'],
            ['name' => 'Test Inst', 'logo_path' => 'logo.png', 'api_key' => 'key']
        );

        $event = AssessmentEvent::firstOrCreate(
            ['code' => 'TEST-EVENT-01'],
            [
                'institution_id' => $institution->id,
                'name' => 'Test Event 01',
                'year' => 2026,
                'start_date' => now()->toDateString(),
                'end_date' => now()->toDateString(),
                'status' => 'completed',
            ]
        );

        $batch = Batch::firstOrCreate(
            ['event_id' => $event->id, 'code' => 'b1'],
            ['name' => 'Batch 1', 'location' => 'Pusat', 'batch_number' => 1, 'start_date' => now()->toDateString(), 'end_date' => now()->toDateString()]
        );
        $template = AssessmentTemplate::firstOrCreate(['code' => 't1'], ['name' => 'T1', 'description' => 'd']);
        $formation = PositionFormation::firstOrCreate(
            ['event_id' => $event->id, 'code' => 'f1'],
            ['template_id' => $template->id, 'name' => 'F1', 'quota' => 10]
        );

        $participant = Participant::firstOrCreate(
            ['username' => 'test_user_api_01'],
            [
                'event_id' => $event->id,
                'batch_id' => $batch->id,
                'position_formation_id' => $formation->id,
                'test_number' => 'TST-001',
                'skb_number' => 'SKB-001',
                'name' => 'Test User API',
                'gender' => 'L',
                'assessment_date' => now()->toDateString(),
            ]
        );

        $tesData = $apiClient->fetchParticipantTests($participant->id, $event->id);

        $result = $importService->importParticipantTests($participant->id, $event->id, $tesData, 'api');

        $this->assertIsArray($result);
        $this->assertGreaterThan(0, $result['imported']);
        $this->assertEquals(0, $result['failed']);

        $cfitResult = TestResult::where('participant_id', $participant->id)
            ->where('event_id', $event->id)
            ->where('test_code', 'A.1')
            ->first();

        $this->assertNotNull($cfitResult);
        $this->assertEquals('api', $cfitResult->source);
        $this->assertEquals('pending', $cfitResult->conversion_status);
        $this->assertEquals(115, $cfitResult->summary_data['iq'] ?? null);
    }
}
