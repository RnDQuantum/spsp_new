<?php

namespace App\Livewire\Pages\LaporanAlatTes;

use App\Models\Participant;
use App\Services\TestReportService;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app', ['title' => 'Detail Laporan Alat Tes'])]
class DetailLaporanTes extends Component
{
    public ?int $participantId = null;

    public ?Participant $participant = null;

    public array $testReports = [];

    public function mount(?int $participantId, TestReportService $testReportService): void
    {
        $id = $participantId ?? session('filter.participant_id');

        if ($id) {
            $this->participant = Participant::with([
                'assessmentEvent',
                'assessmentEvent.project',
                'assessmentEvent.institution',
                'positionFormation',
                'batch',
            ])->find($id);
        }

        if (! $this->participant) {
            $this->participant = Participant::with([
                'assessmentEvent',
                'assessmentEvent.project',
                'assessmentEvent.institution',
                'positionFormation',
                'batch',
            ])->first();
        }

        if ($this->participant) {
            $this->participantId = $this->participant->id;
            session()->put('filter.participant_id', $this->participant->id);

            $this->testReports = $testReportService->getParticipantAllTestReports(
                $this->participant->id,
                $this->participant->event_id
            );
        }
    }

    public function render()
    {
        return view('livewire.pages.laporan-alat-tes.detail-laporan-tes', [
            'participant' => $this->participant,
            'testReports' => $this->testReports,
        ]);
    }
}
