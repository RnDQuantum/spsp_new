<?php

declare(strict_types=1);

namespace Tests\Feature\Livewire;

use App\Livewire\Pages\HCA\HcaReportPage;
use App\Livewire\Pages\HCA\Sections\DiscProfile;
use App\Livewire\Pages\HCA\Sections\ExecutiveSummary;
use App\Livewire\Pages\HCA\Sections\IndexRadarSection;
use App\Livewire\Pages\HCA\Sections\NineBoxMatrix;
use App\Livewire\Pages\HCA\Sections\ParticipantProfile;
use App\Livewire\Pages\HCA\Sections\PerformanceDashboard;
use App\Livewire\Pages\HCA\Sections\ScoreListSection;
use App\Livewire\Pages\HCA\Sections\SuccessionReadiness;
use App\Livewire\Pages\HCA\Sections\TimelineSection;
use App\Models\Institution;
use App\Models\Participant;
use App\Models\User;
use Livewire\Livewire;
use Tests\TestCase;

class HcaReportPageTest extends TestCase
{
    /**
     * Test that the demo route renders successfully
     */
    public function test_demo_route_renders_successfully(): void
    {
        $response = $this->get(route('hca-report-demo'));

        $response->assertStatus(200);
        $response->assertSeeLivewire(HcaReportPage::class);
    }

    /**
     * Test that the main hca-report route requires authentication
     */
    public function test_hca_report_route_requires_authentication(): void
    {
        $response = $this->get(route('hca-report'));

        $response->assertRedirect(route('login'));
    }

    /**
     * Test that authenticated user with valid institution can access the main hca-report route
     */
    public function test_authenticated_user_can_access_hca_report(): void
    {
        $institution = Institution::query()->first() ?? Institution::factory()->create();
        /** @var User $user */
        $user = User::factory()->create([
            'institution_id' => $institution->id,
        ]);

        $response = $this->actingAs($user)->get(route('hca-report'));

        $response->assertStatus(200);
        $response->assertSeeLivewire(HcaReportPage::class);
    }

    /**
     * Test that user without assigned institution is blocked with 403
     */
    public function test_user_without_institution_is_blocked(): void
    {
        /** @var User $user */
        $user = User::factory()->create([
            'institution_id' => null,
        ]);

        $response = $this->actingAs($user)->get(route('hca-report'));

        $response->assertStatus(403);
    }

    /**
     * Test that hca-report route with participant ID loads active participant
     */
    public function test_hca_report_with_participant_parameter_loads_correctly(): void
    {
        $institution = Institution::query()->first() ?? Institution::factory()->create();
        /** @var User $user */
        $user = User::factory()->create([
            'institution_id' => $institution->id,
        ]);
        $participant = Participant::query()->where('institution_id', $institution->id)->first()
            ?? Participant::factory()->create(['institution_id' => $institution->id]);

        $response = $this->actingAs($user)->get(route('hca-report', ['participant' => $participant->id]));

        $response->assertStatus(200);
        $response->assertSeeLivewire(HcaReportPage::class);
    }

    /**
     * Test that the component initializes with default values
     */
    public function test_component_initializes_with_default_values(): void
    {
        Livewire::test(HcaReportPage::class)
            ->assertSet('activeSection', 'cover')
            ->assertSet('printMode', false);
    }

    /**
     * Test that switching sections updates the active section property
     */
    public function test_switching_sections_updates_state(): void
    {
        Livewire::test(HcaReportPage::class)
            // Initial state
            ->assertSet('activeSection', 'cover')

            // Switch to HCI (which is active in Phase A)
            ->call('setSection', 'hci')
            ->assertSet('activeSection', 'hci')

            // Switch to Riwayat Karier (active in Phase A)
            ->call('setSection', 'career')
            ->assertSet('activeSection', 'career')

            // Switch to Performance Dashboard (active in Phase A)
            ->call('setSection', 'performance')
            ->assertSet('activeSection', 'performance')

            // Switch to Kekuatan Psikologis (active in Phase A)
            ->call('setSection', 'strengths')
            ->assertSet('activeSection', 'strengths')

            // Switch to Executive Summary (newly active in Phase B)
            ->call('setSection', 'exec_summary')
            ->assertSet('activeSection', 'exec_summary')

            // Switch to DISC (newly active in Phase B)
            ->call('setSection', 'disc')
            ->assertSet('activeSection', 'disc')

            // Switch to 9-Box Matrix (newly active in Phase B)
            ->call('setSection', 'nine_box')
            ->assertSet('activeSection', 'nine_box');
    }

    /**
     * Test that switching to a non-existent section does not change the state
     */
    public function test_switching_to_invalid_section_does_not_change_state(): void
    {
        Livewire::test(HcaReportPage::class)
            ->assertSet('activeSection', 'cover')

            // Switch to a completely invalid code
            ->call('setSection', 'invalid_section_code_999')
            ->assertSet('activeSection', 'cover'); // should remain 'cover'
    }

    /**
     * Test that print mode toggles state correctly
     */
    public function test_print_mode_toggles_state(): void
    {
        Livewire::test(HcaReportPage::class)
            ->assertSet('printMode', false)
            ->call('togglePrintMode', true)
            ->assertSet('printMode', true)
            ->call('togglePrintMode', false)
            ->assertSet('printMode', false);
    }

    /**
     * Test that selecting a participant updates participantId and session
     */
    public function test_selecting_participant_updates_participant_id(): void
    {
        $participant = Participant::query()->first() ?? Participant::factory()->create();

        Livewire::test(HcaReportPage::class)
            ->call('selectParticipant', $participant->id)
            ->assertSet('participantId', $participant->id)
            ->assertSet('showTalentModal', false);

        $this->assertEquals($participant->id, session('filter.participant_id'));
    }

    /**
     * Test toggling talent selector modal
     */
    public function test_toggling_talent_modal_updates_state(): void
    {
        Livewire::test(HcaReportPage::class)
            ->assertSet('showTalentModal', false)
            ->call('toggleTalentModal')
            ->assertSet('showTalentModal', true)
            ->call('toggleTalentModal')
            ->assertSet('showTalentModal', false);
    }

    /**
     * Test executive summary section renders dynamic 5 pillars data
     */
    public function test_executive_summary_section_renders_dynamic_data(): void
    {
        $participant = Participant::query()->first() ?? Participant::factory()->create();

        Livewire::test(ExecutiveSummary::class, ['participantId' => $participant->id])
            ->assertSee('Ringkasan')
            ->assertSee('Talent Index Score')
            ->assertSee('Pilar Evaluasi Asesmen')
            ->assertSee('Kompetensi')
            ->assertSee('Potensi')
            ->assertSee('Kinerja');
    }

    /**
     * Test timeline section renders dynamic career history data
     */
    public function test_timeline_section_renders_dynamic_career_history_data(): void
    {
        $participant = Participant::with('careerHistories')->first();
        if (! $participant) {
            $this->markTestSkipped('No participant found');
        }

        Livewire::test(TimelineSection::class, ['participantId' => $participant->id])
            ->assertSee('Riwayat')
            ->assertSee('Karier')
            ->assertSee('Estimasi Masa Kerja Efektif')
            ->assertSee('Posisi Saat Ini');
    }

    /**
     * Test participant has careerHistories relationship working
     */
    public function test_participant_has_career_histories_relationship(): void
    {
        $participant = Participant::with('careerHistories')->first();
        if (! $participant) {
            $this->markTestSkipped('No participant found');
        }

        if ($participant->careerHistories()->doesntExist()) {
            $participant->careerHistories()->create([
                'position_title' => 'VP of Talent Management',
                'company_or_institution' => 'Kantor Pusat',
                'start_year' => 2024,
                'end_year' => null,
                'is_current' => true,
                'achievements' => ['Memimpin transformasi talent mapping.'],
                'order_index' => 0,
            ]);
            $participant->load('careerHistories');
        }

        $this->assertTrue($participant->careerHistories()->exists());
        $firstCareer = $participant->careerHistories->first();
        $this->assertNotNull($firstCareer->position_title);
        $this->assertTrue($firstCareer->is_current);
        $this->assertIsArray($firstCareer->achievements);
    }

    /**
     * Test score list section renders dynamic competency data for participant
     */
    public function test_score_list_section_renders_dynamic_competency_data(): void
    {
        $participant = Participant::query()->first();
        if (! $participant) {
            $this->markTestSkipped('No participant found');
        }

        Livewire::test(ScoreListSection::class, [
            'sectionCode' => 'competency',
            'participantId' => $participant->id,
        ])
            ->assertSee('Layer 1')
            ->assertSee('Kompetensi')
            ->assertSee('Skor Rata-Rata')
            ->assertSee('Skor aktual')
            ->assertSee('Standar');
    }

    /**
     * Test index radar section renders dynamic potential data for participant
     */
    public function test_index_radar_section_renders_dynamic_potential_data(): void
    {
        $participant = Participant::query()->first();
        if (! $participant) {
            $this->markTestSkipped('No participant found');
        }

        Livewire::test(IndexRadarSection::class, [
            'sectionCode' => 'potential',
            'participantId' => $participant->id,
        ])
            ->assertSee('Layer')
            ->assertSee('Potensi')
            ->assertSee('Score Index');
    }

    /**
     * Test score list section renders dynamic cognitive profile data for participant
     */
    public function test_score_list_section_renders_dynamic_cognitive_data(): void
    {
        $participant = Participant::query()->first();
        if (! $participant) {
            $this->markTestSkipped('No participant found');
        }

        Livewire::test(ScoreListSection::class, [
            'sectionCode' => 'cognitive',
            'participantId' => $participant->id,
        ])
            ->assertSee('IQ')
            ->assertSee('Profil Kognitif')
            ->assertSee('Skor Rata-Rata');
    }

    /**
     * Test score list section renders dynamic Big Five personality data for participant
     */
    public function test_score_list_section_renders_dynamic_big_five_data(): void
    {
        $participant = Participant::query()->first();
        if (! $participant) {
            $this->markTestSkipped('No participant found');
        }

        Livewire::test(ScoreListSection::class, [
            'sectionCode' => 'big_five',
            'participantId' => $participant->id,
        ])
            ->assertSee('Big Five')
            ->assertSee('Personality')
            ->assertSee('Openness to Experience')
            ->assertSee('Conscientiousness');
    }

    /**
     * Test disc profile renders dynamic data for participant
     */
    public function test_disc_profile_renders_dynamic_data(): void
    {
        $participant = Participant::query()->first();
        if (! $participant) {
            $this->markTestSkipped('No participant found');
        }

        Livewire::test(DiscProfile::class, [
            'participantId' => $participant->id,
        ])
            ->assertSee('Profil')
            ->assertSee('DISC')
            ->assertSee('Dominant Style');
    }

    /**
     * Test score list section renders dynamic Learning Agility data for participant
     */
    public function test_score_list_section_renders_dynamic_learning_agility_data(): void
    {
        $participant = Participant::query()->first();
        if (! $participant) {
            $this->markTestSkipped('No participant found');
        }

        Livewire::test(ScoreListSection::class, [
            'sectionCode' => 'learning_agility',
            'participantId' => $participant->id,
        ])
            ->assertSee('Learning Agility')
            ->assertSee('Mental Agility')
            ->assertSee('People Agility');
    }

    /**
     * Test score list section renders dynamic Leadership Potential data for participant
     */
    public function test_score_list_section_renders_dynamic_leadership_potential_data(): void
    {
        $participant = Participant::query()->first();
        if (! $participant) {
            $this->markTestSkipped('No participant found');
        }

        Livewire::test(ScoreListSection::class, [
            'sectionCode' => 'leadership_potential',
            'participantId' => $participant->id,
        ])
            ->assertSee('Leadership Potential')
            ->assertSee('Visioning')
            ->assertSee('Decision Making');
    }

    /**
     * Test score list section renders dynamic Values & Integrity data for participant
     */
    public function test_score_list_section_renders_dynamic_integrity_data(): void
    {
        $participant = Participant::query()->first();
        if (! $participant) {
            $this->markTestSkipped('No participant found');
        }

        Livewire::test(ScoreListSection::class, [
            'sectionCode' => 'integrity',
            'participantId' => $participant->id,
        ])
            ->assertSee('Values & Integrity')
            ->assertSee('Honesty & Transparency')
            ->assertSee('Ethical Compliance');
    }

    /**
     * Test performance dashboard renders dynamic data for participant
     */
    public function test_performance_dashboard_renders_dynamic_data(): void
    {
        $participant = Participant::with('performanceRecords')->first();
        if (! $participant) {
            $this->markTestSkipped('No participant found');
        }

        if ($participant->performanceRecords()->doesntExist()) {
            $participant->performanceRecords()->create([
                'year' => 2026,
                'kpi_score' => 98.20,
                'target_score' => 100.00,
                'benchmark_score' => 90.00,
                'performance_rating' => 'Sangat Baik',
                'kpi_breakdown' => [
                    ['metric' => 'Revenue', 'weight' => '30%', 'target' => '100%', 'actual' => '102%', 'status' => 'Exceeded', 'statusClass' => ''],
                ],
                'achievements' => ['Mencapai KPI 98.2%'],
            ]);
            $participant->load('performanceRecords');
        }

        Livewire::test(PerformanceDashboard::class, [
            'participantId' => $participant->id,
        ])
            ->assertSee('Performance')
            ->assertSee('Dashboard')
            ->assertSee('Analisa Kinerja');
    }

    /**
     * Test participant has performanceRecords relationship working
     */
    public function test_participant_has_performance_records_relationship(): void
    {
        $participant = Participant::query()->first();
        if (! $participant) {
            $this->markTestSkipped('No participant found');
        }

        if ($participant->performanceRecords()->doesntExist()) {
            $participant->performanceRecords()->create([
                'year' => 2026,
                'kpi_score' => 98.20,
                'target_score' => 100.00,
                'benchmark_score' => 90.00,
                'performance_rating' => 'Sangat Baik',
                'kpi_breakdown' => [],
                'achievements' => [],
            ]);
        }

        $this->assertTrue($participant->performanceRecords()->exists());
        $record = $participant->performanceRecords()->first();
        $this->assertNotNull($record->year);
        $this->assertIsFloat((float) $record->kpi_score);
    }

    /**
     * Test nine box matrix renders dynamic data for participant
     */
    public function test_nine_box_matrix_renders_dynamic_data(): void
    {
        $participant = Participant::with(['finalAssessment', 'performanceRecords'])->first();
        if (! $participant) {
            $this->markTestSkipped('No participant found');
        }

        Livewire::test(NineBoxMatrix::class, [
            'participantId' => $participant->id,
        ])
            ->assertSee('9-Box')
            ->assertSee('Talent Klasifikasi')
            ->assertSee('BOX')
            ->assertSee('Talent Placement');
    }

    /**
     * Test succession readiness renders dynamic data for participant
     */
    public function test_succession_readiness_renders_dynamic_data(): void
    {
        $participant = Participant::with(['positionFormation.template', 'finalAssessment', 'performanceRecords'])->first();
        if (! $participant) {
            $this->markTestSkipped('No participant found');
        }

        Livewire::test(SuccessionReadiness::class, [
            'participantId' => $participant->id,
        ])
            ->assertSee('Succession')
            ->assertSee('Readiness')
            ->assertSee('Jabatan Target Utama')
            ->assertSee('Horizon 1')
            ->assertSee('Siap Sekarang');
    }

    /**
     * Test participant profile renders complete demographics, employment, and assessment data
     */
    public function test_participant_profile_renders_complete_demographics_and_employment_data(): void
    {
        $participant = Participant::with(['positionFormation.template', 'assessmentEvent.institution', 'batch', 'institution'])->first();
        if (! $participant) {
            $this->markTestSkipped('No participant found');
        }

        Livewire::test(ParticipantProfile::class, [
            'participantId' => $participant->id,
        ])
            ->assertSee('Identitas')
            ->assertSee('Peserta')
            ->assertSee('Informasi Pribadi')
            ->assertSee('Profil Kepegawaian')
            ->assertSee('Konteks Asesmen')
            ->assertSee($participant->name)
            ->assertSee($participant->test_number);
    }
}
