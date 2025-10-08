<?php

namespace App\Livewire;

use App\Models\Aspect;
use App\Models\AspectAssessment;
use App\Models\CategoryType;
use App\Models\FinalAssessment;
use App\Models\Participant;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app', ['title' => 'General Matching'])]
class ReportComponent extends Component
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

    public function mount($eventCode, $testNumber): void
    {

        // Load participant with relations based on event code and test number
        $this->participant = Participant::with([
            'assessmentEvent.template',
            'batch',
            'positionFormation',
        ])
            ->whereHas('assessmentEvent', function ($query) use ($eventCode) {
                $query->where('code', $eventCode);
            })
            ->where('test_number', $testNumber)
            ->firstOrFail();

        // Load final assessment
        $this->finalAssessment = FinalAssessment::where('participant_id', $this->participant->id)->first();

        if ($this->finalAssessment) {
            $this->jobMatchPercentage = round($this->finalAssessment->achievement_percentage);
        }

        // Get template from event
        $template = $this->participant->assessmentEvent->template;

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
            return [
                'name' => $assessment->aspect->name,
                'code' => $assessment->aspect->code,
                'percentage' => $assessment->percentage_score,
                'individual_rating' => $assessment->individual_rating,
                'standard_rating' => $assessment->standard_rating,
                'description' => $assessment->description_text,
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
        return view('livewire.pages.general_matching');
    }
}
