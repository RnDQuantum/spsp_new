<?php

declare(strict_types=1);

namespace App\Livewire\Pages\HCA\Components;

use App\Models\Participant;
use App\Models\ParticipantCareerHistory;
use App\Models\ParticipantPerformanceRecord;
use App\Models\ParticipantPersonalProfile;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Livewire\Attributes\On;
use Livewire\Component;

class HcaDataEditorModal extends Component
{
    public bool $isOpen = false;

    public ?int $participantId = null;

    public string $activeTab = 'performance'; // 'performance', 'career', 'personal'

    public bool $isSaving = false;

    public ?string $successMessage = null;

    /**
     * Performance Records state
     *
     * @var array<int, array{
     *     id: int|null,
     *     year: int|string,
     *     kpi_score: float|string,
     *     target_score: float|string,
     *     benchmark_score: float|string,
     *     performance_rating: string,
     *     achievements_text: string
     * }>
     */
    public array $performanceRecords = [];

    /**
     * Career Histories state
     *
     * @var array<int, array{
     *     id: int|null,
     *     position_title: string,
     *     company_or_institution: string,
     *     start_year: int|string,
     *     end_year: int|string|null,
     *     is_current: bool,
     *     achievements_text: string
     * }>
     */
    public array $careerHistories = [];

    /**
     * Personal Profile state
     */
    public string $bloodType = 'O+';

    public string $hobbies = '';

    public string $sports = '';

    public string $medicalNotes = '';

    public string $culturalNotes = '';

    public string $mottoOrValues = '';

    /**
     * Listen for open modal event
     */
    #[On('open-hca-editor')]
    public function openEditor(?int $participantId = null): void
    {
        if ($participantId) {
            $this->participantId = $participantId;
        }

        if (! $this->participantId) {
            $this->participantId = session('filter.participant_id') ?? Participant::query()->first()?->id;
        }

        $this->loadData();
        $this->isOpen = true;
        $this->successMessage = null;
    }

    /**
     * Close modal
     */
    public function closeEditor(): void
    {
        $this->isOpen = false;
        $this->successMessage = null;
    }

    /**
     * Set active tab in drawer
     */
    public function setActiveTab(string $tab): void
    {
        $this->activeTab = $tab;
        $this->successMessage = null;
    }

    /**
     * Load current participant data from database
     */
    public function loadData(): void
    {
        if (! $this->participantId) {
            return;
        }

        $participant = Participant::with([
            'performanceRecords' => fn ($q) => $q->orderBy('year', 'asc'),
            'careerHistories' => fn ($q) => $q->orderBy('start_year', 'asc'),
            'personalProfile',
        ])->find($this->participantId);

        if (! $participant) {
            return;
        }

        // 1. Performance records
        $this->performanceRecords = [];
        if ($participant->performanceRecords->isNotEmpty()) {
            foreach ($participant->performanceRecords as $rec) {
                $achievements = $rec->achievements ?? [];
                $achievementsText = is_array($achievements) ? implode("\n", $achievements) : (string) $achievements;

                $this->performanceRecords[] = [
                    'id' => $rec->id,
                    'year' => (int) $rec->year,
                    'kpi_score' => (float) $rec->kpi_score,
                    'target_score' => (float) $rec->target_score,
                    'benchmark_score' => (float) $rec->benchmark_score,
                    'performance_rating' => $rec->performance_rating ?? 'Sangat Baik',
                    'achievements_text' => $achievementsText,
                ];
            }
        } else {
            // Default 3 sample years if totally empty
            $currentYear = (int) date('Y');
            $this->performanceRecords = [
                [
                    'id' => null,
                    'year' => $currentYear - 2,
                    'kpi_score' => 94.50,
                    'target_score' => 100.00,
                    'benchmark_score' => 90.00,
                    'performance_rating' => 'Baik',
                    'achievements_text' => 'Pencapaian target operasional unit kerja',
                ],
                [
                    'id' => null,
                    'year' => $currentYear - 1,
                    'kpi_score' => 96.80,
                    'target_score' => 100.00,
                    'benchmark_score' => 90.00,
                    'performance_rating' => 'Sangat Baik',
                    'achievements_text' => 'Efisiensi anggaran dan automasi proses bisnis',
                ],
                [
                    'id' => null,
                    'year' => $currentYear,
                    'kpi_score' => 98.20,
                    'target_score' => 100.00,
                    'benchmark_score' => 90.00,
                    'performance_rating' => 'Sangat Baik',
                    'achievements_text' => 'Melampaui target KPI strategis organisasi',
                ],
            ];
        }

        // 2. Career histories
        $this->careerHistories = [];
        if ($participant->careerHistories->isNotEmpty()) {
            foreach ($participant->careerHistories as $car) {
                $achievements = $car->achievements ?? [];
                $achievementsText = is_array($achievements) ? implode("\n", $achievements) : (string) $achievements;

                $this->careerHistories[] = [
                    'id' => $car->id,
                    'position_title' => $car->position_title,
                    'company_or_institution' => $car->company_or_institution,
                    'start_year' => (int) $car->start_year,
                    'end_year' => $car->end_year ? (int) $car->end_year : null,
                    'is_current' => (bool) $car->is_current,
                    'achievements_text' => $achievementsText,
                ];
            }
        } else {
            $currentPos = $participant->current_position ?? 'Pejabat Fungsional / Struktural';
            $instName = $participant->assessmentEvent?->institution?->name ?? 'Instansi Pemerintahan / Korporasi';
            $currentYear = (int) date('Y');

            $this->careerHistories = [
                [
                    'id' => null,
                    'position_title' => 'Analis / Staf Senior',
                    'company_or_institution' => $instName,
                    'start_year' => $currentYear - 5,
                    'end_year' => $currentYear - 2,
                    'is_current' => false,
                    'achievements_text' => "Mengawal pelaksanaan program kerja operasional divisi\nPenyusunan kajian teknis dan telaah staf",
                ],
                [
                    'id' => null,
                    'position_title' => $currentPos,
                    'company_or_institution' => $instName,
                    'start_year' => $currentYear - 2,
                    'end_year' => null,
                    'is_current' => true,
                    'achievements_text' => "Memimpin inisiatif strategis unit kerja dan koordinasi lintas fungsi\nPencapaian target kinerja utama dengan predikat memuaskan",
                ],
            ];
        }

        // 3. Personal profile
        $profile = $participant->personalProfile;
        if ($profile) {
            $this->bloodType = $profile->blood_type ?? 'O+';
            $this->hobbies = $profile->hobbies ?? '';
            $this->sports = $profile->sports ?? '';
            $this->medicalNotes = $profile->medical_notes ?? '';
            $this->culturalNotes = $profile->cultural_notes ?? '';
            $this->mottoOrValues = $profile->motto_or_values ?? '';
        } else {
            $this->bloodType = 'O+';
            $this->hobbies = 'Membaca, Fotografi, Riset Kebijakan';
            $this->sports = 'Jogging, Bulu Tangkis';
            $this->medicalNotes = 'Kondisi fisik prima, tidak ada riwayat penyakit kronis berat.';
            $this->culturalNotes = 'Menjunjung tinggi nilai kejujuran, integritas, dan budaya gotong royong.';
            $this->mottoOrValues = 'Integritas dalam Bekerja, Keunggulan dalam Melayani.';
        }
    }

    /**
     * Add a new performance record row
     */
    public function addPerformanceRow(): void
    {
        $lastYear = count($this->performanceRecords) > 0
            ? (int) end($this->performanceRecords)['year'] + 1
            : (int) date('Y');

        $this->performanceRecords[] = [
            'id' => null,
            'year' => $lastYear,
            'kpi_score' => 95.00,
            'target_score' => 100.00,
            'benchmark_score' => 90.00,
            'performance_rating' => 'Sangat Baik',
            'achievements_text' => '',
        ];
    }

    /**
     * Remove a performance record row
     */
    public function removePerformanceRow(int $index): void
    {
        if (isset($this->performanceRecords[$index])) {
            unset($this->performanceRecords[$index]);
            $this->performanceRecords = array_values($this->performanceRecords);
        }
    }

    /**
     * Add a new career history row
     */
    public function addCareerRow(): void
    {
        $this->careerHistories[] = [
            'id' => null,
            'position_title' => '',
            'company_or_institution' => '',
            'start_year' => (int) date('Y') - 1,
            'end_year' => null,
            'is_current' => false,
            'achievements_text' => '',
        ];
    }

    /**
     * Remove a career history row
     */
    public function removeCareerRow(int $index): void
    {
        if (isset($this->careerHistories[$index])) {
            unset($this->careerHistories[$index]);
            $this->careerHistories = array_values($this->careerHistories);
        }
    }

    /**
     * Toggle is_current for career history row
     */
    public function toggleCurrentCareer(int $index): void
    {
        if (isset($this->careerHistories[$index])) {
            $isCurrent = ! $this->careerHistories[$index]['is_current'];
            $this->careerHistories[$index]['is_current'] = $isCurrent;
            if ($isCurrent) {
                $this->careerHistories[$index]['end_year'] = null;
            }
        }
    }

    /**
     * Save all supplementary data atomically
     */
    public function save(): void
    {
        if (! $this->participantId) {
            return;
        }

        $this->isSaving = true;

        $participant = Participant::find($this->participantId);
        if (! $participant) {
            $this->isSaving = false;

            return;
        }

        DB::transaction(function () use ($participant) {
            // 1. Save Performance Records
            ParticipantPerformanceRecord::where('participant_id', $participant->id)->delete();
            foreach ($this->performanceRecords as $rec) {
                if (empty($rec['year']) || empty($rec['kpi_score'])) {
                    continue;
                }

                $achievements = array_values(array_filter(
                    array_map('trim', explode("\n", (string) ($rec['achievements_text'] ?? '')))
                ));

                ParticipantPerformanceRecord::create([
                    'participant_id' => $participant->id,
                    'year' => (int) $rec['year'],
                    'kpi_score' => (float) $rec['kpi_score'],
                    'target_score' => ! empty($rec['target_score']) ? (float) $rec['target_score'] : 100.00,
                    'benchmark_score' => ! empty($rec['benchmark_score']) ? (float) $rec['benchmark_score'] : 90.00,
                    'performance_rating' => $rec['performance_rating'] ?: 'Sangat Baik',
                    'achievements' => $achievements,
                ]);
            }

            // 2. Save Career Histories
            ParticipantCareerHistory::where('participant_id', $participant->id)->delete();
            $orderIndex = 0;
            foreach ($this->careerHistories as $car) {
                if (empty($car['position_title']) || empty($car['company_or_institution'])) {
                    continue;
                }

                $achievements = array_values(array_filter(
                    array_map('trim', explode("\n", (string) ($car['achievements_text'] ?? '')))
                ));

                ParticipantCareerHistory::create([
                    'participant_id' => $participant->id,
                    'position_title' => trim($car['position_title']),
                    'company_or_institution' => trim($car['company_or_institution']),
                    'start_year' => (int) $car['start_year'],
                    'end_year' => ! empty($car['is_current']) ? null : (! empty($car['end_year']) ? (int) $car['end_year'] : null),
                    'is_current' => (bool) ($car['is_current'] ?? false),
                    'achievements' => $achievements,
                    'order_index' => $orderIndex++,
                ]);
            }

            // 3. Save Personal Profile
            ParticipantPersonalProfile::updateOrCreate(
                ['participant_id' => $participant->id],
                [
                    'blood_type' => $this->bloodType ?: 'O+',
                    'hobbies' => trim($this->hobbies),
                    'sports' => trim($this->sports),
                    'medical_notes' => trim($this->medicalNotes),
                    'cultural_notes' => trim($this->culturalNotes),
                    'motto_or_values' => trim($this->mottoOrValues),
                ]
            );
        });

        $this->isSaving = false;
        $this->successMessage = 'Data pelengkap berhasil disimpan dan disinkronkan ke seluruh section laporan.';

        // Dispatch livewire events to parent and child components
        $this->dispatch('hca-data-updated', participantId: $this->participantId);
    }

    /**
     * Get participant property
     */
    public function getParticipantProperty(): ?Participant
    {
        return $this->participantId ? Participant::with(['positionFormation', 'assessmentEvent.institution'])->find($this->participantId) : null;
    }

    public function render(): View
    {
        return view('livewire.pages.h-c-a.components.hca-data-editor-modal');
    }
}
