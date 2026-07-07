<?php

declare(strict_types=1);

namespace Tests\Feature\Livewire;

use App\Livewire\HcaReport\HcaReportPage;
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
}
