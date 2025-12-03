<?php

declare(strict_types=1);

namespace Tests\Feature\Livewire\DynamicStandard;

use App\Livewire\Components\SelectiveAspectsModal;
use App\Models\Aspect;
use App\Models\AssessmentTemplate;
use App\Models\CategoryType;
use App\Models\Institution;
use App\Models\SubAspect;
use App\Models\User;
use App\Services\DynamicStandardService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * SelectiveAspectsModal Livewire Component Tests
 *
 * Tests for bulk aspect/sub-aspect selection and weight editing modal.
 * This is a critical component for session adjustment functionality.
 *
 * Test Coverage:
 * - ✅ Modal opens with current session state (2 tests)
 * - ✅ Select/deselect aspects (2 tests)
 * - ✅ Edit aspect weights (1 test)
 * - ✅ Toggle sub-aspects for Potensi (1 test)
 * - ✅ Validates total weight = 100% (1 test)
 * - ✅ Validates minimum 3 active aspects (1 test)
 * - ✅ Saves to session via DynamicStandardService (1 test)
 * - ✅ Auto-distribute weights functionality (1 test)
 *
 * TOTAL: 10 tests
 *
 * @see \App\Livewire\Components\SelectiveAspectsModal
 * @see docs/LIVEWIRE_TESTING_GUIDE.md
 */
class SelectiveAspectsModalTest extends TestCase
{
    use RefreshDatabase;

    private DynamicStandardService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(DynamicStandardService::class);

        // Clear cache between tests
        \App\Services\Cache\AspectCacheService::clearCache();
    }

    // ========================================
    // HELPER METHODS
    // ========================================

    private function createTemplate(): AssessmentTemplate
    {
        return AssessmentTemplate::factory()->create();
    }

    private function createCategory(int $templateId, string $code): CategoryType
    {
        return CategoryType::create([
            'template_id' => $templateId,
            'code' => $code,
            'name' => ucfirst($code),
            'weight_percentage' => 50,
            'description' => "Category {$code}",
            'order' => 1,
        ]);
    }

    private function createAspect(int $templateId, int $categoryId, string $code, int $weight = 10, float $rating = 3.0): Aspect
    {
        return Aspect::create([
            'template_id' => $templateId,
            'category_type_id' => $categoryId,
            'code' => $code,
            'name' => "Aspect {$code}",
            'weight_percentage' => $weight,
            'standard_rating' => $rating,
            'order' => 1,
        ]);
    }

    private function createSubAspect(int $aspectId, string $code, float $rating = 3.0): SubAspect
    {
        return SubAspect::create([
            'aspect_id' => $aspectId,
            'code' => $code,
            'name' => "Sub-Aspect {$code}",
            'standard_rating' => $rating,
            'order' => 1,
        ]);
    }

    private function createInstitution(): Institution
    {
        return Institution::factory()->create();
    }

    // ========================================
    // TEST: Modal Opens with Current Session State
    // ========================================

    public function test_modal_opens_with_session_state_for_potensi(): void
    {
        // Arrange: Create template with Potensi category and aspects
        $user = User::factory()->create();
        $template = $this->createTemplate();
        $category = $this->createCategory($template->id, 'potensi');
        $aspect1 = $this->createAspect($template->id, $category->id, 'asp_01', 20);
        $aspect2 = $this->createAspect($template->id, $category->id, 'asp_02', 30);
        $subAspect1 = $this->createSubAspect($aspect1->id, 'sub_01');
        $subAspect2 = $this->createSubAspect($aspect1->id, 'sub_02');

        // Set session adjustments
        $this->service->saveBulkSelection($template->id, [
            'active_aspects' => [
                'asp_01' => true,
                'asp_02' => false,
            ],
            'aspect_weights' => [
                'asp_01' => 25,
            ],
            'active_sub_aspects' => [
                'sub_01' => true,
                'sub_02' => false,
            ],
        ]);

        // Act & Assert: Open modal and verify state loaded
        Livewire::actingAs($user)
            ->test(SelectiveAspectsModal::class)
            ->dispatch('openSelectionModal', templateId: $template->id, categoryCode: 'potensi')
            ->assertSet('show', true)
            ->assertSet('dataLoaded', true)
            ->assertSet('templateId', $template->id)
            ->assertSet('categoryCode', 'potensi')
            ->assertSet('selectedAspects.asp_01', true)
            ->assertSet('selectedAspects.asp_02', false)
            ->assertSet('aspectWeights.asp_01', 25)
            ->assertSet('selectedSubAspects.asp_01.sub_01', true)
            ->assertSet('selectedSubAspects.asp_01.sub_02', false);
    }

    public function test_modal_opens_with_session_state_for_kompetensi(): void
    {
        // Arrange: Create template with Kompetensi category (no sub-aspects)
        $user = User::factory()->create();
        $template = $this->createTemplate();
        $category = $this->createCategory($template->id, 'kompetensi');
        $aspect1 = $this->createAspect($template->id, $category->id, 'asp_k01', 40);
        $aspect2 = $this->createAspect($template->id, $category->id, 'asp_k02', 60);

        // Set session adjustments
        $this->service->saveBulkSelection($template->id, [
            'active_aspects' => [
                'asp_k01' => true,
                'asp_k02' => true,
            ],
            'aspect_weights' => [
                'asp_k01' => 35,
            ],
        ]);

        // Act & Assert: Open modal and verify state loaded
        Livewire::actingAs($user)
            ->test(SelectiveAspectsModal::class)
            ->dispatch('openSelectionModal', templateId: $template->id, categoryCode: 'kompetensi')
            ->assertSet('show', true)
            ->assertSet('dataLoaded', true)
            ->assertSet('templateId', $template->id)
            ->assertSet('categoryCode', 'kompetensi')
            ->assertSet('selectedAspects.asp_k01', true)
            ->assertSet('selectedAspects.asp_k02', true)
            ->assertSet('aspectWeights.asp_k01', 35);
    }

    // ========================================
    // TEST: Select/Deselect Aspects
    // ========================================

    public function test_selecting_aspect_auto_checks_first_sub_aspect_for_potensi(): void
    {
        // Arrange: Create template with unchecked aspect
        $user = User::factory()->create();
        $template = $this->createTemplate();
        $category = $this->createCategory($template->id, 'potensi');
        $aspect = $this->createAspect($template->id, $category->id, 'asp_01', 20);
        $subAspect1 = $this->createSubAspect($aspect->id, 'sub_01');
        $subAspect2 = $this->createSubAspect($aspect->id, 'sub_02');

        // Set aspect as inactive
        $this->service->saveBulkSelection($template->id, [
            'active_aspects' => [
                'asp_01' => false,
            ],
        ]);

        // Act: Open modal and select aspect
        $component = Livewire::actingAs($user)
            ->test(SelectiveAspectsModal::class)
            ->dispatch('openSelectionModal', templateId: $template->id, categoryCode: 'potensi')
            ->set('selectedAspects.asp_01', true);

        // Assert: First sub-aspect auto-checked
        $component->assertSet('selectedSubAspects.asp_01.sub_01', true);
    }

    public function test_deselecting_aspect_unchecks_all_sub_aspects_and_zeros_weight(): void
    {
        // Arrange: Create template with checked aspect
        $user = User::factory()->create();
        $template = $this->createTemplate();
        $category = $this->createCategory($template->id, 'potensi');
        $aspect = $this->createAspect($template->id, $category->id, 'asp_01', 20);
        $subAspect1 = $this->createSubAspect($aspect->id, 'sub_01');
        $subAspect2 = $this->createSubAspect($aspect->id, 'sub_02');

        // Set aspect and sub-aspects as active
        $this->service->saveBulkSelection($template->id, [
            'active_aspects' => [
                'asp_01' => true,
            ],
            'aspect_weights' => [
                'asp_01' => 25,
            ],
            'active_sub_aspects' => [
                'sub_01' => true,
                'sub_02' => true,
            ],
        ]);

        // Act: Open modal and deselect aspect
        $component = Livewire::actingAs($user)
            ->test(SelectiveAspectsModal::class)
            ->dispatch('openSelectionModal', templateId: $template->id, categoryCode: 'potensi')
            ->set('selectedAspects.asp_01', false);

        // Assert: All sub-aspects unchecked and weight = 0
        $component
            ->assertSet('selectedSubAspects.asp_01.sub_01', false)
            ->assertSet('selectedSubAspects.asp_01.sub_02', false)
            ->assertSet('aspectWeights.asp_01', 0);
    }

    // ========================================
    // TEST: Edit Aspect Weights
    // ========================================

    public function test_editing_aspect_weights_updates_state(): void
    {
        // Arrange: Create template with aspects
        $user = User::factory()->create();
        $template = $this->createTemplate();
        $category = $this->createCategory($template->id, 'potensi');
        $aspect1 = $this->createAspect($template->id, $category->id, 'asp_01', 20);
        $aspect2 = $this->createAspect($template->id, $category->id, 'asp_02', 30);
        $aspect3 = $this->createAspect($template->id, $category->id, 'asp_03', 50);

        // Act: Open modal and edit weights
        $component = Livewire::actingAs($user)
            ->test(SelectiveAspectsModal::class)
            ->dispatch('openSelectionModal', templateId: $template->id, categoryCode: 'potensi')
            ->set('aspectWeights.asp_01', 40)
            ->set('aspectWeights.asp_02', 35)
            ->set('aspectWeights.asp_03', 25);

        // Assert: Weights updated
        $component
            ->assertSet('aspectWeights.asp_01', 40)
            ->assertSet('aspectWeights.asp_02', 35)
            ->assertSet('aspectWeights.asp_03', 25);
    }

    // ========================================
    // TEST: Toggle Sub-Aspects (Potensi Only)
    // ========================================

    public function test_toggle_sub_aspects_for_potensi_category(): void
    {
        // Arrange: Create template with Potensi aspect and sub-aspects
        $user = User::factory()->create();
        $template = $this->createTemplate();
        $category = $this->createCategory($template->id, 'potensi');
        $aspect = $this->createAspect($template->id, $category->id, 'asp_01', 20);
        $subAspect1 = $this->createSubAspect($aspect->id, 'sub_01');
        $subAspect2 = $this->createSubAspect($aspect->id, 'sub_02');
        $subAspect3 = $this->createSubAspect($aspect->id, 'sub_03');

        // Set initial state
        $this->service->saveBulkSelection($template->id, [
            'active_sub_aspects' => [
                'sub_01' => true,
                'sub_02' => false,
                'sub_03' => true,
            ],
        ]);

        // Act: Open modal and toggle sub-aspects
        $component = Livewire::actingAs($user)
            ->test(SelectiveAspectsModal::class)
            ->dispatch('openSelectionModal', templateId: $template->id, categoryCode: 'potensi')
            ->set('selectedSubAspects.asp_01.sub_01', false)
            ->set('selectedSubAspects.asp_01.sub_02', true);

        // Assert: Sub-aspects toggled
        $component
            ->assertSet('selectedSubAspects.asp_01.sub_01', false)
            ->assertSet('selectedSubAspects.asp_01.sub_02', true)
            ->assertSet('selectedSubAspects.asp_01.sub_03', true);
    }

    // ========================================
    // TEST: Validates Total Weight = 100%
    // ========================================

    public function test_validates_total_weight_must_equal_100_percent(): void
    {
        // Arrange: Create template with 3 aspects
        $user = User::factory()->create();
        $template = $this->createTemplate();
        $category = $this->createCategory($template->id, 'potensi');
        $aspect1 = $this->createAspect($template->id, $category->id, 'asp_01', 20);
        $aspect2 = $this->createAspect($template->id, $category->id, 'asp_02', 30);
        $aspect3 = $this->createAspect($template->id, $category->id, 'asp_03', 50);

        // Act: Open modal and set weights that don't equal 100%
        $component = Livewire::actingAs($user)
            ->test(SelectiveAspectsModal::class)
            ->dispatch('openSelectionModal', templateId: $template->id, categoryCode: 'potensi')
            ->set('aspectWeights.asp_01', 40)
            ->set('aspectWeights.asp_02', 30)
            ->set('aspectWeights.asp_03', 20); // Total = 90%, not 100%

        // Assert: Validation fails
        $component
            ->call('applySelection')
            ->assertDispatched('show-validation-error');

        // Assert: Session NOT updated (validation failed)
        $this->assertNotEquals(40, session("standard_adjustment.{$template->id}.aspect_weights.asp_01"));
    }

    // ========================================
    // TEST: Validates Minimum 3 Active Aspects
    // ========================================

    public function test_validates_minimum_3_active_aspects_required(): void
    {
        // Arrange: Create template with 4 aspects
        $user = User::factory()->create();
        $template = $this->createTemplate();
        $category = $this->createCategory($template->id, 'potensi');
        $aspect1 = $this->createAspect($template->id, $category->id, 'asp_01', 50);
        $aspect2 = $this->createAspect($template->id, $category->id, 'asp_02', 50);
        $aspect3 = $this->createAspect($template->id, $category->id, 'asp_03', 0);
        $aspect4 = $this->createAspect($template->id, $category->id, 'asp_04', 0);

        // Act: Open modal and deselect to have only 2 active aspects
        $component = Livewire::actingAs($user)
            ->test(SelectiveAspectsModal::class)
            ->dispatch('openSelectionModal', templateId: $template->id, categoryCode: 'potensi')
            ->set('selectedAspects.asp_01', true)
            ->set('selectedAspects.asp_02', true)
            ->set('selectedAspects.asp_03', false)
            ->set('selectedAspects.asp_04', false)
            ->set('aspectWeights.asp_01', 50)
            ->set('aspectWeights.asp_02', 50);

        // Assert: Validation fails (only 2 active, need 3 minimum)
        $component
            ->call('applySelection')
            ->assertDispatched('show-validation-error');
    }

    // ========================================
    // TEST: Saves to Session via DynamicStandardService
    // ========================================

    public function test_applies_selection_saves_to_session_via_service(): void
    {
        // Arrange: Create template with Potensi aspects and sub-aspects
        $user = User::factory()->create();
        $template = $this->createTemplate();
        $category = $this->createCategory($template->id, 'potensi');
        $aspect1 = $this->createAspect($template->id, $category->id, 'asp_01', 20);
        $aspect2 = $this->createAspect($template->id, $category->id, 'asp_02', 30);
        $aspect3 = $this->createAspect($template->id, $category->id, 'asp_03', 50);

        // Create sub-aspects for all aspects (required for Potensi)
        $subAspect1 = $this->createSubAspect($aspect1->id, 'sub_01');
        $subAspect2 = $this->createSubAspect($aspect1->id, 'sub_02');
        $subAspect3 = $this->createSubAspect($aspect2->id, 'sub_03');
        $subAspect4 = $this->createSubAspect($aspect3->id, 'sub_04');

        // Act: Open modal, adjust values, and apply
        Livewire::actingAs($user)
            ->test(SelectiveAspectsModal::class)
            ->dispatch('openSelectionModal', templateId: $template->id, categoryCode: 'potensi')
            ->set('selectedAspects.asp_01', true)
            ->set('selectedAspects.asp_02', true)
            ->set('selectedAspects.asp_03', true)
            ->set('aspectWeights.asp_01', 40)
            ->set('aspectWeights.asp_02', 35)
            ->set('aspectWeights.asp_03', 25)
            ->set('selectedSubAspects.asp_01.sub_01', true)
            ->set('selectedSubAspects.asp_01.sub_02', false)
            ->set('selectedSubAspects.asp_02.sub_03', true)
            ->set('selectedSubAspects.asp_03.sub_04', true)
            ->call('applySelection')
            ->assertDispatched('standard-adjusted', templateId: $template->id)
            ->assertDispatched('show-success')
            ->assertSet('show', false);

        // Assert: Session updated via DynamicStandardService
        $this->assertEquals(40, $this->service->getAspectWeight($template->id, 'asp_01'));
        $this->assertEquals(35, $this->service->getAspectWeight($template->id, 'asp_02'));
        $this->assertEquals(25, $this->service->getAspectWeight($template->id, 'asp_03'));
        $this->assertTrue($this->service->isAspectActive($template->id, 'asp_01'));
        $this->assertTrue($this->service->isSubAspectActive($template->id, 'sub_01'));
        $this->assertFalse($this->service->isSubAspectActive($template->id, 'sub_02'));
        $this->assertTrue($this->service->isSubAspectActive($template->id, 'sub_03'));
        $this->assertTrue($this->service->isSubAspectActive($template->id, 'sub_04'));
    }

    // ========================================
    // TEST: Auto-Distribute Weights
    // ========================================

    public function test_auto_distribute_weights_divides_100_evenly_among_active_aspects(): void
    {
        // Arrange: Create template with 3 aspects
        $user = User::factory()->create();
        $template = $this->createTemplate();
        $category = $this->createCategory($template->id, 'potensi');
        $aspect1 = $this->createAspect($template->id, $category->id, 'asp_01', 20);
        $aspect2 = $this->createAspect($template->id, $category->id, 'asp_02', 30);
        $aspect3 = $this->createAspect($template->id, $category->id, 'asp_03', 50);

        // Act: Open modal and auto-distribute
        $component = Livewire::actingAs($user)
            ->test(SelectiveAspectsModal::class)
            ->dispatch('openSelectionModal', templateId: $template->id, categoryCode: 'potensi')
            ->set('selectedAspects.asp_01', true)
            ->set('selectedAspects.asp_02', true)
            ->set('selectedAspects.asp_03', true)
            ->call('autoDistributeWeights');

        // Assert: Weights distributed evenly (33 + 33 + 34 = 100)
        $component
            ->assertSet('aspectWeights.asp_01', 34) // Gets remainder
            ->assertSet('aspectWeights.asp_02', 33)
            ->assertSet('aspectWeights.asp_03', 33);

        // Verify total = 100%
        $this->assertEquals(100, $component->get('totalWeight'));
    }

    // ========================================
    // TEST: Close Modal Without Saving
    // ========================================

    public function test_close_modal_without_saving_does_not_update_session(): void
    {
        // Arrange: Create template with aspects
        $user = User::factory()->create();
        $template = $this->createTemplate();
        $category = $this->createCategory($template->id, 'potensi');
        $aspect1 = $this->createAspect($template->id, $category->id, 'asp_01', 20);

        // Set initial session value
        $this->service->saveAspectWeight($template->id, 'asp_01', 20);

        // Act: Open modal, change value, but close without saving
        Livewire::actingAs($user)
            ->test(SelectiveAspectsModal::class)
            ->dispatch('openSelectionModal', templateId: $template->id, categoryCode: 'potensi')
            ->set('aspectWeights.asp_01', 50)
            ->call('close')
            ->assertSet('show', false);

        // Assert: Session NOT updated (still original value)
        $this->assertEquals(20, $this->service->getAspectWeight($template->id, 'asp_01'));
    }
}
