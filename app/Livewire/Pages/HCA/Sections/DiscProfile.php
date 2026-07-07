<?php

declare(strict_types=1);

namespace App\Livewire\Pages\HCA\Sections;

use Livewire\Component;
use Illuminate\View\View;

class DiscProfile extends Component
{
    public string $dominantStyle = 'Influence'; // Dominant style
    
    public array $quadrants = [
        [
            'code' => 'D',
            'name' => 'Dominance',
            'label' => 'Mengarahkan & Hasil',
            'desc' => 'Menekankan pencapaian hasil, tantangan baru, keberanian mengambil inisiatif cepat, dan mengendalikan lingkungan sekitar.',
            'isDominant' => false
        ],
        [
            'code' => 'I',
            'name' => 'Influence',
            'label' => 'Mempengaruhi & Rapat',
            'desc' => 'Sangat persuasif, antusias, gemar berkolaborasi, membangun relasi interpersonal yang hangat, dan memotivasi tim secara ekspresif.',
            'isDominant' => true
        ],
        [
            'code' => 'S',
            'name' => 'Steadiness',
            'label' => 'Mendukung & Harmoni',
            'desc' => 'Mengutamakan kestabilan kerja, kesabaran mendengarkan, kesetiaan kelompok, serta kerja sama tim yang tenang dan dapat diprediksi.',
            'isDominant' => false
        ],
        [
            'code' => 'C',
            'name' => 'Compliance',
            'label' => 'Menganalisis & Akurat',
            'desc' => 'Fokus pada keakuratan data, pemenuhan standar prosedur operasional secara disiplin, logika analitik, dan kontrol kualitas tinggi.',
            'isDominant' => false
        ],
    ];

    public function render(): View
    {
        return view('livewire.pages.h-c-a.sections.disc-profile');
    }
}
