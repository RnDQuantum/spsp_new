<?php

declare(strict_types=1);

namespace App\Livewire\Pages\HCA\Sections;

use App\Models\Participant;
use App\Services\HcaDataService;
use Illuminate\View\View;
use Livewire\Attributes\On;
use Livewire\Attributes\Reactive;
use Livewire\Component;

class PerformanceDashboard extends Component
{
    #[Reactive]
    public ?int $participantId = null;

    public string $chartId;

    #[On('hca-data-updated')]
    public function onDataUpdated(): void
    {
        // Re-renders component on data update
    }

    public function mount(): void
    {
        $this->chartId = 'perfChart_'.uniqid();
    }

    public function getParticipantProperty(): ?Participant
    {
        return app(HcaDataService::class)->getParticipant($this->participantId);
    }

    public function render(): View
    {
        $participant = $this->participant;
        $records = $participant?->performanceRecords ?? collect();

        if ($records->isNotEmpty()) {
            $years = $records->pluck('year')->map(fn ($y) => (string) $y)->toArray();
            $kpiTrends = $records->pluck('kpi_score')->map(fn ($v) => round((float) $v, 2))->toArray();
            $kpiBenchmarks = $records->pluck('benchmark_score')->map(fn ($v) => round((float) $v, 2))->toArray();

            $latest = $records->last();
            $latestScore = round((float) $latest->kpi_score, 2);
            $latestYear = (string) $latest->year;
            $kpiBreakdown = $latest->kpi_breakdown ?? [];

            $avgKpi = round((float) $records->avg('kpi_score'), 2);
            $firstScore = round((float) $records->first()->kpi_score, 2);
            $growthPerYear = count($records) > 1
                ? round(($latestScore - $firstScore) / (count($records) - 1), 2)
                : 0.00;

            $name = $participant?->name ?? 'Kandidat';
            $analysisDesc = "Terdapat tren performa yang konsisten dari tahun {$years[0]} hingga {$latestYear}. Pencapaian KPI tahun terakhir {$name} sebesar {$latestScore}% menandakan keandalan tinggi dalam mengeksekusi sasaran kerja strategis unit organisasi.";
        } else {
            $years = ['2022', '2023', '2024', '2025', '2026'];
            $kpiTrends = [92.40, 94.10, 96.80, 95.50, 98.20];
            $kpiBenchmarks = [90.00, 90.00, 90.00, 90.00, 90.00];
            $latestScore = 98.20;
            $latestYear = '2026';
            $avgKpi = 95.40;
            $growthPerYear = 1.45;
            $analysisDesc = 'Terdapat tren peningkatan kinerja yang stabil dari tahun 2022 hingga 2026. Pencapaian KPI tahun terakhir menandakan konsistensi tinggi dalam mengeksekusi inisiatif strategis.';
            $kpiBreakdown = [
                ['metric' => 'Revenue & Budget Efficiency', 'weight' => '30%', 'target' => '100.00%', 'actual' => '102.50%', 'status' => 'Exceeded', 'statusClass' => 'bg-emerald-50 text-forest-green border-emerald-100'],
                ['metric' => 'Talent Pool Development Rate', 'weight' => '25%', 'target' => '85.00%', 'actual' => '89.20%', 'status' => 'Exceeded', 'statusClass' => 'bg-emerald-50 text-forest-green border-emerald-100'],
                ['metric' => 'HR Operations Automation Index', 'weight' => '20%', 'target' => '90.00%', 'actual' => '91.80%', 'status' => 'Achieved', 'statusClass' => 'bg-emerald-50 text-forest-green border-emerald-100'],
                ['metric' => 'Employee Retention Index', 'weight' => '15%', 'target' => '95.00%', 'actual' => '97.50%', 'status' => 'Exceeded', 'statusClass' => 'bg-emerald-50 text-forest-green border-emerald-100'],
                ['metric' => 'Divisional Cost Saving', 'weight' => '10%', 'target' => '10.00%', 'actual' => '11.40%', 'status' => 'Exceeded', 'statusClass' => 'bg-emerald-50 text-forest-green border-emerald-100'],
            ];
        }

        return view('livewire.pages.h-c-a.sections.performance-dashboard', [
            'years' => $years,
            'kpiTrends' => $kpiTrends,
            'kpiBenchmarks' => $kpiBenchmarks,
            'latestScore' => $latestScore,
            'latestYear' => $latestYear,
            'avgKpi' => $avgKpi,
            'growthPerYear' => $growthPerYear,
            'analysisDesc' => $analysisDesc,
            'kpiBreakdown' => $kpiBreakdown,
            'participant' => $participant,
        ]);
    }
}
