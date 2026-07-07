<?php

declare(strict_types=1);

namespace App\Livewire\HcaReport\Sections;

use Livewire\Component;
use Illuminate\View\View;

class MentalHealthSection extends Component
{
    public float $wellbeingIndex = 4.20; // Scale 1.00 - 5.00
    public string $wellbeingCategory = 'Baik / Sehat';

    public array $aspects = [
        ['label' => 'Kesehatan Emosional', 'value' => 4.30, 'desc' => 'Stabilitas emosi dan kemampuan menyalurkan stres kerja secara sehat.'],
        ['label' => 'Resiliensi Diri', 'value' => 4.10, 'desc' => 'Daya lentur bangkit dari kegagalan operasional dan situasi menekan.'],
        ['label' => 'Kepuasan Kerja', 'value' => 4.00, 'desc' => 'Kepuasan psikologis umum terhadap peran, tugas, dan tim kerja.'],
        ['label' => 'Interaksi Sosial', 'value' => 4.40, 'desc' => 'Kemampuan menjalin komunikasi sosial yang harmonis dan suportif.'],
    ];

    public string $clinicalComment = 'Kandidat menunjukkan tingkat kesejahteraan mental (well-being) yang sangat matang. Memiliki mekanisme koping stres yang konstruktif, sehingga sangat stabil dalam mengambil keputusan penting di bawah tekanan tinggi. Tidak terdeteksi adanya indikator klinis yang mengganggu kapasitas kerja fungsional.';

    public function render(): View
    {
        return view('livewire.hca-report.sections.mental-health-section');
    }
}
