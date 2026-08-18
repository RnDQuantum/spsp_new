<?php

declare(strict_types=1);

namespace App\Livewire\Pages\HCA\Sections;

use App\Models\CategoryType;
use App\Models\Participant;
use App\Services\IndividualAssessmentService;
use Illuminate\View\View;
use Livewire\Attributes\Reactive;
use Livewire\Component;

class ScoreListSection extends Component
{
    #[Reactive]
    public ?int $participantId = null;

    public string $sectionCode = '';

    /**
     * Datasets for parameterized score list sections
     */
    public array $datasets = [
        'competency' => [
            'title' => 'Layer 1: Kompetensi',
            'subtitle' => 'Hard Evidence & Perilaku Manajerial',
            'desc' => 'Hasil evaluasi komparatif tingkat kompetensi manajerial, sosial kultural, teknis, dan kepemimpinan peserta terhadap standar formasi jabatan.',
            'average' => 3.90,
            'max_score' => 5.00,
            'scores' => [],
        ],
        'cognitive' => [
            'title' => 'IQ & Profil Kognitif',
            'subtitle' => 'Kapasitas Berpikir & Kemampuan Logika',
            'desc' => 'Deskripsi menyeluruh tentang kecepatan pemrosesan informasi, kapasitas kognitif umum, serta inteligensi numerik, verbal, dan spasial.',
            'average' => 118,
            'max_score' => 140,
            'is_iq' => true,
            'scores' => [
                ['label' => 'Analytical Thinking', 'value' => 120, 'desc' => 'Kapasitas mengurai masalah kompleks menjadi komponen logis.'],
                ['label' => 'Numerical Reasoning', 'value' => 115, 'desc' => 'Kecepatan dan akurasi analisis data angka dan pola kuantitatif.'],
                ['label' => 'Verbal Comprehension', 'value' => 122, 'desc' => 'Pemahaman komprehensif atas logika bahasa dan teks tertulis.'],
                ['label' => 'Abstract Logic', 'value' => 117, 'desc' => 'Kemampuan mengidentifikasi hubungan logis dalam bentuk non-verbal.'],
                ['label' => 'Spatial Orientation', 'value' => 116, 'desc' => 'Visualisasi dan manipulasi objek multi-dimensi dalam ruang.'],
            ],
        ],
        'big_five' => [
            'title' => 'Big Five Personality',
            'subtitle' => 'Inventori Kepribadian Model OCEAN',
            'desc' => 'Mengukur lima dimensi dasar kepribadian untuk memproyeksikan stabilitas emosi, kecenderungan berinteraksi, dan disiplin pencapaian tugas kerja.',
            'average' => 4.12,
            'max_score' => 5.00,
            'scores' => [
                ['label' => 'Openness to Experience', 'value' => 4.10, 'desc' => 'Keterbukaan terhadap ide baru, imajinasi kreatif, dan keragaman pengalaman.'],
                ['label' => 'Conscientiousness', 'value' => 4.50, 'desc' => 'Tingkat disiplin diri, keteraturan kerja, orientasi prestasi, dan keandalan.'],
                ['label' => 'Extraversion', 'value' => 3.80, 'desc' => 'Tingkat kenyamanan dalam interaksi sosial, keaktifan bergaul, dan asertivitas.'],
                ['label' => 'Agreeableness', 'value' => 4.20, 'desc' => 'Kecenderungan untuk kooperatif, berempati, mempercayai, dan membantu orang lain.'],
                ['label' => 'Emotional Stability', 'value' => 4.00, 'desc' => 'Kapasitas mengelola stres, ketenangan emosional, dan ketahanan terhadap tekanan.'],
            ],
        ],
        'learning_agility' => [
            'title' => 'Learning Agility',
            'subtitle' => 'Kelincahan & Adaptabilitas Belajar',
            'desc' => 'Mengukur kelincahan kandidat dalam mempelajari pola baru dan mengaplikasikan pembelajaran masa lalu pada konteks baru.',
            'average' => 4.00,
            'max_score' => 5.00,
            'scores' => [
                ['label' => 'Mental Agility', 'value' => 4.20, 'desc' => 'Kelincahan berpikir dan memecahkan ketidakpastian secara rasional.'],
                ['label' => 'People Agility', 'value' => 3.90, 'desc' => 'Kapasitas berkolaborasi dan memahami dinamika kelompok secara cepat.'],
                ['label' => 'Change Agility', 'value' => 4.10, 'desc' => 'Kesiapan bereksperimen dengan metode baru dan menyukai perubahan.'],
                ['label' => 'Result Agility', 'value' => 3.80, 'desc' => 'Kemampuan memberikan hasil prima dalam situasi transisi atau baru.'],
            ],
        ],
        'leadership_potential' => [
            'title' => 'Leadership Potential',
            'subtitle' => 'Potensi Kepemimpinan & Pengaruh',
            'desc' => 'Proyeksi kapasitas kepemimpinan kandidat untuk memikul tanggung jawab manajerial yang lebih besar.',
            'average' => 3.85,
            'max_score' => 5.00,
            'scores' => [
                ['label' => 'Visioning', 'value' => 4.00, 'desc' => 'Kemampuan merumuskan arah dan target unit kerja jangka panjang.'],
                ['label' => 'Decision Making', 'value' => 3.70, 'desc' => 'Kecepatan dan ketepatan pengambilan keputusan di situasi kritis.'],
                ['label' => 'Strategic Influence', 'value' => 3.90, 'desc' => 'Kekuatan persuasi dan kapasitas merangkul pemangku kepentingan.'],
                ['label' => 'Execution Control', 'value' => 3.80, 'desc' => 'Disiplin mengawal rencana kerja hingga tuntas.'],
                ['label' => 'Coaching & Developing', 'value' => 3.95, 'desc' => 'Kapasitas membimbing dan mempromosikan kapabilitas anggota tim.'],
                ['label' => 'Strategic Thinking', 'value' => 3.75, 'desc' => 'Kemampuan membaca tren eksternal organisasi dan dampaknya.'],
            ],
        ],
        'integrity' => [
            'title' => 'Values & Integrity',
            'subtitle' => 'Integritas Etika & Keselarasan Nilai',
            'desc' => 'Menilai keselarasan perilaku sehari-hari kandidat terhadap kode etik organisasi dan prinsip kejujuran universal.',
            'average' => 4.50,
            'max_score' => 5.00,
            'scores' => [
                ['label' => 'Honesty & Transparency', 'value' => 4.60, 'desc' => 'Keterbukaan dan kejujuran dalam menyampaikan fakta tanpa distorsi.'],
                ['label' => 'Ethical Compliance', 'value' => 4.50, 'desc' => 'Ketaatan total terhadap regulasi dan prinsip etika korporasi.'],
                ['label' => 'Accountability', 'value' => 4.40, 'desc' => 'Keberanian bertanggung jawab atas hasil keputusan kerja sendiri.'],
                ['label' => 'Consistency', 'value' => 4.50, 'desc' => 'Kesesuaian antara ucapan dan tindakan nyata di lapangan.'],
            ],
        ],
    ];

    public function mount(string $sectionCode): void
    {
        $this->sectionCode = $sectionCode;
    }

    /**
     * Get active participant.
     */
    public function getParticipantProperty(): ?Participant
    {
        if (! $this->participantId) {
            return Participant::with(['positionFormation.template', 'finalAssessment'])->first();
        }

        return Participant::with(['positionFormation.template', 'finalAssessment'])->find($this->participantId);
    }

    /**
     * Get dynamic competency data from SPSP database.
     */
    private function getCompetencyData(): array
    {
        $participant = $this->participant;

        if (! $participant || ! $participant->positionFormation?->template) {
            return [
                'title' => 'Layer 1: Kompetensi',
                'subtitle' => 'Hard Evidence & Perilaku Manajerial',
                'desc' => 'Hasil evaluasi komparatif tingkat kompetensi manajerial peserta terhadap standar formasi jabatan.',
                'average' => 3.90,
                'max_score' => 5.00,
                'scores' => [
                    ['label' => 'Integritas', 'value' => 4.00, 'standard' => 3.00, 'gap' => 1.00, 'conclusion' => 'Di Atas Standar', 'desc' => 'Konsisten berperilaku selaras dengan nilai, norma, dan etika organisasi.'],
                    ['label' => 'Kerjasama', 'value' => 3.80, 'standard' => 3.00, 'gap' => 0.80, 'conclusion' => 'Memenuhi Standar', 'desc' => 'Kemampuan membangun hubungan kerja yang produktif dan saling mendukung.'],
                    ['label' => 'Komunikasi', 'value' => 4.10, 'standard' => 3.00, 'gap' => 1.10, 'conclusion' => 'Di Atas Standar', 'desc' => 'Kemampuan menyampaikan informasi secara jelas, persuasif, dan efektif.'],
                    ['label' => 'Orientasi pada Hasil', 'value' => 3.70, 'standard' => 3.00, 'gap' => 0.70, 'conclusion' => 'Memenuhi Standar', 'desc' => 'Fokus pada pencapaian target kerja dengan standar kualitas tinggi.'],
                    ['label' => 'Pelayanan Publik', 'value' => 4.00, 'standard' => 3.00, 'gap' => 1.00, 'conclusion' => 'Di Atas Standar', 'desc' => 'Komitmen memberikan pelayanan prima bagi para pemangku kepentingan.'],
                ],
            ];
        }

        $templateId = $participant->positionFormation->template_id;
        $service = app(IndividualAssessmentService::class);

        $kompetensiCat = CategoryType::where('template_id', $templateId)->where('code', 'kompetensi')->first();

        if (! $kompetensiCat) {
            return $this->datasets['competency'];
        }

        $aspectAssessments = $service->getAspectAssessments($participant->id, $kompetensiCat->id, 0);

        if ($aspectAssessments->isEmpty()) {
            return $this->datasets['competency'];
        }

        $scores = $aspectAssessments->map(function ($item) {
            return [
                'label' => $item['name'],
                'value' => (float) $item['individual_rating'],
                'standard' => (float) ($item['standard_rating'] ?? 3.00),
                'gap' => (float) ($item['gap_rating'] ?? 0.00),
                'conclusion' => (string) ($item['conclusion_text'] ?? ''),
                'desc' => (string) ($item['description'] ?? 'Aspek kompetensi perilaku dan manajerial jabatan.'),
            ];
        })->toArray();

        $average = (float) $aspectAssessments->avg('individual_rating');

        return [
            'title' => 'Layer 1: Kompetensi',
            'subtitle' => 'Hard Evidence & Perilaku Manajerial',
            'desc' => 'Hasil evaluasi komparatif tingkat kompetensi manajerial, sosial kultural, teknis, dan kepemimpinan peserta terhadap standar formasi jabatan yang dipersyaratkan.',
            'average' => round($average, 2),
            'max_score' => 5.00,
            'scores' => $scores,
        ];
    }

    public function render(): View
    {
        if ($this->sectionCode === 'competency') {
            $data = $this->getCompetencyData();
        } else {
            $data = $this->datasets[$this->sectionCode] ?? $this->datasets['competency'];
        }

        return view('livewire.pages.h-c-a.sections.score-list-section', [
            'title' => $data['title'],
            'subtitle' => $data['subtitle'],
            'desc' => $data['desc'],
            'average' => $data['average'],
            'max_score' => $data['max_score'],
            'is_iq' => $data['is_iq'] ?? false,
            'scores' => $data['scores'],
            'participant' => $this->participant,
        ]);
    }
}
