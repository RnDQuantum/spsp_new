<?php

declare(strict_types=1);

namespace App\Livewire\Pages\HCA\Sections;

use App\Models\AspectAssessment;
use App\Models\Participant;
use App\Services\HcaDataService;
use Illuminate\View\View;
use Livewire\Attributes\Reactive;
use Livewire\Component;

class DevelopmentRecommendation extends Component
{
    #[Reactive]
    public ?int $participantId = null;

    public function render(): View
    {
        $participant = app(HcaDataService::class)->getParticipant($this->participantId);
        $analysis = $this->analyzeDevelopmentNeeds($participant);

        return view('livewire.pages.h-c-a.sections.development-recommendation', [
            'strengths' => $analysis['strengths'],
            'gaps' => $analysis['gaps'],
            'focusTheme' => $analysis['focusTheme'],
        ]);
    }

    /**
     * Analyze participant development needs dynamically from aspect assessments
     */
    private function analyzeDevelopmentNeeds(?Participant $participant): array
    {
        if (! $participant) {
            return [
                'focusTheme' => 'AKSELERASI KEPEMIMPINAN & TATA KELOLA',
                'strengths' => [
                    [
                        'aspect' => 'Kepemimpinan Strategis',
                        'score' => '4.20',
                        'gap' => '+0.70',
                        'recommendation' => 'Optimalkan sebagai Lead Inisiatif Transformasi unit dan mentor bagi talenta junior.',
                    ],
                    [
                        'aspect' => 'Ketangkasan Belajar',
                        'score' => '4.10',
                        'gap' => '+0.60',
                        'recommendation' => 'Libatkan dalam adopsi teknologi digital baru dan perancangan pilot project inovatif.',
                    ],
                    [
                        'aspect' => 'Integritas & Etika',
                        'score' => '4.50',
                        'gap' => '+1.00',
                        'recommendation' => 'Posisikan sebagai champion tata kelola (GCG) dan pengawal kepatuhan SOP organisasi.',
                    ],
                ],
                'gaps' => [
                    [
                        'aspect' => 'Pemantauan Detail Teknis & Pengendalian',
                        'score' => '2.90',
                        'gap' => '-0.60',
                        'action_70' => 'Penugasan mengawal langsung audit kepatuhan berkala dan monitoring milestone mingguan.',
                        'action_20' => 'Pendampingan (coaching) teknis operasional bersama Senior Specialist.',
                        'action_10' => 'Pelatihan Manajemen Proyek & Pengendalian Kualitas Operasional.',
                    ],
                    [
                        'aspect' => 'Wawasan Bisnis Makro & Lintas Sektor',
                        'score' => '3.00',
                        'gap' => '-0.50',
                        'action_70' => 'Rotasi tugas jangka pendek pada komite koordinasi lintas direktorat/institusi.',
                        'action_20' => 'Mentoring bulanan bersama jajaran Direksi / Pejabat Pimpinan Tinggi.',
                        'action_10' => 'Program Eksekutif Analisis Kebijakan & Manajemen Strategis.',
                    ],
                    [
                        'aspect' => 'Manajemen Stres & Beban Kerja Puncak',
                        'score' => '3.10',
                        'gap' => '-0.40',
                        'action_70' => 'Penerapan pendelegasian wewenang taktis untuk mencegah bottleneck kerja mandiri.',
                        'action_20' => 'Konsultasi berkala manajemen energi kerja bersama psikolog pendamping.',
                        'action_10' => 'Workshop Executive Stress Management & Work-Life Integration.',
                    ],
                ],
            ];
        }

        // Query all aspect assessments for the participant with aspect relation
        $assessments = AspectAssessment::with('aspect')
            ->where('participant_id', $participant->id)
            ->orderByDesc('individual_rating')
            ->get();

        // 1. Top 3 Strengths (Highest individual rating / positive gap)
        $topStrengths = $assessments->take(3);
        $strengthItems = [];

        foreach ($topStrengths as $st) {
            $aspectName = $st->aspect?->name ?? 'Aspek Kompetensi';
            $rating = (float) $st->individual_rating;
            $std = (float) $st->standard_rating;
            $gap = $rating - $std;
            $gapFormatted = ($gap >= 0 ? '+' : '').number_format($gap, 2);

            $lowerName = strtolower($aspectName);
            $rec = match (true) {
                str_contains($lowerName, 'pikir') || str_contains($lowerName, 'analis') => 'Kapitalisasi ketajaman analisa untuk memimpin kajian kelayakan inisiatif strategis organisasi.',
                str_contains($lowerName, 'pimpin') || str_contains($lowerName, 'arah') => 'Posisikan sebagai Lead Project atau Person-in-Charge program prioritas serta mentor bagi tim.',
                str_contains($lowerName, 'integritas') || str_contains($lowerName, 'etika') => 'Jadikan role model tata kelola organisasi (GCG) dan pengawal standar kepatuhan etika.',
                str_contains($lowerName, 'komunikasi') || str_contains($lowerName, 'kerjasama') => 'Manfaatkan kemahiran komunikasi untuk negosiasi stakeholder dan diplomasi lintas unit.',
                default => 'Berdayakan keunggulan kapabilitas ini sebagai motor penggerak pencapaian target unit kerja.',
            };

            $strengthItems[] = [
                'aspect' => $aspectName,
                'score' => number_format($rating, 2),
                'gap' => $gapFormatted,
                'recommendation' => $rec,
            ];
        }

        // 2. Top 3 Critical Development Gaps (Lowest ratings / most negative gaps)
        $bottomGaps = $assessments->sortBy('gap_rating')->take(3)->values();
        $gapItems = [];

        foreach ($bottomGaps as $gp) {
            $aspectName = $gp->aspect?->name ?? 'Aspek Kompetensi';
            $rating = (float) $gp->individual_rating;
            $std = (float) $gp->standard_rating;
            $gap = $rating - $std;
            $gapFormatted = ($gap >= 0 ? '+' : '').number_format($gap, 2);

            $actions = $this->derive702010Actions($aspectName);

            $gapItems[] = [
                'aspect' => $aspectName,
                'score' => number_format($rating, 2),
                'gap' => $gapFormatted,
                'action_70' => $actions['70'],
                'action_20' => $actions['20'],
                'action_10' => $actions['10'],
            ];
        }

        $focusTheme = 'PENGUATAN KAPABILITAS & AKSELERASI TALENTA';
        if ($bottomGaps->isNotEmpty()) {
            $firstName = $bottomGaps->first()->aspect?->name ?? 'Kompetensi';
            $lastName = $bottomGaps->last()->aspect?->name ?? 'Potensi';
            $focusTheme = 'PENGEMBANGAN '.strtoupper($firstName).' & '.strtoupper($lastName);
        }

        return [
            'focusTheme' => $focusTheme,
            'strengths' => $strengthItems,
            'gaps' => $gapItems,
        ];
    }

    /**
     * Map specific aspect name to actionable 70-20-10 development activities
     */
    private function derive702010Actions(?string $aspectName): array
    {
        $lower = strtolower($aspectName ?? '');

        if (str_contains($lower, 'pimpin') || str_contains($lower, 'arah') || str_contains($lower, 'kelola')) {
            return [
                '70' => 'Penugasan memimpin gugus tugas (task force) proyek lintas bidang dengan target terukur.',
                '20' => 'Executive mentoring dwimingguan bersama pimpinan senior mengenai kepemimpinan adaptif.',
                '10' => 'Executive Leadership & Strategic Transformation Masterclass.',
            ];
        }

        if (str_contains($lower, 'analis') || str_contains($lower, 'pikir') || str_contains($lower, 'logika')) {
            return [
                '70' => 'Penyusunan kajian kebijakan staf dan pemodelan skenario risiko sebelum eksekusi program.',
                '20' => 'Coaching berkala bersama Senior Business Analyst / Ahli Utama terkait metodologi analisa.',
                '10' => 'Pelatihan Advanced Data-Driven Decision Making & Strategic Thinking.',
            ];
        }

        if (str_contains($lower, 'komunikasi') || str_contains($lower, 'kerjasama') || str_contains($lower, 'sosial')) {
            return [
                '70' => 'Keterlibatan aktif dalam negosiasi eksternal dan presentasi kemitraan lintas divisi.',
                '20' => 'Peer feedback dan pendampingan teknik persuasi publik oleh praktisi komunikasi.',
                '10' => 'Workshop High-Impact Executive Presentation & Stakeholder Engagement.',
            ];
        }

        if (str_contains($lower, 'stres') || str_contains($lower, 'tahan') || str_contains($lower, 'sikap')) {
            return [
                '70' => 'Penerapan matriks prioritas Eisenhower dan pendelegasian tugas operasional berulang.',
                '20' => 'Sesi coaching berkala manajemen resiliensi dan pengelolaan energi kerja.',
                '10' => 'Pelatihan Executive Mindfulness, Work-Life Integration, & Peak Performance.',
            ];
        }

        return [
            '70' => 'Penugasan khusus berfokus pada pengayaan peran (job enrichment) di bawah pengawasan terarah.',
            '20' => 'Mentoring terstruktur bersama atasan langsung untuk evaluasi progres berkala.',
            '10' => 'Kursus pengembangan kompetensi fungsional manajerial terakreditasi.',
        ];
    }
}
