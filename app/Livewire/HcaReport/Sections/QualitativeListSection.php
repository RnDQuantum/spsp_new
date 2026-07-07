<?php

declare(strict_types=1);

namespace App\Livewire\HcaReport\Sections;

use Livewire\Component;
use Illuminate\View\View;

class QualitativeListSection extends Component
{
    public string $sectionCode = 'strengths';

    public array $datasets = [
        'strengths' => [
            'title' => 'Kekuatan Psikologis',
            'subtitle' => 'Karakter & Potensi Dominan',
            'desc' => 'Rangkuman aspek kekuatan personal berbasis pengamatan perilaku dan hasil asesmen psikologis terstandar.',
            'items' => [
                [
                    'title' => 'Resiliensi Tinggi & Keuletan Mental',
                    'icon' => 'fa-shield-halved',
                    'tag' => 'Mental Toughness',
                    'desc' => 'Menunjukkan kapasitas luar biasa untuk tetap tenang dan fokus di bawah tekanan tinggi. Mampu memimpin tim keluar dari krisis operasional.'
                ],
                [
                    'title' => 'Kemampuan Visioning & Berpikir Strategis',
                    'icon' => 'fa-compass',
                    'tag' => 'Leadership',
                    'desc' => 'Memiliki visi jangka panjang yang jelas untuk pengembangan organisasi. Mampu mengidentifikasi peluang pasar masa depan.'
                ],
                [
                    'title' => 'Kelincahan Belajar Tinggi (Learning Agility)',
                    'icon' => 'fa-bolt',
                    'tag' => 'Cognitive Agility',
                    'desc' => 'Sangat cepat dalam menyerap konsep-konsep baru, teknologi digital, atau domain bisnis yang belum dikenal.'
                ],
                [
                    'title' => 'Komunikasi & Pengaruh Strategis (Strategic Influence)',
                    'icon' => 'fa-comments',
                    'tag' => 'Interpersonal',
                    'desc' => 'Mahir menyederhanakan data analitik yang rumit menjadi presentasi eksekutif yang persuasif.'
                ],
                [
                    'title' => 'Integritas & Konsistensi Perilaku',
                    'icon' => 'fa-award',
                    'tag' => 'Core Values',
                    'desc' => 'Menunjukkan komitmen tanpa kompromi terhadap nilai-nilai etika organisasi.'
                ]
            ]
        ],
        'personal_profile' => [
            'title' => 'Profil Personal (Pelengkap)',
            'subtitle' => 'Atribut Pendukung Non-Asesmen',
            'desc' => 'Catatan penunjang mengenai preferensi pribadi dan atribut kebudayaan/sosial kandidat. Section ini bersifat opsional dan informatif.',
            'is_personal' => true,
            'items' => [
                [
                    'title' => 'Olahraga & Hobi',
                    'icon' => 'fa-dumbbell',
                    'tag' => 'Active Life',
                    'desc' => 'Aktif bermain Tennis Lapangan seminggu sekali dan menggemari permainan Catur Strategis di waktu senggang.'
                ],
                [
                    'title' => 'Golongan Darah',
                    'icon' => 'fa-droplet',
                    'tag' => 'Medical Reference',
                    'desc' => 'Golongan Darah O+ (Positif). Catatan kesehatan umum menunjukkan kondisi prima tanpa riwayat penyakit kronis.'
                ],
                [
                    'title' => 'Zodiak & Shio',
                    'icon' => 'fa-moon',
                    'tag' => 'Cultural Profile',
                    'desc' => 'Leo / Naga. Menunjukkan karakter bawaan yang ekspresif, berani mengambil keputusan, dan berjiwa pemimpin.'
                ],
                [
                    'title' => 'Weton Jawa',
                    'icon' => 'fa-calendar-day',
                    'tag' => 'Tradition',
                    'desc' => 'Kamis Kliwon. Dalam filosofi tradisional, dikaitkan dengan keteguhan hati, pemikiran mendalam, dan keberuntungan kepemimpinan.'
                ]
            ]
        ]
    ];

    public function mount(string $sectionCode = 'strengths'): void
    {
        $this->sectionCode = $sectionCode;
    }

    public function render(): View
    {
        $data = $this->datasets[$this->sectionCode] ?? $this->datasets['strengths'];
        return view('livewire.hca-report.sections.qualitative-list-section', [
            'title' => $data['title'],
            'subtitle' => $data['subtitle'],
            'desc' => $data['desc'],
            'is_personal' => $data['is_personal'] ?? false,
            'items' => $data['items']
        ]);
    }
}
