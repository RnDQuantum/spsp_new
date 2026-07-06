<?php

declare(strict_types=1);

namespace App\Livewire\HcaReport\Sections;

use Livewire\Component;
use Illuminate\View\View;

class QualitativeListSection extends Component
{
    public array $strengths = [
        [
            'title' => 'Resiliensi Tinggi & Keuletan Mental',
            'icon' => 'fa-shield-halved',
            'tag' => 'Mental Toughness',
            'desc' => 'Menunjukkan kapasitas luar biasa untuk tetap tenang dan fokus di bawah tekanan tinggi. Mampu memimpin tim keluar dari krisis operasional dan cepat beradaptasi dengan perubahan strategis tanpa kehilangan produktivitas.'
        ],
        [
            'title' => 'Kemampuan Visioning & Berpikir Strategis',
            'icon' => 'fa-compass',
            'tag' => 'Leadership',
            'desc' => 'Memiliki visi jangka panjang yang jelas untuk pengembangan organisasi. Mampu mengidentifikasi peluang pasar masa depan dan menerjemahkan rencana makro menjadi peta jalan taktis yang terukur bagi unit kerjanya.'
        ],
        [
            'title' => 'Kelincahan Belajar Tinggi (Learning Agility)',
            'icon' => 'fa-bolt',
            'tag' => 'Cognitive Agility',
            'desc' => 'Sangat cepat dalam menyerap konsep-konsep baru, teknologi digital, atau domain bisnis yang belum dikenal. Menggunakan pembelajaran masa lalu sebagai kerangka kerja baru untuk memecahkan masalah kompleks yang tidak terstruktur.'
        ],
        [
            'title' => 'Komunikasi & Pengaruh Strategis (Strategic Influence)',
            'icon' => 'fa-comments',
            'tag' => 'Interpersonal',
            'desc' => 'Mahir menyederhanakan data analitik yang rumit menjadi presentasi eksekutif yang persuasif. Memiliki kemampuan bernegosiasi yang kuat dan mampu menyatukan berbagai pemangku kepentingan untuk mendukung inisiatif penting.'
        ],
        [
            'title' => 'Integritas & Konsistensi Perilaku',
            'icon' => 'fa-award',
            'tag' => 'Core Values',
            'desc' => 'Menunjukkan komitmen tanpa kompromi terhadap nilai-nilai etika organisasi. Menyelaraskan perkataan dan tindakan dalam pengambilan keputusan sehari-hari, sehingga menumbuhkan kepercayaan yang kuat di mata tim.'
        ]
    ];

    public function render(): View
    {
        return view('livewire.hca-report.sections.qualitative-list-section');
    }
}
