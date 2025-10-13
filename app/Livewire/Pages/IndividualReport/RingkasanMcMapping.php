<?php

namespace App\Livewire\Pages\IndividualReport;

use App\Models\Aspect;
use App\Models\AspectAssessment;
use App\Models\CategoryType;
use App\Models\Participant;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app', ['title' => 'Ringkasan Kompetensi'])]
class RingkasanMcMapping extends Component
{
    public ?Participant $participant = null;

    public ?CategoryType $kompetensiCategory = null;

    public $aspectsData = [];

    public function mount($eventCode, $testNumber): void
    {
        // Load participant
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

        $template = $this->participant->assessmentEvent->template;

        // Get kompetensi category type only
        $this->kompetensiCategory = CategoryType::where('template_id', $template->id)
            ->where('code', 'kompetensi')
            ->first();

        // Load aspects data
        $this->loadAspectsData();
    }

    private function loadAspectsData(): void
    {
        if (! $this->kompetensiCategory) {
            $this->aspectsData = [];

            return;
        }

        $aspectIds = Aspect::where('category_type_id', $this->kompetensiCategory->id)
            ->orderBy('order')
            ->pluck('id')
            ->toArray();

        $aspectAssessments = AspectAssessment::with('aspect')
            ->where('participant_id', $this->participant->id)
            ->whereIn('aspect_id', $aspectIds)
            ->orderBy('aspect_id')
            ->get();

        $this->aspectsData = $aspectAssessments->map(fn ($assessment, $index) => [
            'number' => $index + 1,
            'name' => $assessment->aspect->name,
            'individual_rating' => $assessment->individual_rating,
            'standard_rating' => $assessment->standard_rating,
            'conclusion' => $this->getConclusionData(
                (float) $assessment->individual_rating,
                (float) $assessment->standard_rating
            ),
        ])->toArray();
    }

    private function getConclusionData(float $individualRating, float $standardRating): array
    {
        $gap = $individualRating - $standardRating;

        // Determine conclusion based on gap
        if ($gap >= 1) {
            return [
                'title' => 'Sangat Kompeten',
                'description' => 'Perilaku yang ditunjukkan melampaui tingkatan yang diperlukan',
                'color' => 'blue',
            ];
        } elseif ($gap >= 0) {
            return [
                'title' => 'Kompeten',
                'description' => 'Perilaku yang ditunjukkan sesuai dengan tingkat yang diperlukan',
                'color' => 'green',
            ];
        } elseif ($gap >= -0.5) {
            return [
                'title' => 'Cukup Kompeten',
                'description' => 'Perilaku yang ditunjukkan cukup sesuai dengan tingkatan yang diperlukan, dengan tetap memerlukan pengembangan perilaku',
                'color' => 'yellow',
            ];
        } elseif ($gap >= -1) {
            return [
                'title' => 'Kurang Kompeten',
                'description' => 'Perilaku yang ditunjukkan kurang sesuai dengan tingkatan yang diperlukan dan memerlukan pengembangan perilaku',
                'color' => 'red',
            ];
        } else {
            return [
                'title' => 'Belum Kompeten',
                'description' => 'Perilaku yang ditunjukkan belum sesuai dengan tingkatan yang diperlukan dan sangat memerlukan pengembangan perilaku',
                'color' => 'pink',
            ];
        }
    }

    private function getColorClass(string $color): string
    {
        return match ($color) {
            'blue' => 'bg-blue-600',
            'green' => 'bg-green-500',
            'yellow' => 'bg-yellow-400',
            'red' => 'bg-red-600',
            'pink' => 'bg-pink-300',
            default => 'bg-gray-400',
        };
    }

    public function render()
    {
        return view('livewire.pages.individual-report.ringkasan-mc-mapping');
    }
}
