<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Models\Aspect;
use App\Models\AspectAssessment;
use App\Models\AssessmentEvent;
use App\Models\AssessmentTemplate;
use App\Models\CategoryType;
use App\Models\CustomStandard;
use App\Models\Institution;
use App\Models\Participant;
use App\Models\PositionFormation;
use App\Models\SubAspect;
use App\Models\SubAspectAssessment;
use App\Services\Cache\AspectCacheService;
use App\Services\DynamicStandardService;
use App\Services\TalentPoolService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Session;
use Tests\TestCase;

/**
 * Audit Phase 2 Fix Tests
 *
 * Tests for Bug 2.2: TalentPoolService consistency with inactive sub-aspects.
 *
 * @see docs/SPSP_AUDIT.md
 */
class AuditPhase2FixTest extends TestCase
{
    use RefreshDatabase;

    private TalentPoolService $talentPoolService;

    private DynamicStandardService $dynamicService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->talentPoolService = app(TalentPoolService::class);
        $this->dynamicService = app(DynamicStandardService::class);

        AspectCacheService::clearCache();
        Cache::flush();
    }

    /**
     * Test: TalentPoolService recalculates aspect ratings when sub-aspects are deactivated
     *
     * Scenario:
     * - An aspect 'asp_pot_01' has 2 sub-aspects: 'sub_pot_01' (rating 4) and 'sub_pot_02' (rating 2)
     * - Participant has raw aspect rating 3.0 (from 4 and 2 average)
     * - 'sub_pot_02' is deactivated
     * - Recalculated aspect rating should be 4.0 (only active 'sub_pot_01')
     * - TalentPoolService should return 4.0 as the potensi rating for the participant
     */
    public function test_talent_pool_service_recalculates_rating_with_inactive_sub_aspects(): void
    {
        // Arrange: Create basic template and data structure
        $institution = $this->createInstitution();
        $template = AssessmentTemplate::factory()->create();

        $potensiCategory = CategoryType::factory()->create([
            'template_id' => $template->id,
            'code' => 'potensi',
            'name' => 'Potensi',
            'weight_percentage' => 50,
            'order' => 1,
        ]);

        $kompetensiCategory = CategoryType::factory()->create([
            'template_id' => $template->id,
            'code' => 'kompetensi',
            'name' => 'Kompetensi',
            'weight_percentage' => 50,
            'order' => 2,
        ]);

        $potensiAspect = Aspect::factory()->create([
            'template_id' => $template->id,
            'category_type_id' => $potensiCategory->id,
            'code' => 'asp_pot_01',
            'name' => 'Aspek Potensi 1',
            'weight_percentage' => 100,
            'standard_rating' => 3.0,
            'order' => 1,
        ]);

        $sub1 = SubAspect::factory()->create([
            'aspect_id' => $potensiAspect->id,
            'code' => 'sub_pot_01',
            'name' => 'Sub Potensi 1',
            'standard_rating' => 3,
            'order' => 1,
        ]);

        $sub2 = SubAspect::factory()->create([
            'aspect_id' => $potensiAspect->id,
            'code' => 'sub_pot_02',
            'name' => 'Sub Potensi 2',
            'standard_rating' => 3,
            'order' => 2,
        ]);

        $kompetensiAspect = Aspect::factory()->create([
            'template_id' => $template->id,
            'category_type_id' => $kompetensiCategory->id,
            'code' => 'asp_kom_01',
            'name' => 'Aspek Kompetensi 1',
            'weight_percentage' => 100,
            'standard_rating' => 4.0,
            'order' => 1,
        ]);

        // Create event and position
        $event = AssessmentEvent::factory()->create([
            'institution_id' => $institution->id,
            'code' => 'EVENT-01',
        ]);

        $position = PositionFormation::factory()->create([
            'event_id' => $event->id,
            'template_id' => $template->id,
        ]);

        // Create participant
        $participant = Participant::factory()->create([
            'event_id' => $event->id,
            'position_formation_id' => $position->id,
        ]);

        // Create category assessments
        $potensiCatAss = \App\Models\CategoryAssessment::factory()->create([
            'participant_id' => $participant->id,
            'category_type_id' => $potensiCategory->id,
            'event_id' => $event->id,
            'position_formation_id' => $position->id,
        ]);

        $kompetensiCatAss = \App\Models\CategoryAssessment::factory()->create([
            'participant_id' => $participant->id,
            'category_type_id' => $kompetensiCategory->id,
            'event_id' => $event->id,
            'position_formation_id' => $position->id,
        ]);

        // Create aspect assessments with raw individual_rating = 3.0
        $potensiAspectAss = AspectAssessment::factory()->create([
            'category_assessment_id' => $potensiCatAss->id,
            'participant_id' => $participant->id,
            'aspect_id' => $potensiAspect->id,
            'event_id' => $event->id,
            'position_formation_id' => $position->id,
            'individual_rating' => 3.0,
            'standard_rating' => 3.0,
        ]);

        AspectAssessment::factory()->create([
            'category_assessment_id' => $kompetensiCatAss->id,
            'participant_id' => $participant->id,
            'aspect_id' => $kompetensiAspect->id,
            'event_id' => $event->id,
            'position_formation_id' => $position->id,
            'individual_rating' => 4.5,
            'standard_rating' => 4.0,
        ]);

        // Create sub-aspect assessments
        SubAspectAssessment::factory()->create([
            'aspect_assessment_id' => $potensiAspectAss->id,
            'sub_aspect_id' => $sub1->id,
            'participant_id' => $participant->id,
            'event_id' => $event->id,
            'individual_rating' => 4.0,
            'standard_rating' => 3.0,
        ]);

        SubAspectAssessment::factory()->create([
            'aspect_assessment_id' => $potensiAspectAss->id,
            'sub_aspect_id' => $sub2->id,
            'participant_id' => $participant->id,
            'event_id' => $event->id,
            'individual_rating' => 2.0,
            'standard_rating' => 3.0,
        ]);

        // Preload aspect cache
        AspectCacheService::preloadByTemplate($template->id);

        // 1. Verify standard/all-active case first (no adjustments)
        $dataBefore = $this->talentPoolService->getNineBoxMatrixData($event->id, $position->id);
        $pDataBefore = $dataBefore['participants']->firstWhere('participant_id', $participant->id);

        $this->assertEquals(3.0, $pDataBefore['potensi_rating']); // Raw average of 4 and 2
        $this->assertEquals(4.5, $pDataBefore['kinerja_rating']);

        // 2. Act: Set sub_pot_02 as INACTIVE
        $this->dynamicService->setSubAspectActive($template->id, 'sub_pot_02', false);

        // Clear cache so TalentPoolService calculates again
        Cache::flush();
        AspectCacheService::clearCache();
        $this->talentPoolService = app(TalentPoolService::class); // Fresh instance

        // Get recalculated data
        $dataAfter = $this->talentPoolService->getNineBoxMatrixData($event->id, $position->id);
        $pDataAfter = $dataAfter['participants']->firstWhere('participant_id', $participant->id);

        // Assert: Rating should be recalculated to 4.0 (only active sub1 rating 4.0)
        $this->assertEquals(4.0, $pDataAfter['potensi_rating']);
        $this->assertEquals(4.5, $pDataAfter['kinerja_rating']); // Kompetensi unchanged
    }

    /**
     * Test: TalentPoolService excludes inactive aspects completely from the category average
     */
    public function test_talent_pool_service_excludes_inactive_aspects(): void
    {
        $institution = $this->createInstitution();
        $template = AssessmentTemplate::factory()->create();

        $potensiCategory = CategoryType::factory()->create([
            'template_id' => $template->id,
            'code' => 'potensi',
            'name' => 'Potensi',
            'weight_percentage' => 100,
        ]);

        // Create 2 aspects
        $aspect1 = Aspect::factory()->create([
            'template_id' => $template->id,
            'category_type_id' => $potensiCategory->id,
            'code' => 'asp_pot_01',
            'weight_percentage' => 50,
        ]);

        $aspect2 = Aspect::factory()->create([
            'template_id' => $template->id,
            'category_type_id' => $potensiCategory->id,
            'code' => 'asp_pot_02',
            'weight_percentage' => 50,
        ]);

        $event = AssessmentEvent::factory()->create([
            'institution_id' => $institution->id,
        ]);

        $position = PositionFormation::factory()->create([
            'event_id' => $event->id,
            'template_id' => $template->id,
        ]);

        $participant = Participant::factory()->create([
            'event_id' => $event->id,
            'position_formation_id' => $position->id,
        ]);

        $potensiCatAss = \App\Models\CategoryAssessment::factory()->create([
            'participant_id' => $participant->id,
            'category_type_id' => $potensiCategory->id,
            'event_id' => $event->id,
            'position_formation_id' => $position->id,
        ]);

        // Create competency category assessment so TalentPoolService setup is complete
        $kompetensiCategory = CategoryType::factory()->create([
            'template_id' => $template->id,
            'code' => 'kompetensi',
        ]);
        $kompetensiCatAss = \App\Models\CategoryAssessment::factory()->create([
            'participant_id' => $participant->id,
            'category_type_id' => $kompetensiCategory->id,
            'event_id' => $event->id,
            'position_formation_id' => $position->id,
        ]);
        $kompetensiAspect = Aspect::factory()->create([
            'template_id' => $template->id,
            'category_type_id' => $kompetensiCategory->id,
            'code' => 'asp_kom_01',
        ]);
        AspectAssessment::factory()->create([
            'category_assessment_id' => $kompetensiCatAss->id,
            'participant_id' => $participant->id,
            'aspect_id' => $kompetensiAspect->id,
            'event_id' => $event->id,
            'position_formation_id' => $position->id,
            'individual_rating' => 3.0,
        ]);

        // Aspect ratings: aspect1 = 4.0, aspect2 = 2.0
        AspectAssessment::factory()->create([
            'category_assessment_id' => $potensiCatAss->id,
            'participant_id' => $participant->id,
            'aspect_id' => $aspect1->id,
            'event_id' => $event->id,
            'position_formation_id' => $position->id,
            'individual_rating' => 4.0,
        ]);

        AspectAssessment::factory()->create([
            'category_assessment_id' => $potensiCatAss->id,
            'participant_id' => $participant->id,
            'aspect_id' => $aspect2->id,
            'event_id' => $event->id,
            'position_formation_id' => $position->id,
            'individual_rating' => 2.0,
        ]);

        AspectCacheService::preloadByTemplate($template->id);

        // Before: average should be (4+2)/2 = 3.0
        $dataBefore = $this->talentPoolService->getNineBoxMatrixData($event->id, $position->id);
        $pBefore = $dataBefore['participants']->firstWhere('participant_id', $participant->id);
        $this->assertEquals(3.0, $pBefore['potensi_rating']);

        // Act: Set aspect2 as inactive
        $this->dynamicService->setAspectActive($template->id, 'asp_pot_02', false);

        // Clear caches and get fresh service
        Cache::flush();
        AspectCacheService::clearCache();
        $this->talentPoolService = app(TalentPoolService::class);

        // After: average should be 4.0 (aspect2 is completely excluded)
        $dataAfter = $this->talentPoolService->getNineBoxMatrixData($event->id, $position->id);
        $pAfter = $dataAfter['participants']->firstWhere('participant_id', $participant->id);
        $this->assertEquals(4.0, $pAfter['potensi_rating']);
    }

    private function createInstitution(): Institution
    {
        return Institution::create([
            'code' => 'INST_'.uniqid(),
            'name' => 'Test Institution',
            'api_key' => 'test_api_key_'.uniqid(),
        ]);
    }
}
