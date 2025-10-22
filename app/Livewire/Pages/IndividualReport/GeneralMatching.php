<?php

namespace App\Livewire\Pages\IndividualReport;

use App\Models\Aspect;
use App\Models\AspectAssessment;
use App\Models\CategoryType;
use App\Models\FinalAssessment;
use App\Models\Participant;
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
        if (!$this->eventCode || !$this->testNumber) {
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

    private function calculateJobMatchPercentage(): void
    {
        $allPercentages = [];

        // Collect percentages from potensi aspects
        foreach ($this->potensiAspects as $aspect) {
            // Calculate percentage same way as in loadAspectsForCategory
            $percentage = min(($aspect['individual_rating'] / $aspect['standard_rating']) * 100, 100);
            $allPercentages[] = $percentage;
        }

        // Collect percentages from kompetensi aspects
        foreach ($this->kompetensiAspects as $aspect) {
            // Calculate percentage same way as in loadAspectsForCategory
            $percentage = min(($aspect['individual_rating'] / $aspect['standard_rating']) * 100, 100);
            $allPercentages[] = $percentage;
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
        // Get aspect IDs for this category
        $aspectIds = Aspect::where('category_type_id', $categoryTypeId)
            ->pluck('id')
            ->toArray();

        // Load aspect assessments with sub-aspects
        $aspectAssessments = AspectAssessment::with([
            'aspect.subAspects',
            'subAspectAssessments.subAspect',
        ])
            ->where('participant_id', $this->participant->id)
            ->whereIn('aspect_id', $aspectIds)
            ->orderBy('aspect_id')
            ->get();

        // Process each aspect
        $this->$propertyName = $aspectAssessments->map(function ($assessment) {
            // Calculate percentage based on individual vs standard rating, capped at 100%
            $percentage = min(($assessment->individual_rating / $assessment->standard_rating) * 100, 100);

            return [
                'name' => $assessment->aspect->name,
                'code' => $assessment->aspect->code,
                'percentage' => round($percentage), // Round to remove decimal places
                'individual_rating' => $assessment->individual_rating,
                'standard_rating' => $assessment->standard_rating,
                'description' => $assessment->aspect->description,
                'sub_aspects' => $this->loadSubAspects($assessment),
            ];
        })->toArray();
    }

    private function loadSubAspects(AspectAssessment $aspectAssessment): array
    {
        return $aspectAssessment->subAspectAssessments->map(function ($subAssessment) {
            return [
                'name' => $subAssessment->subAspect->name,
                'individual_rating' => $subAssessment->individual_rating,
                'standard_rating' => $subAssessment->standard_rating,
                'rating_label' => $subAssessment->rating_label,
            ];
        })->toArray();
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
