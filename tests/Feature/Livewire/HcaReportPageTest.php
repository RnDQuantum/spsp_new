<?php

declare(strict_types=1);

namespace Tests\Feature\Livewire;

use App\Livewire\Pages\HCA\HcaReportPage;
use App\Livewire\Pages\HCA\Sections\ExecutiveSummary;
use App\Livewire\Pages\HCA\Sections\ScoreListSection;
use App\Livewire\Pages\HCA\Sections\TimelineSection;
use App\Models\Participant;
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
            ->assertSee('Skor Rata-Rata');
    }
}
