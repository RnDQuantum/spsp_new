<?php

declare(strict_types=1);

namespace App\Livewire\HcaReport\Sections;

use Livewire\Component;
use Illuminate\View\View;

class ScoreListSection extends Component
{
    public string $sectionCode = '';

    /**
     * Datasets for each parameterized score list section
     */
    public array $datasets = [
        'competency' => [
            'title' => 'Layer 1: Kompetensi',
            'subtitle' => 'Hard Evidence & Manajerial',
            'desc' => 'Hasil evaluasi komparatif tingkat kompetensi manajerial, sosial, teknis, digital, dan kepemimpinan peserta.',
            'average' => 3.90,
            'max_score' => 5.00,
            'scores' => [
                ['label' => 'Kompetensi Teknis', 'value' => 4.10, 'desc' => 'Penguasaan keahlian fungsional dan operasional sesuai bidang tugas.'],
                ['label' => 'Kompetensi Manajerial', 'value' => 3.80, 'desc' => 'Kemampuan mengelola tim, merencanakan kerja, dan mengeksekusi target.'],
                ['label' => 'Kompetensi Sosial Kultural', 'value' => 4.00, 'desc' => 'Kemampuan berinteraksi dengan latar belakang budaya dan nilai sosial.'],
                ['label' => 'Kompetensi Digital', 'value' => 3.70, 'desc' => 'Pemanfaatan instrumen digital untuk meningkatkan produktivitas.'],
                ['label' => 'Kompetensi Kepemimpinan', 'value' => 3.90, 'desc' => 'Kapasitas mengarahkan, memotivasi, dan mendelegasikan tanggung jawab.'],
            ]
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
            ]
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
            ]
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
            ]
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
            ]
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
            ]
        ],
    ];

    public function mount(string $sectionCode): void
    {
        $this->sectionCode = $sectionCode;
    }

    public function render(): View
    {
        $data = $this->datasets[$this->sectionCode] ?? $this->datasets['competency'];
        return view('livewire.hca-report.sections.score-list-section', [
            'title' => $data['title'],
            'subtitle' => $data['subtitle'],
            'desc' => $data['desc'],
            'average' => $data['average'],
            'max_score' => $data['max_score'],
            'is_iq' => $data['is_iq'] ?? false,
            'scores' => $data['scores']
        ]);
    }
}
