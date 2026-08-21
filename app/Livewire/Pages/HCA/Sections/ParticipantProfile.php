<?php

declare(strict_types=1);

namespace App\Livewire\Pages\HCA\Sections;

use App\Models\Participant;
use App\Services\HcaDataService;
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
        return app(HcaDataService::class)->getParticipant($this->participantId);
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

    public function getFormattedNameProperty(): string
    {
        $p = $this->participant;
        if (! $p) {
            return '-';
        }

        $name = trim($p->name);
        $gelarDepan = trim((string) $p->gelar_depan);
        $gelarBelakang = trim((string) $p->gelar_belakang);

        $result = $name;
        if (! empty($gelarDepan) && ! str_starts_with($name, $gelarDepan)) {
            $result = $gelarDepan.' '.$result;
        }
        if (! empty($gelarBelakang) && ! str_ends_with($name, $gelarBelakang)) {
            $result = $result.', '.$gelarBelakang;
        }

        return $result;
    }

    public function getPersonalBiodataProperty(): array
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
        if ($p->tempat_lahir || $p->tanggal_lahir) {
            $dateStr = $p->tanggal_lahir ? Carbon::parse($p->tanggal_lahir)->translatedFormat('d F Y') : '';
            $age = $p->tanggal_lahir ? Carbon::parse($p->tanggal_lahir)->age.' Thn' : '';
            $placeStr = $p->tempat_lahir ? $p->tempat_lahir : '';

            $combined = trim($placeStr.($placeStr && $dateStr ? ', ' : '').$dateStr);
            $birthInfo = $combined.($age ? " ({$age})" : '');
        }

        return [
            ['label' => 'Nama Lengkap', 'value' => $this->formatted_name, 'icon' => 'fa-user'],
            ['label' => 'NIK (No. KTP)', 'value' => $p->nik ?: '-', 'icon' => 'fa-id-card'],
            ['label' => 'Nomor Tes', 'value' => $p->test_number ?: '-', 'icon' => 'fa-hashtag'],
            ['label' => 'Nomor SKB', 'value' => $p->skb_number ?: '-', 'icon' => 'fa-file-signature'],
            ['label' => 'Nomor KJG', 'value' => $p->no_kjg ?: '-', 'icon' => 'fa-barcode'],
            ['label' => 'Tempat & Tanggal Lahir', 'value' => $birthInfo ?: '-', 'icon' => 'fa-cake-candles'],
            ['label' => 'Jenis Kelamin', 'value' => $genderLabel, 'icon' => 'fa-venus-mars'],
            ['label' => 'Agama', 'value' => $p->agama ?: '-', 'icon' => 'fa-hands-praying'],
            ['label' => 'Status Pernikahan', 'value' => $p->status_perkawinan ?: '-', 'icon' => 'fa-ring'],
            ['label' => 'Pendidikan Terakhir', 'value' => $p->pendidikan ?: '-', 'icon' => 'fa-graduation-cap'],
            ['label' => 'Alamat Email', 'value' => $p->email ?: '-', 'icon' => 'fa-envelope'],
            ['label' => 'No. Handphone / WhatsApp', 'value' => $p->phone ?: '-', 'icon' => 'fa-phone'],
        ];
    }

    public function getEmploymentBiodataProperty(): array
    {
        $p = $this->participant;
        if (! $p) {
            return [];
        }

        $instansi = $p->institution?->name
            ?? $p->assessmentEvent?->institution?->name
            ?? 'Instansi Klien';

        $pangkatGolongan = '-';
        if ($p->pangkat || $p->golongan) {
            $pangkatGolongan = trim(($p->pangkat ? $p->pangkat : '').($p->pangkat && $p->golongan ? ' / ' : '').($p->golongan ? "Gol. {$p->golongan}" : ''));
        }

        return [
            ['label' => 'Status Kepegawaian', 'value' => $p->status_kepegawaian ?: '-', 'icon' => 'fa-user-tie'],
            ['label' => 'Pangkat / Golongan', 'value' => $pangkatGolongan, 'icon' => 'fa-award'],
            ['label' => 'Unit Kerja / Dinas', 'value' => $p->unit_kerja ?: '-', 'icon' => 'fa-sitemap'],
            ['label' => 'Instansi / Lembaga', 'value' => $instansi, 'icon' => 'fa-building'],
            ['label' => 'Jabatan Pelaksana', 'value' => $p->jabatan_pelaksana ?: '-', 'icon' => 'fa-user-gear'],
            ['label' => 'Jabatan Fungsional', 'value' => $p->jbt_fungsional ?: '-', 'icon' => 'fa-briefcase'],
            ['label' => 'Jabatan Struktural', 'value' => $p->jbt_struktural ?: '-', 'icon' => 'fa-network-wired'],
            ['label' => 'Pengalaman Kerja', 'value' => $p->pengalaman_kerja ?: '-', 'icon' => 'fa-clock-rotate-left'],
        ];
    }

    public function getAssessmentBiodataProperty(): array
    {
        $p = $this->participant;
        if (! $p) {
            return [];
        }

        $assessmentDate = $p->assessment_date
            ? $p->assessment_date->translatedFormat('d F Y')
            : ($p->assessmentEvent?->start_date ? Carbon::parse($p->assessmentEvent->start_date)->translatedFormat('d F Y') : '-');

        return [
            ['label' => 'Formasi Jabatan Target', 'value' => $p->positionFormation?->name ?: '-', 'icon' => 'fa-bullseye'],
            ['label' => 'Level Jabatan Target', 'value' => $p->positionFormation?->level_jabatan ?: '-', 'icon' => 'fa-stairs'],
            ['label' => 'Minat Penempatan', 'value' => $p->minat_penempatan ?: '-', 'icon' => 'fa-map-pin'],
            ['label' => 'Event Asesmen', 'value' => $p->assessmentEvent?->name ?: '-', 'icon' => 'fa-calendar-check'],
            ['label' => 'Gelombang / Batch', 'value' => $p->batch?->name ?: '-', 'icon' => 'fa-layer-group'],
            ['label' => 'Tanggal Pelaksanaan', 'value' => $assessmentDate, 'icon' => 'fa-calendar-day'],
        ];
    }

    public function getBiodataProperty(): array
    {
        return array_merge(
            $this->personal_biodata,
            $this->employment_biodata,
            $this->assessment_biodata
        );
    }

    public function render(): View
    {
        return view('livewire.pages.h-c-a.sections.participant-profile', [
            'participant' => $this->participant,
            'personalBiodata' => $this->personal_biodata,
            'employmentBiodata' => $this->employment_biodata,
            'assessmentBiodata' => $this->assessment_biodata,
            'biodata' => $this->biodata,
            'formattedName' => $this->formatted_name,
            'initials' => $this->getInitials($this->participant?->name),
        ]);
    }
}
