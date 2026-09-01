<?php

declare(strict_types=1);

namespace App\Livewire\Pages;

use App\Livewire\Concerns\SyncsSessionFromUrlParams;
use App\Models\Participant;
use App\Models\ParticipantCareerHistory;
use App\Models\ParticipantPerformanceRecord;
use App\Models\ParticipantPersonalProfile;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app', ['title' => 'Detail Peserta'])]
class ParticipantDetail extends Component
{
    use SyncsSessionFromUrlParams;

    public ?Participant $participant = null;

    public string $mainTab = 'reports'; // 'reports', 'supplementary_data'

    public string $supplementarySubTab = 'performance'; // 'performance', 'career', 'personal'

    public ?string $successMessage = null;

    public bool $isSaving = false;

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
     * Succession Curation state (Override)
     */
    public ?string $successionTargetRole = null;

    public ?string $readinessHorizon = null;

    public ?int $readinessPercentage = null;

    public ?string $successionNotes = null;

    public function mount(string $eventCode, string $testNumber): void
    {
        // Load participant dengan semua relasi yang diperlukan
        $this->participant = Participant::with([
            'assessmentEvent.institution',
            'batch',
            'positionFormation.template',
            'performanceRecords' => fn ($q) => $q->orderBy('year', 'asc'),
            'careerHistories' => fn ($q) => $q->orderBy('start_year', 'asc'),
            'personalProfile',
        ])
            ->whereHas('assessmentEvent', function ($query) use ($eventCode) {
                $query->where('code', $eventCode);
            })
            ->where('test_number', $testNumber)
            ->firstOrFail();

        // Sync session from URL parameters
        $this->syncSessionFromParticipant($this->participant);

        $this->loadSupplementaryData();
    }

    /**
     * Switch main tab
     */
    public function setMainTab(string $tab): void
    {
        $this->mainTab = $tab;
        $this->successMessage = null;
    }

    /**
     * Switch supplementary sub-tab
     */
    public function setSupplementarySubTab(string $tab): void
    {
        $this->supplementarySubTab = $tab;
        $this->successMessage = null;
    }

    /**
     * Load supplementary data into form arrays
     */
    public function loadSupplementaryData(): void
    {
        if (! $this->participant) {
            return;
        }

        // 1. Performance records
        $this->performanceRecords = [];
        if ($this->participant->performanceRecords->isNotEmpty()) {
            foreach ($this->participant->performanceRecords as $rec) {
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
        if ($this->participant->careerHistories->isNotEmpty()) {
            foreach ($this->participant->careerHistories as $car) {
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
            $currentPos = $this->participant->current_position ?? 'Pejabat Fungsional / Struktural';
            $instName = $this->participant->assessmentEvent?->institution?->name ?? 'Instansi Pemerintahan / Korporasi';
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

        // 3. Personal profile & Succession Curation
        $profile = $this->participant->personalProfile;
        if ($profile) {
            $this->bloodType = $profile->blood_type ?? 'O+';
            $this->hobbies = $profile->hobbies ?? '';
            $this->sports = $profile->sports ?? '';
            $this->medicalNotes = $profile->medical_notes ?? '';
            $this->culturalNotes = $profile->cultural_notes ?? '';
            $this->mottoOrValues = $profile->motto_or_values ?? '';
            $this->successionTargetRole = $profile->succession_target_role ?? null;
            $this->readinessHorizon = $profile->readiness_horizon ?? null;
            $this->readinessPercentage = $profile->readiness_percentage !== null ? (int) $profile->readiness_percentage : null;
            $this->successionNotes = $profile->succession_notes ?? null;
        } else {
            $this->bloodType = 'O+';
            $this->hobbies = 'Membaca, Fotografi, Riset Kebijakan';
            $this->sports = 'Jogging, Bulu Tangkis';
            $this->medicalNotes = 'Kondisi fisik prima, tidak ada riwayat penyakit kronis berat.';
            $this->culturalNotes = 'Menjunjung tinggi nilai kejujuran, integritas, dan budaya gotong royong.';
            $this->mottoOrValues = 'Integritas dalam Bekerja, Keunggulan dalam Melayani.';
            $this->successionTargetRole = null;
            $this->readinessHorizon = null;
            $this->readinessPercentage = null;
            $this->successionNotes = null;
        }
    }

    /**
     * Add performance row
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
     * Remove performance row
     */
    public function removePerformanceRow(int $index): void
    {
        if (isset($this->performanceRecords[$index])) {
            unset($this->performanceRecords[$index]);
            $this->performanceRecords = array_values($this->performanceRecords);
        }
    }

    /**
     * Add career row
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
     * Remove career row
     */
    public function removeCareerRow(int $index): void
    {
        if (isset($this->careerHistories[$index])) {
            unset($this->careerHistories[$index]);
            $this->careerHistories = array_values($this->careerHistories);
        }
    }

    /**
     * Toggle current career
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
     * Save supplementary data
     */
    public function saveSupplementaryData(): void
    {
        if (! $this->participant) {
            return;
        }

        $this->isSaving = true;

        DB::transaction(function () {
            $participantId = $this->participant->id;

            // 1. Save Performance Records
            ParticipantPerformanceRecord::where('participant_id', $participantId)->delete();
            foreach ($this->performanceRecords as $rec) {
                if (empty($rec['year']) || empty($rec['kpi_score'])) {
                    continue;
                }

                $achievements = array_values(array_filter(
                    array_map('trim', explode("\n", (string) ($rec['achievements_text'] ?? '')))
                ));

                ParticipantPerformanceRecord::create([
                    'participant_id' => $participantId,
                    'year' => (int) $rec['year'],
                    'kpi_score' => (float) $rec['kpi_score'],
                    'target_score' => ! empty($rec['target_score']) ? (float) $rec['target_score'] : 100.00,
                    'benchmark_score' => ! empty($rec['benchmark_score']) ? (float) $rec['benchmark_score'] : 90.00,
                    'performance_rating' => $rec['performance_rating'] ?: 'Sangat Baik',
                    'achievements' => $achievements,
                ]);
            }

            // 2. Save Career Histories
            ParticipantCareerHistory::where('participant_id', $participantId)->delete();
            $orderIndex = 0;
            foreach ($this->careerHistories as $car) {
                if (empty($car['position_title']) || empty($car['company_or_institution'])) {
                    continue;
                }

                $achievements = array_values(array_filter(
                    array_map('trim', explode("\n", (string) ($car['achievements_text'] ?? '')))
                ));

                ParticipantCareerHistory::create([
                    'participant_id' => $participantId,
                    'position_title' => trim($car['position_title']),
                    'company_or_institution' => trim($car['company_or_institution']),
                    'start_year' => (int) $car['start_year'],
                    'end_year' => ! empty($car['is_current']) ? null : (! empty($car['end_year']) ? (int) $car['end_year'] : null),
                    'is_current' => (bool) ($car['is_current'] ?? false),
                    'achievements' => $achievements,
                    'order_index' => $orderIndex++,
                ]);
            }

            // 3. Save Personal Profile & Succession Curation
            ParticipantPersonalProfile::updateOrCreate(
                ['participant_id' => $participantId],
                [
                    'blood_type' => $this->bloodType ?: 'O+',
                    'hobbies' => trim($this->hobbies),
                    'sports' => trim($this->sports),
                    'medical_notes' => trim($this->medicalNotes),
                    'cultural_notes' => trim($this->culturalNotes),
                    'motto_or_values' => trim($this->mottoOrValues),
                    'succession_target_role' => ! empty(trim((string) $this->successionTargetRole)) ? trim((string) $this->successionTargetRole) : null,
                    'readiness_horizon' => ! empty($this->readinessHorizon) ? $this->readinessHorizon : null,
                    'readiness_percentage' => $this->readinessPercentage !== null && $this->readinessPercentage !== '' ? (int) $this->readinessPercentage : null,
                    'succession_notes' => ! empty(trim((string) $this->successionNotes)) ? trim((string) $this->successionNotes) : null,
                ]
            );
        });

        $this->isSaving = false;
        $this->successMessage = 'Data pelengkap HCA berhasil diperbarui dan disimpan ke database.';

        // Reload fresh relations
        $this->participant->load(['performanceRecords', 'careerHistories', 'personalProfile']);
    }

    public function render(): View
    {
        return view('livewire.pages.participant-detail');
    }
}
