<?php

declare(strict_types=1);

namespace App\Livewire\Pages\HCA\Sections;

use Livewire\Component;
use Illuminate\View\View;

class TimelineSection extends Component
{
    public array $timeline = [
        [
            'period' => '2024 — Sekarang',
            'role' => 'VP of Talent Development',
            'unit' => 'Divisi Human Capital, Head Office',
            'achievements' => [
                'Sukses merancang dan meluncurkan Sistem Pemetaan Staf Integratif (SPSP) berskala nasional.',
                'Meningkatkan akurasi identifikasi suksesi kepemimpinan tingkat eksekutif sebesar 35%.',
                'Mengurangi turnover talenta kunci (key talent) di bawah 3% pertahun melalui program retention plan terarah.'
            ]
        ],
        [
            'period' => '2021 — 2024',
            'role' => 'Senior Manager, People Development',
            'unit' => 'Divisi Human Capital, Head Office',
            'achievements' => [
                'Mendesain kurikulum kepemimpinan eksekutif untuk 200+ staf manajemen tingkat menengah.',
                'Menyusun kerangka kompetensi kepemimpinan baru yang berorientasi pada transformasi digital.',
                'Meningkatkan efisiensi anggaran pelatihan internal sebesar 18% dengan transisi ke blended learning.'
            ]
        ],
        [
            'period' => '2018 — 2021',
            'role' => 'Manager, Organization Design',
            'unit' => 'Divisi Organisasi & Tata Kerja',
            'achievements' => [
                'Memimpin restrukturisasi organisasi divisi operasional utama, menyederhanakan jalur pelaporan.',
                'Mengurangi tumpang tindih peran operasional yang menghasilkan efisiensi biaya kerja sebesar 12%.',
                'Merumuskan deskripsi pekerjaan berbasis output (result-oriented job descriptions) secara menyeluruh.'
            ]
        ],
        [
            'period' => '2015 — 2018',
            'role' => 'Senior HR Specialist, Talent Acquisition',
            'unit' => 'Sub-Divisi Rekrutmen & Seleksi',
            'achievements' => [
                'Menerapkan sistem penyaringan seleksi berbasis kompetensi terintegrasi untuk posisi manajerial.',
                'Mempercepat proses pemenuhan kebutuhan posisi kunci (time-to-fill) dari 45 hari menjadi 30 hari.',
                'Membangun program magang kemitraan universitas kelas satu untuk suplai talenta muda.'
            ]
        ]
    ];

    public function render(): View
    {
        return view('livewire.pages.h-c-a.sections.timeline-section');
    }
}
