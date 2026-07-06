<?php

declare(strict_types=1);

namespace App\Livewire\HcaReport\Sections;

use Livewire\Component;
use Illuminate\View\View;

class PerformanceDashboard extends Component
{
    public string $chartId;

    // Time-series KPI trends (2022 - 2026)
    public array $years = ['2022', '2023', '2024', '2025', '2026'];
    public array $kpiTrends = [92.40, 94.10, 96.80, 95.50, 98.20];
    public array $kpiBenchmarks = [92.00, 92.00, 92.00, 92.00, 92.00];

    // Snapshot breakdown of the latest year (2026)
    public array $kpiBreakdown = [
        [
            'metric' => 'Revenue & Budget Efficiency',
            'weight' => '30%',
            'target' => '100.00%',
            'actual' => '102.50%',
            'status' => 'Exceeded',
            'statusClass' => 'bg-emerald-50 text-forest-green border-emerald-100'
        ],
        [
            'metric' => 'Talent Pool Development Rate',
            'weight' => '25%',
            'target' => '85.00%',
            'actual' => '89.20%',
            'status' => 'Exceeded',
            'statusClass' => 'bg-emerald-50 text-forest-green border-emerald-100'
        ],
        [
            'metric' => 'HR Operations Automation Index',
            'weight' => '20%',
            'target' => '90.00%',
            'actual' => '91.80%',
            'status' => 'Achieved',
            'statusClass' => 'bg-emerald-50 text-forest-green border-emerald-100'
        ],
        [
            'metric' => 'Employee Retention Index',
            'weight' => '15%',
            'target' => '95.00%',
            'actual' => '97.50%',
            'status' => 'Exceeded',
            'statusClass' => 'bg-emerald-50 text-forest-green border-emerald-100'
        ],
        [
            'metric' => 'Divisional Cost Saving',
            'weight' => '10%',
            'target' => '10.00%',
            'actual' => '11.40%',
            'status' => 'Exceeded',
            'statusClass' => 'bg-emerald-50 text-forest-green border-emerald-100'
        ]
    ];

    public function mount(): void
    {
        $this->chartId = 'perfChart_' . uniqid();
    }

    public function render(): View
    {
        return view('livewire.hca-report.sections.performance-dashboard');
    }
}
