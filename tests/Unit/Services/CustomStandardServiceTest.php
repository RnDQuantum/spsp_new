<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Models\Aspect;
use App\Models\AssessmentEvent;
use App\Models\AssessmentTemplate;
use App\Models\CategoryType;
use App\Models\CustomStandard;
use App\Models\Institution;
use App\Models\PositionFormation;
use App\Models\SubAspect;
use App\Models\User;
use App\Services\CustomStandardService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Session;
use Tests\TestCase;

/**
 * CustomStandardService Unit Tests
 *
 * Tests all public methods of CustomStandardService including:
 * - Query methods (getForInstitution, getAllForInstitution, getAvailableTemplatesForInstitution)
 * - CRUD operations (create, update, delete)
 * - Template defaults (getTemplateDefaults with data-driven logic)
 * - Session management (select, getSelected, getSelectedStandard, clearSelection)
 * - Getter methods (weights, ratings, active status)
 * - Validation (validate, isCodeUnique)
 *
 * Total: 65 tests covering all 20 public methods
 */
class CustomStandardServiceTest extends TestCase
{
    use RefreshDatabase;

    private CustomStandardService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new CustomStandardService;
    }

    // ==========================================
    // PHASE 1: SERVICE INITIALIZATION (1 test)
    // ==========================================

    public function test_service_can_be_instantiated(): void
    {
        $this->assertInstanceOf(CustomStandardService::class, $this->service);
    }

    // ==========================================
    // PHASE 2: QUERY METHODS (10 tests)
    // ==========================================

    public function test_get_for_institution_returns_custom_standards_for_specific_template(): void
    {
        $institution = Institution::factory()->create();
        $template = AssessmentTemplate::factory()->create();

        $standard1 = CustomStandard::create($this->makeStandardData($institution->id, $template->id, 'STD-001'));
        $standard2 = CustomStandard::create($this->makeStandardData($institution->id, $template->id, 'STD-002'));

        // Different template - should not appear
        $otherTemplate = AssessmentTemplate::factory()->create();
        CustomStandard::create($this->makeStandardData($institution->id, $otherTemplate->id, 'STD-003'));

        $result = $this->service->getForInstitution($institution->id, $template->id);

        $this->assertCount(2, $result);
        $this->assertTrue($result->contains($standard1));
        $this->assertTrue($result->contains($standard2));
    }

    public function test_get_for_institution_only_returns_active_standards(): void
    {
        $institution = Institution::factory()->create();
        $template = AssessmentTemplate::factory()->create();

        $activeStandard = CustomStandard::create($this->makeStandardData($institution->id, $template->id, 'STD-001'));
        $inactiveStandard = CustomStandard::create(array_merge(
            $this->makeStandardData($institution->id, $template->id, 'STD-002'),
            ['is_active' => false]
        ));

        $result = $this->service->getForInstitution($institution->id, $template->id);

        $this->assertCount(1, $result);
        $this->assertTrue($result->contains($activeStandard));
        $this->assertFalse($result->contains($inactiveStandard));
    }

    public function test_get_for_institution_orders_by_name(): void
    {
        $institution = Institution::factory()->create();
        $template = AssessmentTemplate::factory()->create();

        CustomStandard::create($this->makeStandardData($institution->id, $template->id, 'STD-C', 'Zebra Standard'));
        CustomStandard::create($this->makeStandardData($institution->id, $template->id, 'STD-A', 'Alpha Standard'));
        CustomStandard::create($this->makeStandardData($institution->id, $template->id, 'STD-B', 'Beta Standard'));

        $result = $this->service->getForInstitution($institution->id, $template->id);

        $this->assertEquals('Alpha Standard', $result->first()->name);
        $this->assertEquals('Beta Standard', $result->get(1)->name);
        $this->assertEquals('Zebra Standard', $result->last()->name);
    }

    public function test_get_all_for_institution_returns_standards_across_all_templates(): void
    {
        $institution = Institution::factory()->create();
        $template1 = AssessmentTemplate::factory()->create();
        $template2 = AssessmentTemplate::factory()->create();

        $standard1 = CustomStandard::create($this->makeStandardData($institution->id, $template1->id, 'STD-001'));
        $standard2 = CustomStandard::create($this->makeStandardData($institution->id, $template2->id, 'STD-002'));

        // Different institution - should not appear
        $otherInstitution = Institution::factory()->create();
        CustomStandard::create($this->makeStandardData($otherInstitution->id, $template1->id, 'STD-003'));

        $result = $this->service->getAllForInstitution($institution->id);

        $this->assertCount(2, $result);
        $this->assertTrue($result->contains($standard1));
        $this->assertTrue($result->contains($standard2));
    }

    public function test_get_all_for_institution_eager_loads_template_relationship(): void
    {
        $institution = Institution::factory()->create();
        $template = AssessmentTemplate::factory()->create(['name' => 'General Psychology']);

        CustomStandard::create($this->makeStandardData($institution->id, $template->id, 'STD-001'));

        $result = $this->service->getAllForInstitution($institution->id);

        $this->assertTrue($result->first()->relationLoaded('template'));
        $this->assertEquals('General Psychology', $result->first()->template->name);
    }

    public function test_get_available_templates_for_institution_returns_only_used_templates(): void
    {
        $institution = Institution::factory()->create();

        // Create template with event and position formation
        $usedTemplate = AssessmentTemplate::factory()->create(['name' => 'Used Template']);
        $event = AssessmentEvent::factory()->create(['institution_id' => $institution->id]);
        PositionFormation::factory()->create([
            'event_id' => $event->id,
            'template_id' => $usedTemplate->id,
        ]);

        // Create unused template (no position formations)
        $unusedTemplate = AssessmentTemplate::factory()->create(['name' => 'Unused Template']);

        $result = $this->service->getAvailableTemplatesForInstitution($institution->id);

        $this->assertCount(1, $result);
        $this->assertTrue($result->contains($usedTemplate));
        $this->assertFalse($result->contains($unusedTemplate));
    }

    public function test_get_available_templates_orders_by_name(): void
    {
        $institution = Institution::factory()->create();
        $event = AssessmentEvent::factory()->create(['institution_id' => $institution->id]);

        $template1 = AssessmentTemplate::factory()->create(['name' => 'Zebra Template']);
        $template2 = AssessmentTemplate::factory()->create(['name' => 'Alpha Template']);

        PositionFormation::factory()->create(['event_id' => $event->id, 'template_id' => $template1->id]);
        PositionFormation::factory()->create(['event_id' => $event->id, 'template_id' => $template2->id]);

        $result = $this->service->getAvailableTemplatesForInstitution($institution->id);

        $this->assertEquals('Alpha Template', $result->first()->name);
        $this->assertEquals('Zebra Template', $result->last()->name);
    }

    public function test_get_available_templates_removes_duplicates(): void
    {
        $institution = Institution::factory()->create();
        $template = AssessmentTemplate::factory()->create();
        $event = AssessmentEvent::factory()->create(['institution_id' => $institution->id]);

        // Create multiple position formations with same template
        PositionFormation::factory()->create(['event_id' => $event->id, 'template_id' => $template->id]);
        PositionFormation::factory()->create(['event_id' => $event->id, 'template_id' => $template->id]);

        $result = $this->service->getAvailableTemplatesForInstitution($institution->id);

        $this->assertCount(1, $result);
    }

    public function test_get_for_institution_returns_empty_collection_when_no_standards(): void
    {
        $institution = Institution::factory()->create();
        $template = AssessmentTemplate::factory()->create();

        $result = $this->service->getForInstitution($institution->id, $template->id);

        $this->assertCount(0, $result);
        $this->assertInstanceOf(\Illuminate\Support\Collection::class, $result);
    }

    // ==========================================
    // PHASE 3: CRUD OPERATIONS (12 tests)
    // ==========================================

    public function test_create_stores_custom_standard_with_all_data(): void
    {
        $institution = Institution::factory()->create();
        $template = AssessmentTemplate::factory()->create();
        $user = User::factory()->create();

        $data = [
            'institution_id' => $institution->id,
            'template_id' => $template->id,
            'code' => 'STD-001',
            'name' => 'Custom Standard A',
            'description' => 'Test description',
            'category_weights' => ['potensi' => 40, 'kompetensi' => 60],
            'aspect_configs' => ['asp_01' => ['weight' => 20, 'active' => true]],
            'sub_aspect_configs' => ['sub_01' => ['rating' => 4, 'active' => true]],
            'created_by' => $user->id,
        ];

        $result = $this->service->create($data);

        $this->assertInstanceOf(CustomStandard::class, $result);
        $this->assertDatabaseHas('custom_standards', [
            'institution_id' => $institution->id,
            'template_id' => $template->id,
            'code' => 'STD-001',
            'name' => 'Custom Standard A',
            'description' => 'Test description',
            'created_by' => $user->id,
        ]);

        $this->assertEquals(['potensi' => 40, 'kompetensi' => 60], $result->category_weights);
        $this->assertEquals(['asp_01' => ['weight' => 20, 'active' => true]], $result->aspect_configs);
    }

    public function test_create_uses_auth_id_when_created_by_not_provided(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $this->actingAs($user);

        $institution = Institution::factory()->create();
        $template = AssessmentTemplate::factory()->create();

        $data = [
            'institution_id' => $institution->id,
            'template_id' => $template->id,
            'code' => 'STD-001',
            'name' => 'Test Standard',
            'category_weights' => ['potensi' => 50, 'kompetensi' => 50],
            'aspect_configs' => [],
            'sub_aspect_configs' => [],
        ];

        $result = $this->service->create($data);

        $this->assertEquals(auth()->id(), $result->created_by);
    }

    public function test_create_handles_null_description(): void
    {
        $institution = Institution::factory()->create();
        $template = AssessmentTemplate::factory()->create();

        $data = [
            'institution_id' => $institution->id,
            'template_id' => $template->id,
            'code' => 'STD-001',
            'name' => 'Test Standard',
            'category_weights' => ['potensi' => 50, 'kompetensi' => 50],
            'aspect_configs' => [],
            'sub_aspect_configs' => [],
        ];

        $result = $this->service->create($data);

        $this->assertNull($result->description);
    }

    public function test_update_modifies_custom_standard_with_provided_data(): void
    {
        $institution = Institution::factory()->create();
        $template = AssessmentTemplate::factory()->create();

        $standard = CustomStandard::create($this->makeStandardData($institution->id, $template->id, 'STD-001', 'Original Name'));

        $updateData = [
            'code' => 'STD-002',
            'name' => 'Updated Name',
            'description' => 'Updated description',
            'category_weights' => ['potensi' => 30, 'kompetensi' => 70],
        ];

        $result = $this->service->update($standard, $updateData);

        $this->assertEquals('STD-002', $result->code);
        $this->assertEquals('Updated Name', $result->name);
        $this->assertEquals('Updated description', $result->description);
        $this->assertEquals(['potensi' => 30, 'kompetensi' => 70], $result->category_weights);
    }

    public function test_update_keeps_original_values_when_not_provided(): void
    {
        $institution = Institution::factory()->create();
        $template = AssessmentTemplate::factory()->create();

        $originalData = $this->makeStandardData($institution->id, $template->id, 'STD-001', 'Original Name');
        $standard = CustomStandard::create($originalData);

        $updateData = ['name' => 'Updated Name Only'];

        $result = $this->service->update($standard, $updateData);

        $this->assertEquals('STD-001', $result->code); // unchanged
        $this->assertEquals('Updated Name Only', $result->name); // changed
        $this->assertEquals($originalData['category_weights'], $result->category_weights); // unchanged
    }

    public function test_update_returns_fresh_model_instance(): void
    {
        $institution = Institution::factory()->create();
        $template = AssessmentTemplate::factory()->create();

        $standard = CustomStandard::create($this->makeStandardData($institution->id, $template->id, 'STD-001'));

        $result = $this->service->update($standard, ['name' => 'New Name']);

        $this->assertNotSame($standard, $result);
        $this->assertEquals('New Name', $result->name);
    }

    public function test_delete_removes_custom_standard(): void
    {
        $institution = Institution::factory()->create();
        $template = AssessmentTemplate::factory()->create();

        $standard = CustomStandard::create($this->makeStandardData($institution->id, $template->id, 'STD-001'));

        $result = $this->service->delete($standard);

        $this->assertTrue($result);
        $this->assertDatabaseMissing('custom_standards', ['id' => $standard->id]);
    }

    public function test_delete_returns_false_when_deletion_fails(): void
    {
        $institution = Institution::factory()->create();
        $template = AssessmentTemplate::factory()->create();

        $standard = CustomStandard::create($this->makeStandardData($institution->id, $template->id, 'STD-001'));
        $standard->delete(); // Already deleted

        // Try to delete again
        $result = $this->service->delete($standard);

        $this->assertFalse($result);
    }

    public function test_create_casts_json_fields_correctly(): void
    {
        $institution = Institution::factory()->create();
        $template = AssessmentTemplate::factory()->create();

        $data = [
            'institution_id' => $institution->id,
            'template_id' => $template->id,
            'code' => 'STD-001',
            'name' => 'Test',
            'category_weights' => ['potensi' => 50, 'kompetensi' => 50],
            'aspect_configs' => ['asp_01' => ['weight' => 20]],
            'sub_aspect_configs' => ['sub_01' => ['rating' => 4]],
        ];

        $result = $this->service->create($data);

        $this->assertIsArray($result->category_weights);
        $this->assertIsArray($result->aspect_configs);
        $this->assertIsArray($result->sub_aspect_configs);
    }

    public function test_update_can_update_aspect_and_sub_aspect_configs(): void
    {
        $institution = Institution::factory()->create();
        $template = AssessmentTemplate::factory()->create();

        $standard = CustomStandard::create($this->makeStandardData($institution->id, $template->id, 'STD-001'));

        $newAspectConfigs = ['asp_02' => ['weight' => 30, 'active' => false]];
        $newSubAspectConfigs = ['sub_02' => ['rating' => 5, 'active' => false]];

        $result = $this->service->update($standard, [
            'aspect_configs' => $newAspectConfigs,
            'sub_aspect_configs' => $newSubAspectConfigs,
        ]);

        $this->assertEquals($newAspectConfigs, $result->aspect_configs);
        $this->assertEquals($newSubAspectConfigs, $result->sub_aspect_configs);
    }

    public function test_create_and_update_preserve_is_active_default(): void
    {
        $institution = Institution::factory()->create();
        $template = AssessmentTemplate::factory()->create();

        $data = [
            'institution_id' => $institution->id,
            'template_id' => $template->id,
            'code' => 'STD-001',
            'name' => 'Test',
            'category_weights' => [],
            'aspect_configs' => [],
            'sub_aspect_configs' => [],
        ];

        $created = $this->service->create($data);
        $this->assertTrue($created->is_active); // Default should be true

        $updated = $this->service->update($created, ['name' => 'Updated']);
        $this->assertTrue($updated->is_active); // Should remain true
    }

    // ==========================================
    // PHASE 4: TEMPLATE DEFAULTS (8 tests)
    // ==========================================

    public function test_get_template_defaults_returns_all_required_keys(): void
    {
        $template = $this->createTemplateWithCategories();

        $result = $this->service->getTemplateDefaults($template->id);

        $this->assertArrayHasKey('template', $result);
        $this->assertArrayHasKey('category_weights', $result);
        $this->assertArrayHasKey('aspect_configs', $result);
        $this->assertArrayHasKey('sub_aspect_configs', $result);

        $this->assertInstanceOf(AssessmentTemplate::class, $result['template']);
    }

    public function test_get_template_defaults_includes_all_category_weights(): void
    {
        $template = $this->createTemplateWithCategories();

        $result = $this->service->getTemplateDefaults($template->id);

        $this->assertArrayHasKey('potensi', $result['category_weights']);
        $this->assertArrayHasKey('kompetensi', $result['category_weights']);
        $this->assertEquals(40, $result['category_weights']['potensi']);
        $this->assertEquals(60, $result['category_weights']['kompetensi']);
    }

    public function test_get_template_defaults_adds_rating_only_for_aspects_without_sub_aspects(): void
    {
        $template = AssessmentTemplate::factory()->create();
        $category = CategoryType::factory()->create(['template_id' => $template->id, 'code' => 'kompetensi']);

        // Aspect WITHOUT sub-aspects (should have rating)
        $aspectWithoutSubs = Aspect::factory()->create([
            'category_type_id' => $category->id,
            'code' => 'asp_no_subs',
            'weight_percentage' => 30,
            'standard_rating' => 4.5,
        ]);

        // Aspect WITH sub-aspects (should NOT have rating)
        $aspectWithSubs = Aspect::factory()->create([
            'category_type_id' => $category->id,
            'code' => 'asp_with_subs',
            'weight_percentage' => 20,
            'standard_rating' => 3.0,
        ]);
        SubAspect::factory()->create([
            'aspect_id' => $aspectWithSubs->id,
            'code' => 'sub_01',
            'standard_rating' => 3,
        ]);

        $result = $this->service->getTemplateDefaults($template->id);

        // Aspect WITHOUT sub-aspects should have rating
        $this->assertArrayHasKey('rating', $result['aspect_configs']['asp_no_subs']);
        $this->assertEquals(4.5, $result['aspect_configs']['asp_no_subs']['rating']);

        // Aspect WITH sub-aspects should NOT have rating
        $this->assertArrayNotHasKey('rating', $result['aspect_configs']['asp_with_subs']);
    }

    public function test_get_template_defaults_includes_aspect_weights_and_active_status(): void
    {
        $template = $this->createTemplateWithCategories();

        $result = $this->service->getTemplateDefaults($template->id);

        // Check potensi aspect
        $this->assertArrayHasKey('asp_potensi_01', $result['aspect_configs']);
        $this->assertEquals(50, $result['aspect_configs']['asp_potensi_01']['weight']);
        $this->assertTrue($result['aspect_configs']['asp_potensi_01']['active']);

        // Check kompetensi aspect
        $this->assertArrayHasKey('asp_kompetensi_01', $result['aspect_configs']);
        $this->assertEquals(100, $result['aspect_configs']['asp_kompetensi_01']['weight']);
        $this->assertTrue($result['aspect_configs']['asp_kompetensi_01']['active']);
    }

    public function test_get_template_defaults_includes_sub_aspect_ratings_and_active_status(): void
    {
        $template = $this->createTemplateWithCategories();

        $result = $this->service->getTemplateDefaults($template->id);

        // Check sub-aspects from potensi category
        $this->assertArrayHasKey('sub_potensi_01', $result['sub_aspect_configs']);
        $this->assertEquals(3, $result['sub_aspect_configs']['sub_potensi_01']['rating']);
        $this->assertTrue($result['sub_aspect_configs']['sub_potensi_01']['active']);

        $this->assertArrayHasKey('sub_potensi_02', $result['sub_aspect_configs']);
        $this->assertEquals(4, $result['sub_aspect_configs']['sub_potensi_02']['rating']);
        $this->assertTrue($result['sub_aspect_configs']['sub_potensi_02']['active']);
    }

    public function test_get_template_defaults_eager_loads_relationships(): void
    {
        $template = $this->createTemplateWithCategories();

        $result = $this->service->getTemplateDefaults($template->id);

        $this->assertTrue($result['template']->relationLoaded('categoryTypes'));

        $firstCategory = $result['template']->categoryTypes->first();
        $this->assertTrue($firstCategory->relationLoaded('aspects'));

        $firstAspect = $firstCategory->aspects->first();
        $this->assertTrue($firstAspect->relationLoaded('subAspects'));
    }

    public function test_get_template_defaults_throws_exception_for_nonexistent_template(): void
    {
        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        $this->service->getTemplateDefaults(999);
    }

    public function test_get_template_defaults_handles_empty_template(): void
    {
        $template = AssessmentTemplate::factory()->create();
        // No categories, aspects, or sub-aspects

        $result = $this->service->getTemplateDefaults($template->id);

        $this->assertEmpty($result['category_weights']);
        $this->assertEmpty($result['aspect_configs']);
        $this->assertEmpty($result['sub_aspect_configs']);
    }

    // ==========================================
    // PHASE 5: SESSION MANAGEMENT (12 tests)
    // ==========================================

    public function test_select_stores_custom_standard_id_in_session(): void
    {
        $institution = Institution::factory()->create();
        $template = AssessmentTemplate::factory()->create();
        $standard = CustomStandard::create($this->makeStandardData($institution->id, $template->id, 'STD-001'));

        $this->service->select($template->id, $standard->id);

        $this->assertEquals($standard->id, Session::get("selected_standard.{$template->id}"));
    }

    public function test_select_can_store_null_to_revert_to_quantum_default(): void
    {
        $template = AssessmentTemplate::factory()->create();

        $this->service->select($template->id, null);

        $this->assertNull(Session::get("selected_standard.{$template->id}"));
    }

    public function test_select_clears_dynamic_adjustments(): void
    {
        $template = AssessmentTemplate::factory()->create();

        // Set some dynamic adjustments
        Session::put("standard_adjustment.{$template->id}", ['some' => 'data']);

        $institution = Institution::factory()->create();
        $standard = CustomStandard::create($this->makeStandardData($institution->id, $template->id, 'STD-001'));

        $this->service->select($template->id, $standard->id);

        $this->assertFalse(Session::has("standard_adjustment.{$template->id}"));
    }

    public function test_select_allows_switching_between_standards(): void
    {
        $institution = Institution::factory()->create();
        $template = AssessmentTemplate::factory()->create();

        $standard1 = CustomStandard::create($this->makeStandardData($institution->id, $template->id, 'STD-001'));
        $standard2 = CustomStandard::create($this->makeStandardData($institution->id, $template->id, 'STD-002'));

        $this->service->select($template->id, $standard1->id);
        $this->assertEquals($standard1->id, Session::get("selected_standard.{$template->id}"));

        $this->service->select($template->id, $standard2->id);
        $this->assertEquals($standard2->id, Session::get("selected_standard.{$template->id}"));
    }

    public function test_get_selected_returns_custom_standard_id_from_session(): void
    {
        $template = AssessmentTemplate::factory()->create();
        Session::put("selected_standard.{$template->id}", 123);

        $result = $this->service->getSelected($template->id);

        $this->assertEquals(123, $result);
    }

    public function test_get_selected_returns_null_when_no_selection(): void
    {
        $template = AssessmentTemplate::factory()->create();

        $result = $this->service->getSelected($template->id);

        $this->assertNull($result);
    }

    public function test_get_selected_standard_returns_custom_standard_model(): void
    {
        $institution = Institution::factory()->create();
        $template = AssessmentTemplate::factory()->create();
        $standard = CustomStandard::create($this->makeStandardData($institution->id, $template->id, 'STD-001'));

        Session::put("selected_standard.{$template->id}", $standard->id);

        $result = $this->service->getSelectedStandard($template->id);

        $this->assertInstanceOf(CustomStandard::class, $result);
        $this->assertEquals($standard->id, $result->id);
    }

    public function test_get_selected_standard_returns_null_when_no_selection(): void
    {
        $template = AssessmentTemplate::factory()->create();

        $result = $this->service->getSelectedStandard($template->id);

        $this->assertNull($result);
    }

    public function test_get_selected_standard_returns_null_when_standard_not_found(): void
    {
        $template = AssessmentTemplate::factory()->create();
        Session::put("selected_standard.{$template->id}", 999);

        $result = $this->service->getSelectedStandard($template->id);

        $this->assertNull($result);
    }

    public function test_clear_selection_removes_custom_standard_from_session(): void
    {
        $template = AssessmentTemplate::factory()->create();
        Session::put("selected_standard.{$template->id}", 123);

        $this->service->clearSelection($template->id);

        $this->assertFalse(Session::has("selected_standard.{$template->id}"));
    }

    public function test_clear_selection_also_clears_dynamic_adjustments(): void
    {
        $template = AssessmentTemplate::factory()->create();
        Session::put("selected_standard.{$template->id}", 123);
        Session::put("standard_adjustment.{$template->id}", ['some' => 'data']);

        $this->service->clearSelection($template->id);

        $this->assertFalse(Session::has("selected_standard.{$template->id}"));
        $this->assertFalse(Session::has("standard_adjustment.{$template->id}"));
    }

    public function test_session_keys_are_template_specific(): void
    {
        $template1 = AssessmentTemplate::factory()->create();
        $template2 = AssessmentTemplate::factory()->create();

        Session::put("selected_standard.{$template1->id}", 100);
        Session::put("selected_standard.{$template2->id}", 200);

        $this->assertEquals(100, $this->service->getSelected($template1->id));
        $this->assertEquals(200, $this->service->getSelected($template2->id));

        $this->service->clearSelection($template1->id);

        $this->assertNull($this->service->getSelected($template1->id));
        $this->assertEquals(200, $this->service->getSelected($template2->id));
    }

    // ==========================================
    // PHASE 6: GETTER METHODS (15 tests)
    // ==========================================

    public function test_get_aspect_weight_returns_weight_from_custom_standard(): void
    {
        $institution = Institution::factory()->create();
        $template = AssessmentTemplate::factory()->create();

        $standard = CustomStandard::create($this->makeStandardData(
            $institution->id,
            $template->id,
            'STD-001',
            'Test',
            ['potensi' => 40, 'kompetensi' => 60],
            ['asp_01' => ['weight' => 25, 'active' => true]]
        ));

        $result = $this->service->getAspectWeight($standard->id, 'asp_01');

        $this->assertEquals(25, $result);
    }

    public function test_get_aspect_weight_returns_null_for_nonexistent_aspect(): void
    {
        $institution = Institution::factory()->create();
        $template = AssessmentTemplate::factory()->create();

        $standard = CustomStandard::create($this->makeStandardData($institution->id, $template->id, 'STD-001'));

        $result = $this->service->getAspectWeight($standard->id, 'nonexistent');

        $this->assertNull($result);
    }

    public function test_get_aspect_weight_returns_null_for_nonexistent_standard(): void
    {
        $result = $this->service->getAspectWeight(999, 'asp_01');

        $this->assertNull($result);
    }

    public function test_get_aspect_rating_returns_rating_as_float(): void
    {
        $institution = Institution::factory()->create();
        $template = AssessmentTemplate::factory()->create();

        $standard = CustomStandard::create($this->makeStandardData(
            $institution->id,
            $template->id,
            'STD-001',
            'Test',
            ['potensi' => 50, 'kompetensi' => 50],
            ['asp_kompetensi_01' => ['weight' => 100, 'rating' => 4.5, 'active' => true]]
        ));

        $result = $this->service->getAspectRating($standard->id, 'asp_kompetensi_01');

        $this->assertIsFloat($result);
        $this->assertEquals(4.5, $result);
    }

    public function test_get_aspect_rating_returns_null_when_no_rating_field(): void
    {
        $institution = Institution::factory()->create();
        $template = AssessmentTemplate::factory()->create();

        $standard = CustomStandard::create($this->makeStandardData(
            $institution->id,
            $template->id,
            'STD-001',
            'Test',
            ['potensi' => 50, 'kompetensi' => 50],
            ['asp_potensi_01' => ['weight' => 50, 'active' => true]] // No rating (has sub-aspects)
        ));

        $result = $this->service->getAspectRating($standard->id, 'asp_potensi_01');

        $this->assertNull($result);
    }

    public function test_get_aspect_rating_returns_null_for_nonexistent_standard(): void
    {
        $result = $this->service->getAspectRating(999, 'asp_01');

        $this->assertNull($result);
    }

    public function test_get_sub_aspect_rating_returns_rating_from_custom_standard(): void
    {
        $institution = Institution::factory()->create();
        $template = AssessmentTemplate::factory()->create();

        $standard = CustomStandard::create($this->makeStandardData(
            $institution->id,
            $template->id,
            'STD-001',
            'Test',
            ['potensi' => 50, 'kompetensi' => 50],
            [],
            ['sub_01' => ['rating' => 5, 'active' => true]]
        ));

        $result = $this->service->getSubAspectRating($standard->id, 'sub_01');

        $this->assertEquals(5, $result);
    }

    public function test_get_sub_aspect_rating_returns_null_for_nonexistent_sub_aspect(): void
    {
        $institution = Institution::factory()->create();
        $template = AssessmentTemplate::factory()->create();

        $standard = CustomStandard::create($this->makeStandardData($institution->id, $template->id, 'STD-001'));

        $result = $this->service->getSubAspectRating($standard->id, 'nonexistent');

        $this->assertNull($result);
    }

    public function test_get_sub_aspect_rating_returns_null_for_nonexistent_standard(): void
    {
        $result = $this->service->getSubAspectRating(999, 'sub_01');

        $this->assertNull($result);
    }

    public function test_get_category_weight_returns_weight_from_custom_standard(): void
    {
        $institution = Institution::factory()->create();
        $template = AssessmentTemplate::factory()->create();

        $standard = CustomStandard::create($this->makeStandardData(
            $institution->id,
            $template->id,
            'STD-001',
            'Test',
            ['potensi' => 35, 'kompetensi' => 65]
        ));

        $potensiWeight = $this->service->getCategoryWeight($standard->id, 'potensi');
        $kompetensiWeight = $this->service->getCategoryWeight($standard->id, 'kompetensi');

        $this->assertEquals(35, $potensiWeight);
        $this->assertEquals(65, $kompetensiWeight);
    }

    public function test_get_category_weight_returns_null_for_nonexistent_category(): void
    {
        $institution = Institution::factory()->create();
        $template = AssessmentTemplate::factory()->create();

        $standard = CustomStandard::create($this->makeStandardData($institution->id, $template->id, 'STD-001'));

        $result = $this->service->getCategoryWeight($standard->id, 'nonexistent');

        $this->assertNull($result);
    }

    public function test_is_aspect_active_returns_active_status(): void
    {
        $institution = Institution::factory()->create();
        $template = AssessmentTemplate::factory()->create();

        $standard = CustomStandard::create($this->makeStandardData(
            $institution->id,
            $template->id,
            'STD-001',
            'Test',
            ['potensi' => 50, 'kompetensi' => 50],
            [
                'asp_active' => ['weight' => 30, 'active' => true],
                'asp_inactive' => ['weight' => 20, 'active' => false],
            ]
        ));

        $this->assertTrue($this->service->isAspectActive($standard->id, 'asp_active'));
        $this->assertFalse($this->service->isAspectActive($standard->id, 'asp_inactive'));
    }

    public function test_is_aspect_active_defaults_to_true_when_not_found(): void
    {
        $institution = Institution::factory()->create();
        $template = AssessmentTemplate::factory()->create();

        $standard = CustomStandard::create($this->makeStandardData($institution->id, $template->id, 'STD-001'));

        $result = $this->service->isAspectActive($standard->id, 'nonexistent');

        $this->assertTrue($result);
    }

    public function test_is_aspect_active_defaults_to_true_for_nonexistent_standard(): void
    {
        $result = $this->service->isAspectActive(999, 'asp_01');

        $this->assertTrue($result);
    }

    public function test_is_sub_aspect_active_returns_active_status(): void
    {
        $institution = Institution::factory()->create();
        $template = AssessmentTemplate::factory()->create();

        $standard = CustomStandard::create($this->makeStandardData(
            $institution->id,
            $template->id,
            'STD-001',
            'Test',
            ['potensi' => 50, 'kompetensi' => 50],
            [],
            [
                'sub_active' => ['rating' => 4, 'active' => true],
                'sub_inactive' => ['rating' => 3, 'active' => false],
            ]
        ));

        $this->assertTrue($this->service->isSubAspectActive($standard->id, 'sub_active'));
        $this->assertFalse($this->service->isSubAspectActive($standard->id, 'sub_inactive'));
    }

    // ==========================================
    // PHASE 7: VALIDATION METHODS (8 tests)
    // ==========================================

    public function test_validate_passes_when_category_weights_sum_to_100(): void
    {
        $data = [
            'category_weights' => ['potensi' => 40, 'kompetensi' => 60],
        ];

        $errors = $this->service->validate($data);

        $this->assertEmpty($errors);
    }

    public function test_validate_fails_when_category_weights_do_not_sum_to_100(): void
    {
        $data = [
            'category_weights' => ['potensi' => 40, 'kompetensi' => 50], // = 90
        ];

        $errors = $this->service->validate($data);

        $this->assertArrayHasKey('category_weights', $errors);
        $this->assertStringContainsString('100%', $errors['category_weights']);
        $this->assertStringContainsString('90%', $errors['category_weights']);
    }

    public function test_validate_fails_when_aspect_rating_below_1(): void
    {
        $data = [
            'aspect_configs' => [
                'asp_01' => ['weight' => 30, 'rating' => 0.5],
            ],
        ];

        $errors = $this->service->validate($data);

        $this->assertArrayHasKey('aspect_configs.asp_01.rating', $errors);
        $this->assertStringContainsString('1-5', $errors['aspect_configs.asp_01.rating']);
    }

    public function test_validate_fails_when_aspect_rating_above_5(): void
    {
        $data = [
            'aspect_configs' => [
                'asp_01' => ['weight' => 30, 'rating' => 5.5],
            ],
        ];

        $errors = $this->service->validate($data);

        $this->assertArrayHasKey('aspect_configs.asp_01.rating', $errors);
        $this->assertStringContainsString('1-5', $errors['aspect_configs.asp_01.rating']);
    }

    public function test_validate_fails_when_sub_aspect_rating_out_of_range(): void
    {
        $data = [
            'sub_aspect_configs' => [
                'sub_01' => ['rating' => 0, 'active' => true],
                'sub_02' => ['rating' => 6, 'active' => true],
            ],
        ];

        $errors = $this->service->validate($data);

        $this->assertArrayHasKey('sub_aspect_configs.sub_01.rating', $errors);
        $this->assertArrayHasKey('sub_aspect_configs.sub_02.rating', $errors);
    }

    public function test_validate_passes_when_aspect_has_no_rating_field(): void
    {
        $data = [
            'aspect_configs' => [
                'asp_01' => ['weight' => 50, 'active' => true], // No rating (has sub-aspects)
            ],
        ];

        $errors = $this->service->validate($data);

        $this->assertEmpty($errors);
    }

    public function test_validate_accepts_valid_rating_boundaries(): void
    {
        $data = [
            'aspect_configs' => [
                'asp_01' => ['rating' => 1],
                'asp_02' => ['rating' => 5],
            ],
            'sub_aspect_configs' => [
                'sub_01' => ['rating' => 1],
                'sub_02' => ['rating' => 5],
            ],
        ];

        $errors = $this->service->validate($data);

        $this->assertEmpty($errors);
    }

    public function test_validate_returns_empty_array_for_empty_data(): void
    {
        $errors = $this->service->validate([]);

        $this->assertEmpty($errors);
    }

    // ==========================================
    // PHASE 8: CODE UNIQUENESS (5 tests)
    // ==========================================

    public function test_is_code_unique_returns_true_for_unique_code(): void
    {
        $institution = Institution::factory()->create();

        $result = $this->service->isCodeUnique($institution->id, 'NEW-CODE');

        $this->assertTrue($result);
    }

    public function test_is_code_unique_returns_false_for_duplicate_code(): void
    {
        $institution = Institution::factory()->create();
        $template = AssessmentTemplate::factory()->create();

        CustomStandard::create($this->makeStandardData($institution->id, $template->id, 'EXISTING-CODE'));

        $result = $this->service->isCodeUnique($institution->id, 'EXISTING-CODE');

        $this->assertFalse($result);
    }

    public function test_is_code_unique_is_scoped_to_institution(): void
    {
        $institution1 = Institution::factory()->create();
        $institution2 = Institution::factory()->create();
        $template = AssessmentTemplate::factory()->create();

        CustomStandard::create($this->makeStandardData($institution1->id, $template->id, 'SHARED-CODE'));

        // Same code in different institution should be unique
        $result = $this->service->isCodeUnique($institution2->id, 'SHARED-CODE');

        $this->assertTrue($result);
    }

    public function test_is_code_unique_excludes_current_standard_when_updating(): void
    {
        $institution = Institution::factory()->create();
        $template = AssessmentTemplate::factory()->create();

        $standard = CustomStandard::create($this->makeStandardData($institution->id, $template->id, 'MY-CODE'));

        // Should be unique when excluding the current standard
        $result = $this->service->isCodeUnique($institution->id, 'MY-CODE', $standard->id);

        $this->assertTrue($result);
    }

    public function test_is_code_unique_detects_duplicate_even_when_excluding_different_id(): void
    {
        $institution = Institution::factory()->create();
        $template = AssessmentTemplate::factory()->create();

        $standard1 = CustomStandard::create($this->makeStandardData($institution->id, $template->id, 'CODE-A'));
        $standard2 = CustomStandard::create($this->makeStandardData($institution->id, $template->id, 'CODE-B'));

        // Trying to use CODE-A while updating standard2 should fail
        $result = $this->service->isCodeUnique($institution->id, 'CODE-A', $standard2->id);

        $this->assertFalse($result);
    }

    // ==========================================
    // HELPER METHODS
    // ==========================================

    /**
     * Create standard data array for testing
     */
    private function makeStandardData(
        int $institutionId,
        int $templateId,
        string $code,
        string $name = 'Test Standard',
        array $categoryWeights = ['potensi' => 40, 'kompetensi' => 60],
        array $aspectConfigs = ['asp_01' => ['weight' => 50, 'active' => true]],
        array $subAspectConfigs = ['sub_01' => ['rating' => 4, 'active' => true]]
    ): array {
        return [
            'institution_id' => $institutionId,
            'template_id' => $templateId,
            'code' => $code,
            'name' => $name,
            'description' => 'Test description',
            'category_weights' => $categoryWeights,
            'aspect_configs' => $aspectConfigs,
            'sub_aspect_configs' => $subAspectConfigs,
            'is_active' => true,
        ];
    }

    /**
     * Create a template with categories, aspects, and sub-aspects
     */
    private function createTemplateWithCategories(): AssessmentTemplate
    {
        $template = AssessmentTemplate::factory()->create();

        // Potensi category with aspect and sub-aspects
        $potensiCategory = CategoryType::factory()->create([
            'template_id' => $template->id,
            'code' => 'potensi',
            'name' => 'Potensi',
            'weight_percentage' => 40,
        ]);

        $potensiAspect = Aspect::factory()->create([
            'category_type_id' => $potensiCategory->id,
            'code' => 'asp_potensi_01',
            'name' => 'Aspek Potensi 1',
            'weight_percentage' => 50,
        ]);

        SubAspect::factory()->create([
            'aspect_id' => $potensiAspect->id,
            'code' => 'sub_potensi_01',
            'name' => 'Sub Potensi 1',
            'standard_rating' => 3,
        ]);

        SubAspect::factory()->create([
            'aspect_id' => $potensiAspect->id,
            'code' => 'sub_potensi_02',
            'name' => 'Sub Potensi 2',
            'standard_rating' => 4,
        ]);

        // Kompetensi category with aspect WITHOUT sub-aspects
        $kompetensiCategory = CategoryType::factory()->create([
            'template_id' => $template->id,
            'code' => 'kompetensi',
            'name' => 'Kompetensi',
            'weight_percentage' => 60,
        ]);

        Aspect::factory()->create([
            'category_type_id' => $kompetensiCategory->id,
            'code' => 'asp_kompetensi_01',
            'name' => 'Aspek Kompetensi 1',
            'weight_percentage' => 100,
            'standard_rating' => 4.5,
        ]);

        return $template->fresh(['categoryTypes.aspects.subAspects']);
    }
}
