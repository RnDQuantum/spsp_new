<?php

namespace App\Livewire\Pages\IndividualReport;

use App\Models\Aspect;
use App\Models\AspectAssessment;
use App\Models\CategoryType;
use App\Models\FinalAssessment;
use App\Models\Participant;
use App\Services\DynamicStandardService;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app', ['title' => 'General Matching'])]
class GeneralMatching extends Component
{
    // Participant info
    public ?Participant $participant = null;

    public ?FinalAssessment $finalAssessment = null;

    // Category types
    public ?CategoryType $potensiCategory = null;

    public ?CategoryType $kompetensiCategory = null;

    // Aspect assessments grouped by category
    public $potensiAspects = [];

    public $kompetensiAspects = [];

    // Job match percentage (from final assessment)
    public $jobMatchPercentage = 0;

    // CACHE PROPERTIES - untuk menyimpan hasil kalkulasi
    private ?array $potensiAspectsCache = null;

    private ?array $kompetensiAspectsCache = null;

    // ADD: Public properties untuk support child component
    public $eventCode;

    public $testNumber;

    // Flag untuk menentukan apakah standalone atau child
    public $isStandalone = true;

    // Dynamic display parameters
    public $showHeader = true;

    public $showInfoSection = true;

    public $showPotensi = true;

    public $showKompetensi = true;

    public function mount($eventCode, $testNumber, $showHeader = true, $showInfoSection = true, $showPotensi = true, $showKompetensi = true): void
    {
        // Gunakan parameter jika ada (dari route), atau fallback ke property (dari parent)
        $this->eventCode = $eventCode ?? $this->eventCode;
        $this->testNumber = $testNumber ?? $this->testNumber;

        // Set dynamic display parameters
        $this->showHeader = $showHeader;
        $this->showInfoSection = $showInfoSection;
        $this->showPotensi = $showPotensi;
        $this->showKompetensi = $showKompetensi;

        // Tentukan apakah standalone (dari route) atau child (dari parent)
        $this->isStandalone = $eventCode !== null && $testNumber !== null;

        // Validate
        if (! $this->eventCode || ! $this->testNumber) {
            abort(404, 'Event code and test number are required');
        }

        // Load participant with relations based on event code and test number
        $this->participant = Participant::with([
            'assessmentEvent',
            'batch',
            'positionFormation.template',
        ])
            ->whereHas('assessmentEvent', function ($query) {
                $query->where('code', $this->eventCode);
            })
            ->where('test_number', $this->testNumber)
            ->firstOrFail();

        // Load final assessment
        $this->finalAssessment = FinalAssessment::where('participant_id', $this->participant->id)->first();

        // Get template from position
        $template = $this->participant->positionFormation->template;

        // Get category types for this template
        $this->potensiCategory = CategoryType::where('template_id', $template->id)
            ->where('code', 'potensi')
            ->first();

        $this->kompetensiCategory = CategoryType::where('template_id', $template->id)
            ->where('code', 'kompetensi')
            ->first();

        // Load aspect assessments for Potensi
        if ($this->potensiCategory) {
            $this->loadAspectsForCategory($this->potensiCategory->id, 'potensiAspects');
        }

        // Load aspect assessments for Kompetensi
        if ($this->kompetensiCategory) {
            $this->loadAspectsForCategory($this->kompetensiCategory->id, 'kompetensiAspects');
        }

        // Calculate overall job match percentage
        $this->calculateJobMatchPercentage();
    }

    /**
     * Clear all caches
     */
    private function clearCache(): void
    {
        $this->potensiAspectsCache = null;
        $this->kompetensiAspectsCache = null;
    }

    private function calculateJobMatchPercentage(): void
    {
        $allPercentages = [];

        // Collect percentages from potensi aspects
        foreach ($this->potensiAspects as $aspect) {
            $allPercentages[] = $aspect['percentage'];
        }

        // Collect percentages from kompetensi aspects
        foreach ($this->kompetensiAspects as $aspect) {
            $allPercentages[] = $aspect['percentage'];
        }

        // Calculate average if there are any aspects
        if (count($allPercentages) > 0) {
            $this->jobMatchPercentage = round(array_sum($allPercentages) / count($allPercentages));
        } else {
            $this->jobMatchPercentage = 0;
        }
    }

    private function loadAspectsForCategory(int $categoryTypeId, string $propertyName): void
    {
        // OPTIMIZED: Check cache first
        $cacheProperty = $propertyName.'Cache';
        if ($this->$cacheProperty !== null) {
            $this->$propertyName = $this->$cacheProperty;

            return;
        }

        $template = $this->participant->positionFormation->template;
        $standardService = app(DynamicStandardService::class);

        // Determine category code from property name
        $categoryCode = str_contains($propertyName, 'potensi') ? 'potensi' : 'kompetensi';

        // CRITICAL FIX: Get ONLY active aspect IDs to filter individual scores
        $activeAspectIds = $standardService->getActiveAspectIds($template->id, $categoryCode);

        // Fallback to all IDs if no adjustments
        if (empty($activeAspectIds)) {
            $activeAspectIds = Aspect::where('category_type_id', $categoryTypeId)
                ->pluck('id')
                ->toArray();
        }

        // OPTIMIZED: Load aspect assessments with sub-aspects filtered by ACTIVE aspects only
        $aspectAssessments = AspectAssessment::with([
            'aspect.subAspects',
            'subAspectAssessments.subAspect',
        ])
            ->where('participant_id', $this->participant->id)
            ->whereIn('aspect_id', $activeAspectIds) // ✅ CRITICAL: Filter active only
            ->orderBy('aspect_id')
            ->get();

        // Process each aspect
        $result = $aspectAssessments->map(function ($assessment) use ($template, $standardService, $categoryCode) {
            $aspect = $assessment->aspect;

            // Calculate percentage using generalized logic with adjusted ratings
            $percentage = $this->calculateAspectPercentage($assessment, $template, $standardService);

            // CRITICAL FIX: Get adjusted standard rating based on category
            if ($categoryCode === 'potensi') {
                // For Potensi: Recalculate from active sub-aspects
                $adjustedStandardRating = $this->getPotensiAspectStandardRating($assessment, $template, $standardService);
            } else {
                // For Kompetensi: Get adjusted rating from session
                $adjustedStandardRating = $standardService->getAspectRating($template->id, $aspect->code);
            }

            return [
                'name' => $aspect->name,
                'code' => $aspect->code,
                'percentage' => round($percentage), // Standard rounding (5-9 up, 0-4 down)
                'individual_rating' => $assessment->individual_rating,
                'standard_rating' => $adjustedStandardRating, // ✅ FIXED: Use adjusted rating
                'original_standard_rating' => $assessment->standard_rating, // Keep original for reference
                'description' => $aspect->description,
                'sub_aspects' => $this->loadSubAspects($assessment, $template, $standardService),
            ];
        })->toArray();

        // OPTIMIZED: Cache the result
        $this->$cacheProperty = $result;
        $this->$propertyName = $result;
    }

    /**
     * Get Potensi aspect standard rating by averaging active sub-aspects
     */
    private function getPotensiAspectStandardRating(
        AspectAssessment $assessment,
        $template,
        DynamicStandardService $standardService
    ): float {
        if ($assessment->subAspectAssessments->isEmpty()) {
            // No sub-aspects, use aspect rating from session
            return $standardService->getAspectRating($template->id, $assessment->aspect->code);
        }

        $totalRating = 0;
        $activeCount = 0;

        foreach ($assessment->subAspectAssessments as $subAssessment) {
            // Check if sub-aspect is active
            if (! $standardService->isSubAspectActive($template->id, $subAssessment->subAspect->code)) {
                continue;
            }

            // Get adjusted sub-aspect rating
            $adjustedRating = $standardService->getSubAspectRating(
                $template->id,
                $subAssessment->subAspect->code
            );

            $totalRating += $adjustedRating;
            $activeCount++;
        }

        if ($activeCount > 0) {
            return round($totalRating / $activeCount, 2);
        }

        return 0;
    }

    private function calculateAspectPercentage(
        AspectAssessment $assessment,
        $template,
        DynamicStandardService $standardService
    ): float {
        $aspect = $assessment->aspect;

        // Check if aspect has sub-aspects
        if ($assessment->subAspectAssessments->isNotEmpty()) {
            // Calculate based on ACTIVE sub-aspects only (for Potensi)
            $totalValue = 0;
            $activeSubAspectCount = 0;

            foreach ($assessment->subAspectAssessments as $subAssessment) {
                // CRITICAL: Check if sub-aspect is active
                if (! $standardService->isSubAspectActive($template->id, $subAssessment->subAspect->code)) {
                    continue; // Skip inactive sub-aspects
                }

                // Get adjusted standard rating from session
                $adjustedStandardRating = $standardService->getSubAspectRating(
                    $template->id,
                    $subAssessment->subAspect->code
                );

                // Individual rating stays the same (from database)
                $individualRating = $subAssessment->individual_rating;

                // If individual >= standard, value = 1.0 (full contribution)
                // If individual < standard, value = individual / standard (proportional)
                if ($individualRating >= $adjustedStandardRating) {
                    $totalValue += 1.0;
                } else {
                    $totalValue += $individualRating / $adjustedStandardRating;
                }

                $activeSubAspectCount++;
            }

            // Average of ACTIVE sub-aspects, then convert to percentage
            if ($activeSubAspectCount > 0) {
                return ($totalValue / $activeSubAspectCount) * 100;
            }

            return 0;
        } else {
            // Calculate based on aspect-level rating (for Kompetensi)
            // Get adjusted standard rating from session
            $adjustedStandardRating = $standardService->getAspectRating($template->id, $aspect->code);
            $individualRating = $assessment->individual_rating;

            // Apply the same logic: if individual >= standard, return 100%
            if ($individualRating >= $adjustedStandardRating) {
                return 100;
            } else {
                return ($individualRating / $adjustedStandardRating) * 100;
            }
        }
    }

    private function loadSubAspects(
        AspectAssessment $aspectAssessment,
        $template,
        DynamicStandardService $standardService
    ): array {
        return $aspectAssessment->subAspectAssessments
            ->filter(function ($subAssessment) use ($template, $standardService) {
                // CRITICAL: Filter only ACTIVE sub-aspects
                return $standardService->isSubAspectActive($template->id, $subAssessment->subAspect->code);
            })
            ->map(function ($subAssessment) use ($template, $standardService) {
                // Get adjusted standard rating from session
                $adjustedStandardRating = $standardService->getSubAspectRating(
                    $template->id,
                    $subAssessment->subAspect->code
                );

                return [
                    'name' => $subAssessment->subAspect->name,
                    'individual_rating' => $subAssessment->individual_rating,
                    'standard_rating' => $adjustedStandardRating, // ✅ Use adjusted rating
                    'original_standard_rating' => $subAssessment->standard_rating, // Keep original for reference
                    'rating_label' => $subAssessment->rating_label,
                ];
            })
            ->values() // Reset array keys after filter
            ->toArray();
    }

    public function getAspectPercentage(string $aspectCode): int
    {
        // Search in potensi
        $potensi = collect($this->potensiAspects)->firstWhere('code', $aspectCode);
        if ($potensi) {
            return $potensi['percentage'];
        }

        // Search in kompetensi
        $kompetensi = collect($this->kompetensiAspects)->firstWhere('code', $aspectCode);
        if ($kompetensi) {
            return $kompetensi['percentage'];
        }

        return 0;
    }

    public function getSubAspectRating(string $aspectCode, string $subAspectName): ?int
    {
        $aspect = collect($this->potensiAspects)->firstWhere('code', $aspectCode)
            ?? collect($this->kompetensiAspects)->firstWhere('code', $aspectCode);

        if (! $aspect) {
            return null;
        }

        $subAspect = collect($aspect['sub_aspects'])->firstWhere('name', $subAspectName);

        return $subAspect ? $subAspect['individual_rating'] : null;
    }

    public function render()
    {
        // Jika standalone, gunakan layout. Jika child, tidak pakai layout
        if ($this->isStandalone) {
            return view('livewire.pages.individual-report.general-matching')
                ->layout('components.layouts.app', ['title' => 'General Matching']);
        }

        return view('livewire.pages.individual-report.general-matching');
    }
}
