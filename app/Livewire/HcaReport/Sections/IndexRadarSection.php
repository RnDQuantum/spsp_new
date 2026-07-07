<?php

declare(strict_types=1);

namespace App\Livewire\HcaReport\Sections;

use Livewire\Component;
use Illuminate\View\View;

class IndexRadarSection extends Component
{
    public string $sectionCode = 'hci';
    public string $chartId;

    public array $datasets = [
        'hci' => [
            'title' => 'Human Capital Index',
            'subtitle' => 'Dimensi Utama (Layer 1-3)',
            'desc' => 'Budi Santoso menunjukkan profil kompetensi dan potensi yang sangat kokoh. Indeks Kinerja dan Potensi berada jauh di atas standar institusi, menandakan kesiapan tinggi untuk peran kepemimpinan berikutnya.',
            'talentIndex' => 4.12,
            'talentIndexPercent' => 82.40,
            'talentCategory' => 'Strong Talent',
            'labels' => ['Kompetensi', 'Potensi', 'Kinerja', 'Kepemimpinan', 'Integritas'],
            'actualRatings' => [4.00, 4.25, 4.50, 3.80, 4.30],
            'standardRatings' => [3.00, 3.00, 3.00, 3.00, 3.00],
            'toleranceRatings' => [2.70, 2.70, 2.70, 2.70, 2.70],
        ],
        'potential' => [
            'title' => 'Layer 2: Potensi',
            'subtitle' => 'Breakdown Kapasitas Berkembang',
            'desc' => 'Analisis dimensi kapasitas pertumbuhan personal, kesiapan memimpin, kelincahan kognitif, dan motivasi kerja jangka panjang.',
            'talentIndex' => 4.25,
            'talentIndexPercent' => 85.00,
            'talentCategory' => 'High Potential',
            'labels' => ['Cognitive', 'Innovation', 'Agility', 'Strategy', 'EQ'],
            'actualRatings' => [2.50, 4.10, 4.00, 4.30, 4.35],
            'standardRatings' => [4.00, 4.00, 3.00, 3.00, 3.00],
            'toleranceRatings' => [3.70, 2.70, 2.70, 2.70, 2.70],
        ],
        'eq' => [
            'title' => 'Emotional Intelligence (EQ)',
            'subtitle' => 'Kematangan Emosional & Hubungan Kerja',
            'desc' => 'Evaluasi aspek pengenalan diri, pengendalian emosi, keterampilan sosial, empati, dan motivasi intrinsik kandidat.',
            'talentIndex' => 4.35,
            'talentIndexPercent' => 87.00,
            'talentCategory' => 'Highly Mature',
            'labels' => ['Self Awareness', 'Self Regulation', 'Social Skills', 'Empathy', 'Motivation'],
            'actualRatings' => [4.20, 4.50, 4.10, 4.60, 4.35],
            'standardRatings' => [3.00, 3.00, 3.00, 3.00, 3.00],
            'toleranceRatings' => [2.70, 2.70, 2.70, 2.70, 2.70],
        ]
    ];

    public function mount(string $sectionCode = 'hci'): void
    {
        $this->sectionCode = $sectionCode;
        $this->chartId = 'hciRadar_' . $sectionCode . '_' . uniqid();
    }

    public function render(): View
    {
        $data = $this->datasets[$this->sectionCode] ?? $this->datasets['hci'];
        return view('livewire.hca-report.sections.index-radar-section', [
            'title' => $data['title'],
            'subtitle' => $data['subtitle'],
            'desc' => $data['desc'],
            'talentIndex' => $data['talentIndex'],
            'talentIndexPercent' => $data['talentIndexPercent'],
            'talentCategory' => $data['talentCategory'],
            'labels' => $data['labels'],
            'actualRatings' => $data['actualRatings'],
            'standardRatings' => $data['standardRatings'],
            'toleranceRatings' => $data['toleranceRatings'],
        ]);
    }
}
