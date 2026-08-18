<?php

declare(strict_types=1);

namespace App\Livewire\Pages\HCA\Sections;

use App\Models\CategoryType;
use App\Models\Participant;
use App\Models\SubAspectAssessment;
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
            'scores' => [
                ['label' => 'Integritas', 'value' => 4.00, 'standard' => 3.00, 'gap' => 1.00, 'conclusion' => 'Di Atas Standar', 'desc' => 'Konsisten berperilaku selaras dengan nilai, norma, dan etika organisasi.'],
                ['label' => 'Kerjasama', 'value' => 3.80, 'standard' => 3.00, 'gap' => 0.80, 'conclusion' => 'Memenuhi Standar', 'desc' => 'Kemampuan membangun hubungan kerja yang produktif dan saling mendukung.'],
                ['label' => 'Komunikasi', 'value' => 4.10, 'standard' => 3.00, 'gap' => 1.10, 'conclusion' => 'Di Atas Standar', 'desc' => 'Kemampuan menyampaikan informasi secara jelas, persuasif, dan efektif.'],
                ['label' => 'Orientasi pada Hasil', 'value' => 3.70, 'standard' => 3.00, 'gap' => 0.70, 'conclusion' => 'Memenuhi Standar', 'desc' => 'Fokus pada pencapaian target kerja dengan standar kualitas tinggi.'],
                ['label' => 'Pelayanan Publik', 'value' => 4.00, 'standard' => 3.00, 'gap' => 1.00, 'conclusion' => 'Di Atas Standar', 'desc' => 'Komitmen memberikan pelayanan prima bagi para pemangku kepentingan.'],
            ],
        ],
        'cognitive' => [
            'title' => 'IQ & Profil Kognitif',
            'subtitle' => 'Kapasitas Berpikir & Kemampuan Logika',
            'desc' => 'Deskripsi menyeluruh tentang kecepatan pemrosesan informasi, kapasitas kognitif umum, serta inteligensi numerik, verbal, dan spasial.',
            'average' => 118,
            'max_score' => 140,
            'is_iq' => true,
            'scores' => [
                ['label' => 'Analytical Thinking', 'value' => 120, 'standard' => 100, 'gap' => 20, 'conclusion' => 'Di Atas Standar', 'desc' => 'Kapasitas mengurai masalah kompleks menjadi komponen logis.'],
                ['label' => 'Numerical Reasoning', 'value' => 115, 'standard' => 100, 'gap' => 15, 'conclusion' => 'Di Atas Standar', 'desc' => 'Kecepatan dan akurasi analisis data angka dan pola kuantitatif.'],
                ['label' => 'Verbal Comprehension', 'value' => 122, 'standard' => 100, 'gap' => 22, 'conclusion' => 'Di Atas Standar', 'desc' => 'Pemahaman komprehensif atas logika bahasa dan teks tertulis.'],
                ['label' => 'Abstract Logic', 'value' => 117, 'standard' => 100, 'gap' => 17, 'conclusion' => 'Di Atas Standar', 'desc' => 'Kemampuan mengidentifikasi hubungan logis dalam bentuk non-verbal.'],
                ['label' => 'Spatial Orientation', 'value' => 116, 'standard' => 100, 'gap' => 16, 'conclusion' => 'Di Atas Standar', 'desc' => 'Visualisasi dan manipulasi objek multi-dimensi dalam ruang.'],
            ],
        ],
        'big_five' => [
            'title' => 'Big Five Personality',
            'subtitle' => 'Inventori Kepribadian Model OCEAN',
            'desc' => 'Mengukur lima dimensi dasar kepribadian untuk memproyeksikan stabilitas emosi, kecenderungan berinteraksi, dan disiplin pencapaian tugas kerja.',
            'average' => 4.12,
            'max_score' => 5.00,
            'scores' => [
                ['label' => 'Openness to Experience', 'value' => 4.10, 'standard' => 3.00, 'gap' => 1.10, 'conclusion' => 'Memenuhi Standar', 'desc' => 'Keterbukaan terhadap ide baru, imajinasi kreatif, dan keragaman pengalaman.'],
                ['label' => 'Conscientiousness', 'value' => 4.50, 'standard' => 3.50, 'gap' => 1.00, 'conclusion' => 'Memenuhi Standar', 'desc' => 'Tingkat disiplin diri, keteraturan kerja, orientasi prestasi, dan keandalan.'],
                ['label' => 'Extraversion', 'value' => 3.80, 'standard' => 3.00, 'gap' => 0.80, 'conclusion' => 'Memenuhi Standar', 'desc' => 'Tingkat kenyamanan dalam interaksi sosial, keaktifan bergaul, dan asertivitas.'],
                ['label' => 'Agreeableness', 'value' => 4.20, 'standard' => 3.00, 'gap' => 1.20, 'conclusion' => 'Memenuhi Standar', 'desc' => 'Kecenderungan untuk kooperatif, berempati, mempercayai, dan membantu orang lain.'],
                ['label' => 'Emotional Stability', 'value' => 4.00, 'standard' => 3.50, 'gap' => 0.50, 'conclusion' => 'Memenuhi Standar', 'desc' => 'Kapasitas mengelola stres, ketenangan emosional, dan ketahanan terhadap tekanan.'],
            ],
        ],
        'learning_agility' => [
            'title' => 'Learning Agility',
            'subtitle' => 'Kelincahan & Adaptabilitas Belajar',
            'desc' => 'Mengukur kelincahan kandidat dalam mempelajari pola baru dan mengaplikasikan pembelajaran masa lalu pada konteks baru.',
            'average' => 4.00,
            'max_score' => 5.00,
            'scores' => [
                ['label' => 'Mental Agility', 'value' => 4.20, 'standard' => 3.00, 'gap' => 1.20, 'conclusion' => 'Memenuhi Standar', 'desc' => 'Kelincahan berpikir dan memecahkan ketidakpastian secara rasional.'],
                ['label' => 'People Agility', 'value' => 3.90, 'standard' => 3.00, 'gap' => 0.90, 'conclusion' => 'Memenuhi Standar', 'desc' => 'Kapasitas berkolaborasi dan memahami dinamika kelompok secara cepat.'],
                ['label' => 'Change Agility', 'value' => 4.10, 'standard' => 3.00, 'gap' => 1.10, 'conclusion' => 'Memenuhi Standar', 'desc' => 'Kesiapan bereksperimen dengan metode baru dan menyukai perubahan.'],
                ['label' => 'Result Agility', 'value' => 3.80, 'standard' => 3.00, 'gap' => 0.80, 'conclusion' => 'Memenuhi Standar', 'desc' => 'Kemampuan memberikan hasil prima dalam situasi transisi atau baru.'],
            ],
        ],
        'leadership_potential' => [
            'title' => 'Leadership Potential',
            'subtitle' => 'Potensi Kepemimpinan & Pengaruh',
            'desc' => 'Proyeksi kapasitas kepemimpinan kandidat untuk memikul tanggung jawab manajerial yang lebih besar.',
            'average' => 3.85,
            'max_score' => 5.00,
            'scores' => [
                ['label' => 'Visioning', 'value' => 4.00, 'standard' => 3.00, 'gap' => 1.00, 'conclusion' => 'Memenuhi Standar', 'desc' => 'Kemampuan merumuskan arah dan target unit kerja jangka panjang.'],
                ['label' => 'Decision Making', 'value' => 3.70, 'standard' => 3.00, 'gap' => 0.70, 'conclusion' => 'Memenuhi Standar', 'desc' => 'Kecepatan dan ketepatan pengambilan keputusan di situasi kritis.'],
                ['label' => 'Strategic Influence', 'value' => 3.90, 'standard' => 3.00, 'gap' => 0.90, 'conclusion' => 'Memenuhi Standar', 'desc' => 'Kekuatan persuasi dan kapasitas merangkul pemangku kepentingan.'],
                ['label' => 'Execution Control', 'value' => 3.80, 'standard' => 3.00, 'gap' => 0.80, 'conclusion' => 'Memenuhi Standar', 'desc' => 'Disiplin mengawal rencana kerja hingga tuntas.'],
                ['label' => 'Coaching & Developing', 'value' => 3.95, 'standard' => 3.00, 'gap' => 0.95, 'conclusion' => 'Memenuhi Standar', 'desc' => 'Kapasitas membimbing dan mempromosikan kapabilitas anggota tim.'],
                ['label' => 'Strategic Thinking', 'value' => 3.75, 'standard' => 3.00, 'gap' => 0.75, 'conclusion' => 'Memenuhi Standar', 'desc' => 'Kemampuan membaca tren eksternal organisasi dan dampaknya.'],
            ],
        ],
        'integrity' => [
            'title' => 'Values & Integrity',
            'subtitle' => 'Integritas Etika & Keselarasan Nilai',
            'desc' => 'Menilai keselarasan perilaku sehari-hari kandidat terhadap kode etik organisasi dan prinsip kejujuran universal.',
            'average' => 4.50,
            'max_score' => 5.00,
            'scores' => [
                ['label' => 'Honesty & Transparency', 'value' => 4.60, 'standard' => 3.50, 'gap' => 1.10, 'conclusion' => 'Memenuhi Standar', 'desc' => 'Keterbukaan dan kejujuran dalam menyampaikan fakta tanpa distorsi.'],
                ['label' => 'Ethical Compliance', 'value' => 4.50, 'standard' => 3.50, 'gap' => 1.00, 'conclusion' => 'Memenuhi Standar', 'desc' => 'Ketaatan total terhadap regulasi dan prinsip etika korporasi.'],
                ['label' => 'Accountability', 'value' => 4.40, 'standard' => 3.50, 'gap' => 0.90, 'conclusion' => 'Memenuhi Standar', 'desc' => 'Keberanian bertanggung jawab atas hasil keputusan kerja sendiri.'],
                ['label' => 'Consistency', 'value' => 4.50, 'standard' => 3.50, 'gap' => 1.00, 'conclusion' => 'Memenuhi Standar', 'desc' => 'Kesesuaian antara ucapan dan tindakan nyata di lapangan.'],
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
            return Participant::with(['positionFormation.template', 'finalAssessment', 'testResults'])->first();
        }

        return Participant::with(['positionFormation.template', 'finalAssessment', 'testResults'])->find($this->participantId);
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

    /**
     * Get dynamic IQ & Cognitive Profile data from SPSP database.
     */
    private function getCognitiveData(): array
    {
        $participant = $this->participant;

        if (! $participant) {
            return $this->datasets['cognitive'];
        }

        // 1. Cek test_results untuk instrumen kognitif (CFIT atau IST)
        $iqTest = $participant->testResults()
            ->where(function ($q) {
                $q->where('test_name', 'like', '%CFIT%')
                    ->orWhere('test_name', 'like', '%IST%')
                    ->orWhere('test_code', 'like', '%CFIT%')
                    ->orWhere('test_code', 'like', '%IST%');
            })
            ->first();

        $cfitIq = null;
        $cfitCategory = null;
        if ($iqTest && ! empty($iqTest->summary_data)) {
            $summary = $iqTest->summary_data;
            $cfitIq = $summary['total_iq'] ?? $summary['iq_score'] ?? $summary['iq'] ?? null;
            $cfitCategory = $summary['category'] ?? $summary['kategori_iq'] ?? null;
        }

        // 2. Ambil sub-aspek di bawah aspek "Intelektual" atau "Daya Pikir"
        $subAssessments = SubAspectAssessment::where('participant_id', $participant->id)
            ->whereHas('subAspect.aspect', function ($q) {
                $q->where('name', 'like', '%intelektual%')
                    ->orWhere('name', 'like', '%daya pikir%')
                    ->orWhere('code', 'like', '%intelektual%')
                    ->orWhere('code', 'like', '%daya_pikir%');
            })
            ->with('subAspect')
            ->get();

        if ($subAssessments->isNotEmpty()) {
            $scores = $subAssessments->map(function ($item) {
                $val = (float) $item->individual_rating;
                $std = (float) ($item->standard_rating ?? 3.00);
                $gap = $val - $std;
                $conclusion = match (true) {
                    $gap > 0 => 'Di Atas Standar',
                    $gap === 0.0 => 'Memenuhi Standar',
                    default => 'Di Bawah Standar',
                };

                return [
                    'label' => $item->subAspect?->name ?? 'Sub-Aspek Kognitif',
                    'value' => $val,
                    'standard' => $std,
                    'gap' => $gap,
                    'conclusion' => $conclusion,
                    'desc' => $item->subAspect?->description ?? 'Kapasitas pemrosesan kognitif spesifik.',
                ];
            })->toArray();

            $avgRating = (float) $subAssessments->avg('individual_rating');
            $calculatedIq = (int) ($cfitIq ?? round(100 + ($avgRating - 3.0) * 15));

            return [
                'title' => 'IQ & Profil Kognitif',
                'subtitle' => 'Kapasitas Berpikir & Kemampuan Logika',
                'desc' => "Kandidat memiliki estimasi kapasitas inteligensi setara IQ {$calculatedIq}".($cfitCategory ? " ({$cfitCategory})" : '').'. Menunjukkan profil menyeluruh kecepatan pemrosesan informasi, kapasitas kognitif umum, serta inteligensi numerik, verbal, dan penalaran abstrak.',
                'average' => round($avgRating, 2),
                'max_score' => 5.00,
                'is_iq' => false,
                'scores' => $scores,
            ];
        }

        return $this->datasets['cognitive'];
    }

    /**
     * Get dynamic Big Five Personality (OCEAN) data from SPSP database.
     */
    private function getBigFiveData(): array
    {
        $participant = $this->participant;

        if (! $participant) {
            return $this->datasets['big_five'];
        }

        // 1. Cek test 16PF (test_code B.2)
        $sixteenPfTest = $participant->testResults()->where('test_code', 'B.2')->first();
        $na = $sixteenPfTest?->summary_data['nilaiAspek'] ?? [];

        if (! empty($na)) {
            // Konversi sten score (1-10) ke rating skala (1-5)
            $openness = round(((($na['M'] ?? 5) + ($na['Q1'] ?? 5) + ($na['I'] ?? 5)) / 3) / 2, 2);
            $conscientiousness = round(((($na['G'] ?? 5) + ($na['Q3'] ?? 5)) / 2) / 2, 2);
            $extraversion = round(((($na['A'] ?? 5) + ($na['F'] ?? 5) + ($na['H'] ?? 5)) / 3) / 2, 2);
            $agreeableness = round(((($na['A'] ?? 5) + (11 - ($na['L'] ?? 5)) + (11 - ($na['E'] ?? 5))) / 3) / 2, 2);
            $emotionalStability = round(((($na['C'] ?? 5) + (11 - ($na['O'] ?? 5)) + (11 - ($na['Q4'] ?? 5))) / 3) / 2, 2);

            $scores = [
                [
                    'label' => 'Openness to Experience',
                    'value' => min(5.00, max(1.00, $openness)),
                    'standard' => 3.00,
                    'gap' => round($openness - 3.00, 2),
                    'conclusion' => $openness >= 3.00 ? 'Memenuhi Standar' : 'Perlu Penguatan',
                    'desc' => 'Keterbukaan terhadap ide baru, pemikiran konseptual-kreatif, dan fleksibilitas beradaptasi.',
                ],
                [
                    'label' => 'Conscientiousness',
                    'value' => min(5.00, max(1.00, $conscientiousness)),
                    'standard' => 3.50,
                    'gap' => round($conscientiousness - 3.50, 2),
                    'conclusion' => $conscientiousness >= 3.50 ? 'Memenuhi Standar' : 'Perlu Penguatan',
                    'desc' => 'Tingkat disiplin diri, keteraturan kerja, orientasi penyelesaian tugas, dan ketelitian detail.',
                ],
                [
                    'label' => 'Extraversion',
                    'value' => min(5.00, max(1.00, $extraversion)),
                    'standard' => 3.00,
                    'gap' => round($extraversion - 3.00, 2),
                    'conclusion' => $extraversion >= 3.00 ? 'Memenuhi Standar' : 'Kecenderungan Introvert',
                    'desc' => 'Kenyamanan dalam interaksi sosial, inisiatif komunikasi interpersonal, dan asertivitas.',
                ],
                [
                    'label' => 'Agreeableness',
                    'value' => min(5.00, max(1.00, $agreeableness)),
                    'standard' => 3.00,
                    'gap' => round($agreeableness - 3.00, 2),
                    'conclusion' => $agreeableness >= 3.00 ? 'Memenuhi Standar' : 'Perlu Penguatan Empati',
                    'desc' => 'Kecenderungan kooperatif, empati terhadap rekan kerja, dan kemampuan memelihara iklim kolaboratif.',
                ],
                [
                    'label' => 'Emotional Stability',
                    'value' => min(5.00, max(1.00, $emotionalStability)),
                    'standard' => 3.50,
                    'gap' => round($emotionalStability - 3.50, 2),
                    'conclusion' => $emotionalStability >= 3.50 ? 'Memenuhi Standar' : 'Sensitif terhadap Tekanan',
                    'desc' => 'Kapasitas mengelola stres, ketenangan emosional di bawah tekanan kerja, dan resiliensi psikologis.',
                ],
            ];

            $avg = round((float) collect($scores)->avg('value'), 2);

            return [
                'title' => 'Big Five Personality',
                'subtitle' => 'Inventori Kepribadian Model OCEAN',
                'desc' => "Evaluasi kepribadian komparatif model OCEAN {$participant->name} menghasilkan rata-rata skor kestabilan kepribadian {$avg} dari skala 5.00. Menggambarkan pola kecenderungan perilaku, disiplin eksekusi, serta ketahanan emosional dalam dinamika kerja.",
                'average' => $avg,
                'max_score' => 5.00,
                'scores' => $scores,
            ];
        }

        return $this->datasets['big_five'];
    }

    /**
     * Get dynamic Learning Agility data from SPSP database.
     */
    private function getLearningAgilityData(): array
    {
        $participant = $this->participant;

        if (! $participant) {
            return $this->datasets['learning_agility'];
        }

        $sub = SubAspectAssessment::where('participant_id', $participant->id)->with('subAspect')->get();

        if ($sub->isNotEmpty()) {
            $getVal = fn (array $names) => (float) ($sub->filter(fn ($s) => in_array($s->subAspect?->name ?? '', $names))->avg('individual_rating') ?? 3.80);

            $mental = round($getVal(['Daya Analisa', 'Logika Berpikir', 'Kreativitas', 'Daya Abstraksi']), 2);
            $people = round($getVal(['Sosualitas', 'Komunikasi Sosial', 'Kontak Sosial', 'Kepekaan Interpersonal']), 2);
            $change = round($getVal(['Penyesuaian Diri', 'Agen Perubahan', 'Mobilitas', 'Inisiatif']), 2);
            $result = round($getVal(['Hasrat Berprestasi', 'Daya Tahan Kerja', 'Semangat Kerja', 'Result Focus']), 2);

            $scores = [
                ['label' => 'Mental Agility', 'value' => $mental ?: 4.20, 'standard' => 3.00, 'gap' => round(($mental ?: 4.20) - 3.00, 2), 'conclusion' => ($mental ?: 4.20) >= 3.00 ? 'Memenuhi Standar' : 'Perlu Penguatan', 'desc' => 'Kelincahan berpikir dan memecahkan ketidakpastian secara rasional.'],
                ['label' => 'People Agility', 'value' => $people ?: 3.90, 'standard' => 3.00, 'gap' => round(($people ?: 3.90) - 3.00, 2), 'conclusion' => ($people ?: 3.90) >= 3.00 ? 'Memenuhi Standar' : 'Perlu Penguatan', 'desc' => 'Kapasitas berkolaborasi dan memahami dinamika kelompok secara cepat.'],
                ['label' => 'Change Agility', 'value' => $change ?: 4.10, 'standard' => 3.00, 'gap' => round(($change ?: 4.10) - 3.00, 2), 'conclusion' => ($change ?: 4.10) >= 3.00 ? 'Memenuhi Standar' : 'Perlu Penguatan', 'desc' => 'Kesiapan bereksperimen dengan metode baru dan menyukai perubahan.'],
                ['label' => 'Result Agility', 'value' => $result ?: 3.80, 'standard' => 3.00, 'gap' => round(($result ?: 3.80) - 3.00, 2), 'conclusion' => ($result ?: 3.80) >= 3.00 ? 'Memenuhi Standar' : 'Perlu Penguatan', 'desc' => 'Kemampuan memberikan hasil prima dalam situasi transisi atau baru.'],
            ];

            $avg = round((float) collect($scores)->avg('value'), 2);

            return [
                'title' => 'Learning Agility',
                'subtitle' => 'Kelincahan & Adaptabilitas Belajar',
                'desc' => "Kandidat {$participant->name} memiliki skor Learning Agility rata-rata {$avg} dari skala 5.00. Menunjukkan kelincahan menyerap wawasan baru, fleksibilitas adaptasi perubahan, dan kecepatan berprestasi dalam situasi kompleks.",
                'average' => $avg,
                'max_score' => 5.00,
                'scores' => $scores,
            ];
        }

        return $this->datasets['learning_agility'];
    }

    /**
     * Get dynamic Leadership Potential data from SPSP database.
     */
    private function getLeadershipPotentialData(): array
    {
        $participant = $this->participant;

        if (! $participant) {
            return $this->datasets['leadership_potential'];
        }

        $sub = SubAspectAssessment::where('participant_id', $participant->id)->with('subAspect')->get();

        if ($sub->isNotEmpty()) {
            $getVal = fn (array $names) => (float) ($sub->filter(fn ($s) => in_array($s->subAspect?->name ?? '', $names))->avg('individual_rating') ?? 4.00);

            $visioning = round($getVal(['Vision Clarity', 'Direction Setting', 'Perencanaan']), 2);
            $decision = round($getVal(['Pembuatan Keputusan', 'Pemecahan Masalah', 'Identifikasi Masalah']), 2);
            $influence = round($getVal(['Mempengaruhi', 'Communication', 'Komunikasi']), 2);
            $execution = round($getVal(['Planning & Organizing', 'Measurement', 'Pengendalian', 'Sistematika Kerja']), 2);
            $coaching = round($getVal(['Mengarahkan', 'Koordinasi', 'Kerjasama']), 2);
            $strategic = round($getVal(['Kepemimpinan', 'Agen Perubahan', 'Analisa dan Sintesa', 'Result Focus']), 2);

            $scores = [
                ['label' => 'Visioning', 'value' => $visioning ?: 4.00, 'standard' => 3.00, 'gap' => round(($visioning ?: 4.00) - 3.00, 2), 'conclusion' => 'Memenuhi Standar', 'desc' => 'Kemampuan merumuskan arah dan target unit kerja jangka panjang.'],
                ['label' => 'Decision Making', 'value' => $decision ?: 3.70, 'standard' => 3.00, 'gap' => round(($decision ?: 3.70) - 3.00, 2), 'conclusion' => 'Memenuhi Standar', 'desc' => 'Kecepatan dan ketepatan pengambilan keputusan di situasi kritis.'],
                ['label' => 'Strategic Influence', 'value' => $influence ?: 3.90, 'standard' => 3.00, 'gap' => round(($influence ?: 3.90) - 3.00, 2), 'conclusion' => 'Memenuhi Standar', 'desc' => 'Kekuatan persuasi dan kapasitas merangkul pemangku kepentingan.'],
                ['label' => 'Execution Control', 'value' => $execution ?: 3.80, 'standard' => 3.00, 'gap' => round(($execution ?: 3.80) - 3.00, 2), 'conclusion' => 'Memenuhi Standar', 'desc' => 'Disiplin mengawal rencana kerja hingga tuntas.'],
                ['label' => 'Coaching & Developing', 'value' => $coaching ?: 3.95, 'standard' => 3.00, 'gap' => round(($coaching ?: 3.95) - 3.00, 2), 'conclusion' => 'Memenuhi Standar', 'desc' => 'Kapasitas membimbing dan mempromosikan kapabilitas anggota tim.'],
                ['label' => 'Strategic Thinking', 'value' => $strategic ?: 3.75, 'standard' => 3.00, 'gap' => round(($strategic ?: 3.75) - 3.00, 2), 'conclusion' => 'Memenuhi Standar', 'desc' => 'Kemampuan membaca tren eksternal organisasi dan dampaknya.'],
            ];

            $avg = round((float) collect($scores)->avg('value'), 2);

            return [
                'title' => 'Leadership Potential',
                'subtitle' => 'Potensi Kepemimpinan & Pengaruh',
                'desc' => "Proyeksi kepemimpinan {$participant->name} menghasilkan indeks rata-rata {$avg} dari skala 5.00. Mengukur kesiapan memikul tanggung jawab manajerial, pengarahan visi strategis, dan pengawalan eksekusi tim kerja.",
                'average' => $avg,
                'max_score' => 5.00,
                'scores' => $scores,
            ];
        }

        return $this->datasets['leadership_potential'];
    }

    /**
     * Get dynamic Values & Integrity data from SPSP database.
     */
    private function getIntegrityData(): array
    {
        $participant = $this->participant;

        if (! $participant) {
            return $this->datasets['integrity'];
        }

        $sub = SubAspectAssessment::where('participant_id', $participant->id)->with('subAspect')->get();

        if ($sub->isNotEmpty()) {
            $getVal = fn (array $names) => (float) ($sub->filter(fn ($s) => in_array($s->subAspect?->name ?? '', $names))->avg('individual_rating') ?? 4.50);

            $honesty = round($getVal(['Kejujuran', 'Integritas']), 2);
            $compliance = round($getVal(['Kedisiplinan', 'Sistematika Kerja']), 2);
            $accountability = round($getVal(['Tanggung Jawab', 'Commitment']), 2);
            $consistency = round($getVal(['Loyalitas', 'Kestabilan Kerja']), 2);

            $scores = [
                ['label' => 'Honesty & Transparency', 'value' => $honesty ?: 4.60, 'standard' => 3.50, 'gap' => round(($honesty ?: 4.60) - 3.50, 2), 'conclusion' => 'Memenuhi Standar', 'desc' => 'Keterbukaan dan kejujuran dalam menyampaikan fakta tanpa distorsi.'],
                ['label' => 'Ethical Compliance', 'value' => $compliance ?: 4.50, 'standard' => 3.50, 'gap' => round(($compliance ?: 4.50) - 3.50, 2), 'conclusion' => 'Memenuhi Standar', 'desc' => 'Ketaatan total terhadap regulasi dan prinsip etika korporasi.'],
                ['label' => 'Accountability', 'value' => $accountability ?: 4.40, 'standard' => 3.50, 'gap' => round(($accountability ?: 4.40) - 3.50, 2), 'conclusion' => 'Memenuhi Standar', 'desc' => 'Keberanian bertanggung jawab atas hasil keputusan kerja sendiri.'],
                ['label' => 'Consistency', 'value' => $consistency ?: 4.50, 'standard' => 3.50, 'gap' => round(($consistency ?: 4.50) - 3.50, 2), 'conclusion' => 'Memenuhi Standar', 'desc' => 'Kesesuaian antara ucapan dan tindakan nyata di lapangan.'],
            ];

            $avg = round((float) collect($scores)->avg('value'), 2);

            return [
                'title' => 'Values & Integrity',
                'subtitle' => 'Integritas Etika & Keselarasan Nilai',
                'desc' => "Evaluasi keselarasan nilai dan integritas {$participant->name} menghasilkan rata-rata skor {$avg} dari skala 5.00. Menggambarkan konsistensi kepatuhan etika, tanggung jawab moral, dan akuntabilitas kerja.",
                'average' => $avg,
                'max_score' => 5.00,
                'scores' => $scores,
            ];
        }

        return $this->datasets['integrity'];
    }

    public function render(): View
    {
        $data = match ($this->sectionCode) {
            'competency' => $this->getCompetencyData(),
            'cognitive' => $this->getCognitiveData(),
            'big_five' => $this->getBigFiveData(),
            'learning_agility' => $this->getLearningAgilityData(),
            'leadership_potential' => $this->getLeadershipPotentialData(),
            'integrity' => $this->getIntegrityData(),
            default => $this->datasets[$this->sectionCode] ?? $this->datasets['competency'],
        };

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
