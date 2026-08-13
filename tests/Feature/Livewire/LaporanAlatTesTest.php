<?php

declare(strict_types=1);

namespace Tests\Feature\Livewire;

use App\Livewire\Pages\LaporanAlatTes\LaporanAlatTes;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class LaporanAlatTesTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_renders_successfully(): void
    {
        Livewire::test(LaporanAlatTes::class)
            ->assertStatus(200);
    }

    #[Test]
    public function it_handles_event_selected_and_position_selected_events_without_resolution_exceptions(): void
    {
        Livewire::test(LaporanAlatTes::class)
            ->dispatch('event-selected', eventCode: 'EVT-001')
            ->assertStatus(200)
            ->dispatch('position-selected', positionFormationId: 1)
            ->assertStatus(200);
    }
}
