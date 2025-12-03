<?php

namespace Tests\Feature\Livewire\Components;

use App\Livewire\Components\ParticipantSelector;
use App\Models\AssessmentEvent;
use App\Models\Participant;
use App\Models\PositionFormation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ParticipantSelectorTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test component renders without errors
     */
    public function test_component_renders_successfully(): void
    {
        Livewire::test(ParticipantSelector::class)
            ->assertStatus(200)
            ->assertSee('Cari peserta atau pilih dari daftar');
    }

    /**
     * Test auto-loads participants on initial load
     */
    public function test_auto_loads_participants_on_initial_load(): void
    {
        $event = AssessmentEvent::factory()->create();
        $position = PositionFormation::factory()->create();

        // Create 100 participants
        Participant::factory()
            ->count(100)
            ->create([
                'event_id' => $event->id,
                'position_formation_id' => $position->id,
            ]);

        session(['filter.event_code' => $event->code, 'filter.position_formation_id' => $position->id]);

        Livewire::test(ParticipantSelector::class)
            ->call('loadInitial')
            ->assertSet('availableParticipants', function ($participants) {
                return count($participants) === 50;
            })
            ->assertSet('hasMorePages', true);
    }

    /**
     * Test search returns limited results
     */
    public function test_search_limits_results_to_50(): void
    {
        $event = AssessmentEvent::factory()->create();
        $position = PositionFormation::factory()->create();

        // Create 100 participants
        Participant::factory()
            ->count(100)
            ->create([
                'event_id' => $event->id,
                'position_formation_id' => $position->id,
                'name' => 'Test Participant',
            ]);

        session(['filter.event_code' => $event->code, 'filter.position_formation_id' => $position->id]);

        Livewire::test(ParticipantSelector::class)
            ->set('search', 'Test')
            ->assertSet('availableParticipants', function ($participants) {
                return count($participants) <= 50;
            });
    }

    /**
     * Test search finds participants by name
     */
    public function test_search_finds_participants_by_name(): void
    {
        $event = AssessmentEvent::factory()->create();
        $position = PositionFormation::factory()->create();

        Participant::factory()->create([
            'event_id' => $event->id,
            'position_formation_id' => $position->id,
            'name' => 'John Doe',
            'test_number' => '001',
        ]);

        Participant::factory()->create([
            'event_id' => $event->id,
            'position_formation_id' => $position->id,
            'name' => 'Jane Smith',
            'test_number' => '002',
        ]);

        session(['filter.event_code' => $event->code, 'filter.position_formation_id' => $position->id]);

        Livewire::test(ParticipantSelector::class)
            ->set('search', 'John')
            ->assertSet('availableParticipants', function ($participants) {
                return count($participants) === 1 && $participants[0]['name'] === 'John Doe';
            });
    }

    /**
     * Test search finds participants by test number
     */
    public function test_search_finds_participants_by_test_number(): void
    {
        $event = AssessmentEvent::factory()->create();
        $position = PositionFormation::factory()->create();

        Participant::factory()->create([
            'event_id' => $event->id,
            'position_formation_id' => $position->id,
            'name' => 'John Doe',
            'test_number' => 'ABC123',
        ]);

        session(['filter.event_code' => $event->code, 'filter.position_formation_id' => $position->id]);

        Livewire::test(ParticipantSelector::class)
            ->set('search', 'ABC')
            ->assertSet('availableParticipants', function ($participants) {
                return count($participants) === 1 && $participants[0]['test_number'] === 'ABC123';
            });
    }

    /**
     * Test search works with single character
     */
    public function test_search_works_with_single_character(): void
    {
        $event = AssessmentEvent::factory()->create();
        $position = PositionFormation::factory()->create();

        Participant::factory()->create([
            'event_id' => $event->id,
            'position_formation_id' => $position->id,
            'name' => 'Alice',
        ]);

        Participant::factory()->create([
            'event_id' => $event->id,
            'position_formation_id' => $position->id,
            'name' => 'Bob',
        ]);

        session(['filter.event_code' => $event->code, 'filter.position_formation_id' => $position->id]);

        Livewire::test(ParticipantSelector::class)
            ->set('search', 'A')
            ->assertSet('availableParticipants', function ($participants) {
                return count($participants) === 1 && $participants[0]['name'] === 'Alice';
            });
    }

    /**
     * Test reset clears selection and search
     */
    public function test_reset_clears_selection_and_search(): void
    {
        $event = AssessmentEvent::factory()->create();
        $position = PositionFormation::factory()->create();

        $participant = Participant::factory()->create([
            'event_id' => $event->id,
            'position_formation_id' => $position->id,
            'name' => 'John Doe',
        ]);

        session(['filter.event_code' => $event->code, 'filter.position_formation_id' => $position->id]);

        Livewire::test(ParticipantSelector::class)
            ->set('search', 'John')
            ->set('participantId', $participant->id)
            ->call('resetParticipant')
            ->assertSet('participantId', null)
            ->assertSet('search', '')
            ->assertSet('availableParticipants', []);
    }

    /**
     * Test infinite scroll loads more participants
     */
    public function test_infinite_scroll_loads_more_participants(): void
    {
        $event = AssessmentEvent::factory()->create();
        $position = PositionFormation::factory()->create();

        // Create 120 participants (more than 2 pages of 50)
        Participant::factory()
            ->count(120)
            ->create([
                'event_id' => $event->id,
                'position_formation_id' => $position->id,
                'name' => 'Test Participant',
            ]);

        session(['filter.event_code' => $event->code, 'filter.position_formation_id' => $position->id]);

        $component = Livewire::test(ParticipantSelector::class)
            ->set('search', 'Test')
            ->assertSet('availableParticipants', function ($participants) {
                return count($participants) === 50;
            })
            ->assertSet('hasMorePages', true)
            ->assertSet('page', 1);

        // Load more (page 2)
        $component->call('loadMore')
            ->assertSet('availableParticipants', function ($participants) {
                return count($participants) === 100;
            })
            ->assertSet('hasMorePages', true)
            ->assertSet('page', 2);

        // Load more (page 3)
        $component->call('loadMore')
            ->assertSet('availableParticipants', function ($participants) {
                return count($participants) === 120;
            })
            ->assertSet('hasMorePages', false)
            ->assertSet('page', 3);
    }

    /**
     * Test search reset pagination
     */
    public function test_search_resets_pagination(): void
    {
        $event = AssessmentEvent::factory()->create();
        $position = PositionFormation::factory()->create();

        Participant::factory()
            ->count(100)
            ->create([
                'event_id' => $event->id,
                'position_formation_id' => $position->id,
                'name' => 'Test Participant',
            ]);

        session(['filter.event_code' => $event->code, 'filter.position_formation_id' => $position->id]);

        Livewire::test(ParticipantSelector::class)
            ->set('search', 'Test')
            ->assertSet('page', 1)
            ->call('loadMore')
            ->assertSet('page', 2)
            // Change search query - should reset to page 1
            ->set('search', 'Tes')
            ->assertSet('page', 1)
            ->assertSet('availableParticipants', function ($participants) {
                return count($participants) <= 50;
            });
    }
}
