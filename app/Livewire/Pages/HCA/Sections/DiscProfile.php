<?php

declare(strict_types=1);

namespace App\Livewire\Pages\HCA\Sections;

use App\Models\Participant;
use Illuminate\View\View;
use Livewire\Attributes\Reactive;
use Livewire\Component;

class DiscProfile extends Component
{
    #[Reactive]
    public ?int $participantId = null;

    public function getParticipantProperty(): ?Participant
    {
        if (! $this->participantId) {
            return Participant::with(['testResults'])->first();
        }

        return Participant::with(['testResults'])->find($this->participantId);
    }

    public function render(): View
    {
        $participant = $this->participant;

        $papiTest = $participant?->testResults()->where('test_code', 'D.1')->first();
        $na = $papiTest?->summary_data['nilaiAspek'] ?? [];

        if (! empty($na)) {
            $scores = [
                'D' => (int) (($na['L'] ?? 3) + ($na['P'] ?? 3) + ($na['G'] ?? 3)),
                'I' => (int) (($na['S'] ?? 3) + ($na['X'] ?? 3) + ($na['V'] ?? 3)),
                'S' => (int) (($na['K'] ?? 3) + ($na['C'] ?? 3) + ($na['E'] ?? 3)),
                'C' => (int) (($na['D'] ?? 3) + ($na['R'] ?? 3) + ($na['W'] ?? 3)),
            ];
        } else {
            // Default distribution jika alat tes belum tersedia
            $scores = ['D' => 7, 'I' => 9, 'S' => 6, 'C' => 5];
        }

        arsort($scores);
        $dominantCode = (string) array_key_first($scores);

        $styleNames = [
            'D' => 'Dominance',
            'I' => 'Influence',
            'S' => 'Steadiness',
            'C' => 'Compliance',
        ];

        $dominantStyle = $styleNames[$dominantCode] ?? 'Influence';

        $quadrants = [
            [
                'code' => 'D',
                'name' => 'Dominance',
                'label' => 'Mengarahkan & Orientasi Hasil',
                'score' => $scores['D'] ?? 0,
                'desc' => 'Menekankan pencapaian hasil, tantangan baru, keberanian mengambil inisiatif cepat, dan ketegasan mengendalikan situasi kerja.',
                'isDominant' => $dominantCode === 'D',
            ],
            [
                'code' => 'I',
                'name' => 'Influence',
                'label' => 'Mempengaruhi & Membangun Relasi',
                'score' => $scores['I'] ?? 0,
                'desc' => 'Sangat persuasif, antusias, gemar berkolaborasi, membangun relasi interpersonal yang hangat, dan memotivasi tim secara ekspresif.',
                'isDominant' => $dominantCode === 'I',
            ],
            [
                'code' => 'S',
                'name' => 'Steadiness',
                'label' => 'Mendukung & Harmoni Tim',
                'score' => $scores['S'] ?? 0,
                'desc' => 'Mengutamakan kestabilan kerja, kesabaran mendengarkan, kesetiaan kelompok, serta kerja sama tim yang tenang dan dapat diprediksi.',
                'isDominant' => $dominantCode === 'S',
            ],
            [
                'code' => 'C',
                'name' => 'Compliance',
                'label' => 'Menganalisis & Kepatuhan Standar',
                'score' => $scores['C'] ?? 0,
                'desc' => 'Fokus pada keakuratan data, pemenuhan standar prosedur operasional secara disiplin, logika analitik, dan kontrol kualitas tinggi.',
                'isDominant' => $dominantCode === 'C',
            ],
        ];

        $name = $participant?->name ?? 'Kandidat';
        $interpretation = match ($dominantCode) {
            'D' => "{$name} menunjukkan gaya kerja Dominance yang kuat: berorientasi tinggi pada sasaran, berani mengambil keputusan cepat dalam situasi menekan, dan piawai menggerakkan eksekusi strategi operasional.",
            'I' => "{$name} sangat mahir mempengaruhi lingkungan kerjanya secara persuasif dan membangun jejaring. Gaya ini sangat prima untuk peran kepemimpinan yang membutuhkan kolaborasi dinamis lintas unit kerja.",
            'S' => "{$name} memiliki gaya kerja Steadiness yang konsisten: menjadi perekat harmoni kelompok, sabar dalam merawat kesinambungan proses kerja, dan sangat andal dalam kerja tim berjangka panjang.",
            'C' => "{$name} menekankan ketelitian analitis dan kepatuhan regulasi yang ketat: sangat efektif dalam mitigasi risiko, pengendalian mutu, dan pemastian tata kelola organisasi yang akuntabel.",
            default => "{$name} memiliki fleksibilitas adaptasi gaya perilaku kerja yang seimbang.",
        };

        return view('livewire.pages.h-c-a.sections.disc-profile', [
            'dominantCode' => $dominantCode,
            'dominantStyle' => $dominantStyle,
            'quadrants' => $quadrants,
            'interpretation' => $interpretation,
            'participant' => $participant,
        ]);
    }
}
