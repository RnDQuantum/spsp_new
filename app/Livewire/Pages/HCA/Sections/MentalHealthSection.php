<?php

declare(strict_types=1);

namespace App\Livewire\Pages\HCA\Sections;

use App\Models\Participant;
use Illuminate\View\View;
use Livewire\Attributes\Reactive;
use Livewire\Component;

class MentalHealthSection extends Component
{
    #[Reactive]
    public ?int $participantId = null;

    public float $wellbeingIndex = 4.20; // Scale 1.00 - 5.00

    public string $wellbeingCategory = 'Baik / Sehat';

    public string $validityStatus = 'PRIMA & ADAPTIF';

    public array $aspects = [];

    public string $clinicalComment = '';

    public array $validityScales = [];

    public array $clinicalScales = [];

    public array $elevatedScales = [];

    public function mount(?int $participantId = null): void
    {
        if ($participantId) {
            $this->participantId = $participantId;
        }
        $this->loadMmpiData();
    }

    public function updatedParticipantId(): void
    {
        $this->loadMmpiData();
    }

    public function loadMmpiData(): void
    {
        if (! $this->participantId) {
            $this->setDefaultAspects();

            return;
        }

        $participant = Participant::with('mmpi')->find($this->participantId);
        $mmpi = $participant?->mmpi;

        if (! $mmpi) {
            $this->setDefaultAspects();

            return;
        }

        $this->wellbeingIndex = $mmpi->wellbeing_score;
        $this->wellbeingCategory = $mmpi->wellbeing_category;
        $this->validityScales = $mmpi->validity_scales;
        $this->clinicalScales = $mmpi->clinical_scales;
        $this->elevatedScales = $mmpi->elevated_scales;

        // Validity Badge
        if (str_contains(strtolower((string) $mmpi->validitas), 'perhatian') || str_contains(strtolower((string) $mmpi->validitas), 'defensif')) {
            $this->validityStatus = 'PERHATIAN / DEF packet';
        } else {
            $this->validityStatus = 'PRIMA & ADAPTIF';
        }

        // Sub-aspect values proportional to well-being index
        $wb = $this->wellbeingIndex;
        $this->aspects = [
            [
                'label' => 'Kesehatan Emosional',
                'value' => round(max(1.0, min(5.0, $wb + 0.1)), 2),
                'desc' => $mmpi->internal ?: 'Stabilitas emosi dan kemampuan menyalurkan stres kerja secara sehat.',
            ],
            [
                'label' => 'Resiliensi Diri',
                'value' => round(max(1.0, min(5.0, $wb - 0.1)), 2),
                'desc' => 'Daya lentur bangkit dari kegagalan operasional dan situasi menekan.',
            ],
            [
                'label' => 'Kapasitas & Kepuasan Kerja',
                'value' => round(max(1.0, min(5.0, $wb - 0.2)), 2),
                'desc' => $mmpi->kap_kerja ?: 'Kapasitas fungsional terhadap peran, tugas, dan target kerja.',
            ],
            [
                'label' => 'Interaksi Sosial & Relasi',
                'value' => round(max(1.0, min(5.0, $wb + 0.2)), 2),
                'desc' => $mmpi->interpersonal ?: 'Kemampuan menjalin komunikasi sosial yang harmonis dan suportif.',
            ],
        ];

        $commentParts = [];
        if ($mmpi->kesimpulan) {
            $commentParts[] = $mmpi->kesimpulan;
        }
        if ($mmpi->klinik) {
            $commentParts[] = $mmpi->klinik;
        }

        $this->clinicalComment = ! empty($commentParts)
            ? implode("\n\n", $commentParts)
            : 'Kandidat menunjukkan tingkat kesejahteraan mental (well-being) yang sangat matang. Memiliki mekanisme koping stres yang konstruktif, sehingga sangat stabil dalam mengambil keputusan penting di bawah tekanan tinggi. Tidak terdeteksi adanya indikator klinis yang mengganggu kapasitas kerja fungsional.';
    }

    private function setDefaultAspects(): void
    {
        $this->wellbeingIndex = 4.20;
        $this->wellbeingCategory = 'Baik / Sehat';
        $this->validityStatus = 'PRIMA & ADAPTIF';
        $this->aspects = [
            ['label' => 'Kesehatan Emosional', 'value' => 4.30, 'desc' => 'Stabilitas emosi dan kemampuan menyalurkan stres kerja secara sehat.'],
            ['label' => 'Resiliensi Diri', 'value' => 4.10, 'desc' => 'Daya lentur bangkit dari kegagalan operasional dan situasi menekan.'],
            ['label' => 'Kepuasan Kerja', 'value' => 4.00, 'desc' => 'Kepuasan psikologis umum terhadap peran, tugas, dan tim kerja.'],
            ['label' => 'Interaksi Sosial', 'value' => 4.40, 'desc' => 'Kemampuan menjalin komunikasi sosial yang harmonis dan suportif.'],
        ];
        $this->clinicalComment = 'Kandidat menunjukkan tingkat kesejahteraan mental (well-being) yang sangat matang. Memiliki mekanisme koping stres yang konstruktif, sehingga sangat stabil dalam mengambil keputusan penting di bawah tekanan tinggi. Tidak terdeteksi adanya indikator klinis yang mengganggu kapasitas kerja fungsional.';
    }

    public function render(): View
    {
        $this->loadMmpiData();

        return view('livewire.pages.h-c-a.sections.mental-health-section');
    }
}
