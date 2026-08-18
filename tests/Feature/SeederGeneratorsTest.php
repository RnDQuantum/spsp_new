<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Livewire\Pages\HCA\Sections\DevelopmentRecommendation;
use App\Livewire\Pages\HCA\Sections\QualitativeListSection;
use App\Models\AssessmentEvent;
use App\Models\Batch;
use App\Models\Participant;
use App\Models\PositionFormation;
use Database\Seeders\Data\AssessmentEventConfig;
use Database\Seeders\Support\AssessmentRecordGenerator;
use Database\Seeders\Support\ParticipantProfileGenerator;
use Database\Seeders\Support\PersonalProfileGenerator;
use Database\Seeders\Support\TestResultGenerator;
use Tests\TestCase;

class SeederGeneratorsTest extends TestCase
{
    /**
     * Test AssessmentEventConfig returns valid configurations array
     */
    public function test_assessment_event_config_returns_15_events(): void
    {
        $configs = AssessmentEventConfig::getConfigurations();

        $this->assertIsArray($configs);
        $this->assertCount(15, $configs);

        $eventCodes = collect($configs)->pluck('event.code')->all();
        $this->assertContains('PR-A-313', $eventCodes);
        $this->assertContains('PR-A-338', $eventCodes);
        $this->assertContains('PR-A-355', $eventCodes);
    }

    /**
     * Test ParticipantProfileGenerator generates valid 18 attributes
     */
    public function test_participant_profile_generator_generates_valid_profile(): void
    {
        $event = new AssessmentEvent(['institution_id' => 1]);
        $event->id = 1;
        $batch = new Batch;
        $batch->id = 1;
        $position = new PositionFormation(['name' => 'Analis Hukum']);
        $position->id = 1;

        ParticipantProfileGenerator::resetCounter();
        $profile = ParticipantProfileGenerator::generate($event, $batch, $position);

        $this->assertIsArray($profile);
        $this->assertEquals(1, $profile['event_id']);
        $this->assertEquals(1, $profile['batch_id']);
        $this->assertEquals('ANALIS HUKUM', $profile['jabatan_pelaksana']);
        $this->assertNotEmpty($profile['username']);
        $this->assertNotEmpty($profile['test_number']);
        $this->assertNotEmpty($profile['skb_number']);
        $this->assertNotEmpty($profile['email']);
        $this->assertNotEmpty($profile['nik']);
    }

    /**
     * Test AssessmentRecordGenerator calculates nine box distribution and multipliers
     */
    public function test_assessment_record_generator_nine_box_and_multipliers(): void
    {
        $distribution = ['K-1' => 0, 'K-5' => 100];
        $box = AssessmentRecordGenerator::determineNineBoxCategory($distribution);
        $this->assertEquals('K-5', $box);

        $multHigh = AssessmentRecordGenerator::getPerformanceMultiplier('high');
        $this->assertEquals(1.05, $multHigh[0]);
        $this->assertEquals(1.25, $multHigh[1]);

        $conclusion = AssessmentRecordGenerator::determineFinalConclusion(115.0);
        $this->assertEquals('K', $conclusion);
    }

    /**
     * Test TestResultGenerator produces accurate test results for legacy vs api events
     */
    public function test_test_result_generator_produces_complete_test_results(): void
    {
        $participant = new Participant([
            'id' => 999,
            'event_id' => 10,
            'assessment_date' => '2025-10-10',
            'pendidikan' => 'S1',
        ]);

        // 1. Online API Event (PR-A-338)
        $apiEvent = new AssessmentEvent([
            'id' => 10,
            'code' => 'PR-A-338',
        ]);

        $apiResults = TestResultGenerator::generateForParticipant($participant, $apiEvent, 'K-9');
        $this->assertIsArray($apiResults);
        $this->assertNotEmpty($apiResults);

        $testCodes = collect($apiResults)->pluck('test_code')->all();
        $this->assertContains('A.1', $testCodes);
        $this->assertContains('B.1', $testCodes);
        $this->assertContains('B.2', $testCodes);
        $this->assertContains('D.2', $testCodes);
        $this->assertContains('F.1', $testCodes);
        $this->assertContains('G.1', $testCodes);
        $this->assertContains('H.1', $testCodes);

        // Verify summary data json content
        $kraepelinTr = collect($apiResults)->firstWhere('test_code', 'D.2');
        $kSummary = json_decode($kraepelinTr['summary_data'], true);
        $this->assertArrayHasKey('kesimpulan_akhir', $kSummary);
        $this->assertArrayHasKey('panker', $kSummary['kesimpulan_akhir']);

        // 2. Legacy DB Event (PR-A-313)
        $legacyEvent = new AssessmentEvent([
            'id' => 11,
            'code' => 'PR-A-313',
        ]);

        $legacyResults = TestResultGenerator::generateForParticipant($participant, $legacyEvent, 'K-5');
        $legacyCodes = collect($legacyResults)->pluck('test_code')->all();
        $this->assertContains('A.5', $legacyCodes);
        $this->assertContains('D.1', $legacyCodes);
        $this->assertContains('B.2', $legacyCodes);
        $this->assertEquals('lsp_db', $legacyResults[0]['source']);
    }

    /**
     * Test PersonalProfileGenerator calculates zodiac, shio, weton accurately
     */
    public function test_personal_profile_generator_calculates_zodiac_shio_weton(): void
    {
        $participant = new Participant([
            'name' => 'Dr. Bambang Setiawan, M.M.',
            'tanggal_lahir' => '1985-08-17',
        ]);
        $participant->id = 101;

        $profile = PersonalProfileGenerator::generateForParticipant($participant);

        $this->assertIsArray($profile);
        $this->assertEquals(101, $profile['participant_id']);
        $this->assertEquals('Leo', $profile['zodiac']);
        $this->assertEquals('Kerbau', $profile['chinese_zodiac']); // 1985 is Kerbau (Ox)
        $this->assertStringContainsString('Sabtu', $profile['weton']); // 1985-08-17 was Saturday
        $this->assertNotEmpty($profile['hobbies']);
        $this->assertNotEmpty($profile['sports']);
        $this->assertNotEmpty($profile['blood_type']);
    }

    /**
     * Test HCA Livewire Section components render without errors
     */
    public function test_hca_sections_render_dynamically(): void
    {
        $participant = Participant::query()->first();
        if (! $participant) {
            $this->markTestSkipped('No participants in test database');
        }

        // Test DevelopmentRecommendation
        $devComp = new DevelopmentRecommendation;
        $devComp->participantId = $participant->id;
        $devView = $devComp->render();
        $this->assertNotEmpty($devView->getData()['focusTheme']);
        $this->assertIsArray($devView->getData()['strengths']);
        $this->assertIsArray($devView->getData()['gaps']);

        // Test QualitativeListSection (strengths)
        $qualComp = new QualitativeListSection;
        $qualComp->participantId = $participant->id;
        $qualComp->sectionCode = 'strengths';
        $qualView = $qualComp->render();
        $this->assertCount(5, $qualView->getData()['items']);

        // Test QualitativeListSection (personal_profile)
        $qualComp->sectionCode = 'personal_profile';
        $profView = $qualComp->render();
        $this->assertGreaterThanOrEqual(4, count($profView->getData()['items']));
    }
}
