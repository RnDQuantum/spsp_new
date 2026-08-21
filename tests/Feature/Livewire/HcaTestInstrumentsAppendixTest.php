<?php

declare(strict_types=1);

namespace Tests\Feature\Livewire;

use App\Livewire\Pages\HCA\HcaReportPage;
use App\Livewire\Pages\HCA\Sections\TestInstrumentsAppendix;
use App\Models\Participant;
use Livewire\Livewire;
use Tests\TestCase;

class HcaTestInstrumentsAppendixTest extends TestCase
{
    /**
     * Test appendix section component renders successfully with participant
     */
    public function test_appendix_component_renders_successfully(): void
    {
        $participant = Participant::with(['assessmentEvent', 'positionFormation'])->first();

        Livewire::test(TestInstrumentsAppendix::class, [
            'participantId' => $participant?->id,
        ])
            ->assertStatus(200)
            ->assertSee('Laporan Hasil Alat Tes Psikometri')
            ->assertSee('Level 1 Evidence Layer');
    }

    /**
     * Test switching categories updates selectedCategory state
     */
    public function test_category_filtering_works_as_expected(): void
    {
        $participant = Participant::with(['assessmentEvent', 'positionFormation'])->first();

        Livewire::test(TestInstrumentsAppendix::class, [
            'participantId' => $participant?->id,
        ])
            ->assertSet('selectedCategory', 'all')
            ->call('setCategory', 'cognitive')
            ->assertSet('selectedCategory', 'cognitive')
            ->call('setCategory', 'personality')
            ->assertSet('selectedCategory', 'personality')
            ->call('setCategory', 'work_attitude')
            ->assertSet('selectedCategory', 'work_attitude')
            ->call('setCategory', 'clinical')
            ->assertSet('selectedCategory', 'clinical')
            ->call('setCategory', 'emotional_interest')
            ->assertSet('selectedCategory', 'emotional_interest');
    }

    /**
     * Test main HcaReportPage can navigate to test_instruments_appendix section
     */
    public function test_hca_report_page_can_switch_to_appendix(): void
    {
        $participant = Participant::first();

        Livewire::test(HcaReportPage::class, [
            'participant' => $participant?->id,
        ])
            ->assertSeeLivewire(TestInstrumentsAppendix::class)
            ->call('setSection', 'test_instruments_appendix')
            ->assertSet('activeSection', 'test_instruments_appendix');
    }

    /**
     * Test PDF report view includes appendix section
     */
    public function test_pdf_report_view_includes_appendix(): void
    {
        $participant = Participant::query()->first() ?? Participant::factory()->create();

        $view = $this->view('pdf.hca.report', [
            'participant' => $participant,
        ]);

        $view->assertSeeLivewire(TestInstrumentsAppendix::class);
    }
}
