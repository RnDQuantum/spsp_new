<?php

declare(strict_types=1);

namespace App\Livewire\Pages\HCA\Sections;

use App\Models\Participant;
use Carbon\Carbon;
use Illuminate\View\View;
use Livewire\Attributes\Reactive;
use Livewire\Component;

class ParticipantProfile extends Component
{
    #[Reactive]
    public ?int $participantId = null;

    public function getParticipantProperty(): ?Participant
    {
        if (! $this->participantId) {
            return Participant::with([
                'assessmentEvent.institution',
                'positionFormation.template',
                'batch',
                'institution',
                'finalAssessment',
            ])->first();
        }

        return Participant::with([
            'assessmentEvent.institution',
            'positionFormation.template',
            'batch',
            'institution',
            'finalAssessment',
        ])->find($this->participantId);
    }

    public function getInitials(?string $name): string
    {
        if (! $name) {
            return 'P';
        }

        $cleanName = explode(',', $name)[0];
        $words = array_filter(explode(' ', trim($cleanName)));

        if (count($words) === 0) {
            return 'P';
        }

        if (count($words) === 1) {
            return strtoupper(substr($words[0], 0, 2));
        }

        return strtoupper(substr($words[0], 0, 1).substr(end($words), 0, 1));
    }

    public function getBiodataProperty(): array
    {
        $p = $this->participant;

        if (! $p) {
            return [];
        }

        $genderLabel = match (strtoupper((string) $p->gender)) {
            'L', 'MALE', 'LAKI-LAKI' => 'Laki-Laki',
            'P', 'FEMALE', 'PEREMPUAN' => 'Perempuan',
            default => $p->gender ?: '-',
        };

        $birthInfo = '-';
        if ($p->birth_place || $p->birth_date) {
            $dateStr = $p->birth_date ? Carbon::parse($p->birth_date)->translatedFormat('d F Y') : '';
            $birthInfo = trim(($p->birth_place ? $p->birth_place.', ' : '').$dateStr);
        }

        $educationStr = $p->last_education ?: '-';
        if (! empty($p->major)) {
            $educationStr .= ' ('.$p->major.')';
        }

        $instansi = $p->institution?->name
            ?? $p->assessmentEvent?->institution?->name
            ?? 'SPSP Institution';

        return [
            ['label' => 'Nama Lengkap', 'value' => $p->name],
            ['label' => 'Nomor Tes', 'value' => $p->test_number ?: '-'],
            ['label' => 'Nomor SKB / NIP', 'value' => $p->skb_number ?: ($p->nip_nik ?: '-')],
            ['label' => 'Formasi Jabatan Target', 'value' => $p->positionFormation?->name ?: '-'],
            ['label' => 'Instansi / Unit Kerja', 'value' => $instansi],
            ['label' => 'Gelombang / Batch', 'value' => $p->batch?->name ?: '-'],
            ['label' => 'Tanggal Asesmen', 'value' => $p->assessment_date ? $p->assessment_date->translatedFormat('d F Y') : '-'],
            ['label' => 'Jenis Kelamin', 'value' => $genderLabel],
            ['label' => 'Pendidikan Terakhir', 'value' => $educationStr],
            ['label' => 'Tempat, Tanggal Lahir', 'value' => $birthInfo],
        ];
    }

    public function render(): View
    {
        return view('livewire.pages.h-c-a.sections.participant-profile', [
            'participant' => $this->participant,
            'biodata' => $this->biodata,
            'initials' => $this->getInitials($this->participant?->name),
        ]);
    }
}
