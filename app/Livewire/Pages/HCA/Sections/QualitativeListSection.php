<?php

declare(strict_types=1);

namespace App\Livewire\Pages\HCA\Sections;

use App\Models\AspectAssessment;
use App\Models\Participant;
use App\Services\HcaDataService;
use Illuminate\View\View;
use Livewire\Attributes\On;
use Livewire\Attributes\Reactive;
use Livewire\Component;

class QualitativeListSection extends Component
{
    #[Reactive]
    public ?int $participantId = null;

    public string $sectionCode = 'strengths';

    #[On('hca-data-updated')]
    public function onDataUpdated(): void
    {
        // Re-renders component on data update
    }

    public function mount(string $sectionCode = 'strengths'): void
    {
        $this->sectionCode = $sectionCode;
    }

    public function render(): View
    {
        $participant = app(HcaDataService::class)->getParticipant($this->participantId);

        if ($this->sectionCode === 'personal_profile') {
            $data = $this->buildPersonalProfileData($participant);
        } else {
            $data = $this->buildStrengthsData($participant);
        }

        return view('livewire.pages.h-c-a.sections.qualitative-list-section', [
            'title' => $data['title'],
            'subtitle' => $data['subtitle'],
            'desc' => $data['desc'],
            'is_personal' => $data['is_personal'] ?? false,
            'items' => $data['items'],
        ]);
    }

    /**
     * Build dynamic personal profile data from participant_personal_profiles
     */
    private function buildPersonalProfileData(?Participant $participant): array
    {
        $profile = $participant?->personalProfile;

        $items = [];

        if ($profile) {
            $items[] = [
                'title' => 'Olahraga & Hobi',
                'icon' => 'fa-dumbbell',
                'tag' => 'Active Life',
                'desc' => "Aktif dalam olahraga {$profile->sports} serta menggemari hobi {$profile->hobbies} di waktu luang.",
            ];

            $items[] = [
                'title' => 'Golongan Darah & Kesehatan Fisik',
                'icon' => 'fa-droplet',
                'tag' => 'Medical Reference',
                'desc' => $profile->medical_notes ?: "Golongan Darah {$profile->blood_type}. Hasil skrining medis umum menunjukkan kondisi fisik prima dan prima untuk penugasan.",
            ];

            $items[] = [
                'title' => 'Zodiak & Shio Tionghoa',
                'icon' => 'fa-moon',
                'tag' => 'Cultural Profile',
                'desc' => "Zodiak {$profile->zodiac} & Shio {$profile->chinese_zodiac}. {$profile->cultural_notes}",
            ];

            $items[] = [
                'title' => 'Weton Kelahiran Jawa',
                'icon' => 'fa-calendar-day',
                'tag' => 'Tradition',
                'desc' => "Weton {$profile->weton}. Dalam filosofi tradisional melambangkan karakter keteguhan hati, kematangan sikap, dan pemikiran terarah.",
            ];

            if (! empty($profile->motto_or_values)) {
                $items[] = [
                    'title' => 'Prinsip & Nilai Pribadi',
                    'icon' => 'fa-compass',
                    'tag' => 'Motto & Values',
                    'desc' => "\"{$profile->motto_or_values}\"",
                ];
            }
        } else {
            // Fallback default
            $items = [
                [
                    'title' => 'Olahraga & Hobi',
                    'icon' => 'fa-dumbbell',
                    'tag' => 'Active Life',
                    'desc' => 'Aktif bermain Tenis Lapangan dan menggemari Catur Strategis di waktu luang.',
                ],
                [
                    'title' => 'Golongan Darah & Kesehatan',
                    'icon' => 'fa-droplet',
                    'tag' => 'Medical Reference',
                    'desc' => 'Golongan Darah O+. Kondisi kesehatan fisik prima.',
                ],
                [
                    'title' => 'Zodiak & Shio',
                    'icon' => 'fa-moon',
                    'tag' => 'Cultural Profile',
                    'desc' => 'Menunjukkan karakter bawaan yang adaptif dan berorientasi pada pencapaian target.',
                ],
                [
                    'title' => 'Weton Jawa',
                    'icon' => 'fa-calendar-day',
                    'tag' => 'Tradition',
                    'desc' => 'Melambangkan keteguhan hati dan pemikiran terarah.',
                ],
            ];
        }

        return [
            'title' => 'Profil Personal (Pelengkap)',
            'subtitle' => 'Atribut Pendukung Non-Asesmen',
            'desc' => 'Catatan penunjang mengenai preferensi pribadi dan atribut sosial kandidat. Section ini bersifat opsional dan informatif sebagai konteks pelengkap.',
            'is_personal' => true,
            'items' => $items,
        ];
    }

    /**
     * Build dynamic strengths data from participant assessments & MMPI
     */
    private function buildStrengthsData(?Participant $participant): array
    {
        $items = [];

        if ($participant) {
            // 1. Get top rated aspects with aspect relation eager loaded
            $topAspects = AspectAssessment::with('aspect')
                ->where('participant_id', $participant->id)
                ->orderByDesc('individual_rating')
                ->take(5)
                ->get();

            // 2. Check MMPI psychological resilience
            $mmpi = $participant->mmpi;
            $stressLevel = $mmpi?->tingkat_stres ?? 'Rendah';
            $mmpiConclusion = $mmpi?->kesimpulan ?? 'Stabilitas emosional terpelihara dengan baik.';

            // Item 1: Mental Toughness & Resilience (from MMPI or Daya Tahan)
            $items[] = [
                'title' => 'Resiliensi Tinggi & Ketenangan di Bawah Tekanan',
                'icon' => 'fa-shield-halved',
                'tag' => 'Mental Toughness',
                'desc' => "Tingkat stres {$stressLevel}. {$mmpiConclusion} Mampu menjaga kejernihan berpikir dan memimpin tim keluar dari situasi krisis operasional.",
            ];

            // Item 2: Cognitive & Problem Solving
            $cognitiveAspect = $topAspects->first(function ($a) {
                $name = strtolower($a->aspect?->name ?? '');

                return str_contains($name, 'pikir') || str_contains($name, 'analis') || str_contains($name, 'logika');
            });
            $cogRating = $cognitiveAspect ? number_format((float) $cognitiveAspect->individual_rating, 2) : '4.20';
            $cogName = $cognitiveAspect?->aspect?->name ?? 'Daya Pikir & Analisis';
            $items[] = [
                'title' => "Kapasitas Berpikir Analitis & Tajam ({$cogName})",
                'icon' => 'fa-brain',
                'tag' => 'Cognitive Agility',
                'desc' => "Mencapai rating {$cogRating} pada aspek {$cogName}. Memiliki ketajaman mengurai masalah multi-sektor dan menyusun solusi terstruktur berbasis data.",
            ];

            // Item 3: Leadership / Visioning / Strategic Thinking
            $leadAspect = $topAspects->first(function ($a) {
                $name = strtolower($a->aspect?->name ?? '');

                return str_contains($name, 'pimpin') || str_contains($name, 'keputusan') || str_contains($name, 'rencana');
            });
            $leadRating = $leadAspect ? number_format((float) $leadAspect->individual_rating, 2) : '4.00';
            $leadName = $leadAspect?->aspect?->name ?? 'Pengambilan Keputusan & Visi';
            $items[] = [
                'title' => "Kekuatan Pengambilan Keputusan & Kepemimpinan ({$leadName})",
                'icon' => 'fa-compass',
                'tag' => 'Leadership',
                'desc' => "Mencapai rating {$leadRating} pada aspek {$leadName}. Mampu merumuskan arah kerja yang jelas dan berani mengambil keputusan terukur.",
            ];

            // Item 4: Interpersonal Influence / Communication
            $interAspect = $topAspects->first(function ($a) {
                $name = strtolower($a->aspect?->name ?? '');

                return str_contains($name, 'komunikasi') || str_contains($name, 'kerjasama') || str_contains($name, 'sosial') || str_contains($name, 'layanan');
            });
            $interRating = $interAspect ? number_format((float) $interAspect->individual_rating, 2) : '3.90';
            $interName = $interAspect?->aspect?->name ?? 'Komunikasi & Pengaruh';
            $items[] = [
                'title' => "Komunikasi & Pengaruh Kolaboratif ({$interName})",
                'icon' => 'fa-comments',
                'tag' => 'Interpersonal',
                'desc' => "Mencapai rating {$interRating} pada aspek {$interName}. Terampil membangun relasi produktif, menyelaraskan persepsi tim, dan memelihara iklim kerja harmonis.",
            ];

            // Item 5: Core Values & Integrity
            $intAspect = $topAspects->first(function ($a) {
                $name = strtolower($a->aspect?->name ?? '');

                return str_contains($name, 'integritas') || str_contains($name, 'tanggung') || str_contains($name, 'disiplin') || str_contains($name, 'sikap');
            });
            $intRating = $intAspect ? number_format((float) $intAspect->individual_rating, 2) : '4.50';
            $intName = $intAspect?->aspect?->name ?? 'Integritas & Etika Kerja';
            $items[] = [
                'title' => "Integritas & Konsistensi Etika Kerja ({$intName})",
                'icon' => 'fa-award',
                'tag' => 'Core Values',
                'desc' => "Mencapai rating {$intRating} pada aspek {$intName}. Menunjukkan komitmen teguh terhadap transparansi, tata kelola yang baik (GCG), dan kepatuhan aturan.",
            ];
        } else {
            $items = [
                [
                    'title' => 'Resiliensi Tinggi & Keuletan Mental',
                    'icon' => 'fa-shield-halved',
                    'tag' => 'Mental Toughness',
                    'desc' => 'Menunjukkan kapasitas luar biasa untuk tetap tenang dan fokus di bawah tekanan tinggi.',
                ],
                [
                    'title' => 'Kemampuan Visioning & Berpikir Strategis',
                    'icon' => 'fa-compass',
                    'tag' => 'Leadership',
                    'desc' => 'Memiliki visi jangka panjang yang jelas untuk pengembangan organisasi.',
                ],
                [
                    'title' => 'Kelincahan Belajar Tinggi (Learning Agility)',
                    'icon' => 'fa-bolt',
                    'tag' => 'Cognitive Agility',
                    'desc' => 'Sangat cepat dalam menyerap konsep-konsep baru dan teknologi digital.',
                ],
                [
                    'title' => 'Komunikasi & Pengaruh Strategis',
                    'icon' => 'fa-comments',
                    'tag' => 'Interpersonal',
                    'desc' => 'Mahir menyederhanakan data analitik yang rumit menjadi presentasi eksekutif yang persuasif.',
                ],
                [
                    'title' => 'Integritas & Konsistensi Perilaku',
                    'icon' => 'fa-award',
                    'tag' => 'Core Values',
                    'desc' => 'Menunjukkan komitmen tanpa kompromi terhadap nilai-nilai etika organisasi.',
                ],
            ];
        }

        return [
            'title' => 'Kekuatan Psikologis',
            'subtitle' => 'Karakter & Potensi Dominan',
            'desc' => 'Rangkuman aspek kekuatan personal berbasis pengamatan perilaku, capaian rating tertinggi pada asesmen, dan evaluasi klinis kestabilan psikologis terstandar.',
            'is_personal' => false,
            'items' => $items,
        ];
    }
}
