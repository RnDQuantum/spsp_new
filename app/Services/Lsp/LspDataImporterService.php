<?php

namespace App\Services\Lsp;

use App\Models\Aspect;
use App\Models\AspectAssessment;
use App\Models\AssessmentEvent;
use App\Models\AssessmentTemplate;
use App\Models\Batch;
use App\Models\CategoryAssessment;
use App\Models\CategoryType;
use App\Models\FinalAssessment;
use App\Models\Institution;
use App\Models\Interpretation;
use App\Models\Mmpi;
use App\Models\Participant;
use App\Models\PositionFormation;
use App\Models\Project;
use App\Models\SubAspect;
use App\Models\SubAspectAssessment;
use App\Models\TestResult;
use App\Services\Api\ApiDataTransformerService;
use Closure;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class LspDataImporterService
{
    /**
     * Registry master data in-memory per import session
     */
    protected array $templateRegistry = [];

    protected array $batchRegistry = [];

    protected array $formationRegistry = [];

    protected array $aspectRegistry = [];

    protected array $subAspectRegistry = [];

    public function __construct(
        protected LspDataTransformerService $reportService,
        protected ?ApiDataTransformerService $apiTransformer = null
    ) {
        $this->apiTransformer = $apiTransformer ?? app(ApiDataTransformerService::class);
    }

    /**
     * Memeriksa apakah proyek termasuk Jalur A (Legacy DB < PR-A-338) atau Jalur B (API Online >= PR-A-338).
     */
    public function isLegacyProject(string $kodeProyek): bool
    {
        if (preg_match('/PR-[A-Z]-(\d+)/i', $kodeProyek, $matches)) {
            return (int) $matches[1] < 338;
        }

        return true;
    }

    /**
     * Import seluruh peserta dari proyek LSP tertentu ke database SPSP dengan metode chunking & bulk upsert.
     */
    public function importProject(string $kodeProyek, ?string $singleUsername = null, ?int $institutionId = null, ?Closure $progressCallback = null): array
    {
        $dbLsp = DB::connection('lsp');

        // 1. Fetch metadata dari DB LSP: Pelaksanaan (proyek), Master Proyek (proyek_produksi), & Klien (klien)
        $proyekLsp = $dbLsp->table('proyek')->where('kode_proyek', $kodeProyek)->first();
        $proyekProduksiLsp = null;
        if ($proyekLsp && ! empty($proyekLsp->nama_proyek)) {
            $proyekProduksiLsp = $dbLsp->table('proyek_produksi')
                ->where('kode', $proyekLsp->nama_proyek)
                ->orWhere('nama', $proyekLsp->nama_proyek)
                ->first();
        }

        $klienKode = $proyekProduksiLsp->instansi ?? ($proyekLsp->instansi ?? null);
        $klienLsp = null;
        if ($klienKode) {
            $klienLsp = $dbLsp->table('klien')->where('kode_klien', $klienKode)->first();
        }

        // 2. Sinkronkan Instansi / Klien
        if (! $institutionId) {
            if ($klienLsp && ! empty($klienLsp->nama_klien)) {
                $instCode = Str::slug($klienLsp->kode_klien ?: $klienLsp->nama_klien);
                $institution = Institution::updateOrCreate(
                    ['code' => $instCode],
                    [
                        'name' => $klienLsp->nama_klien,
                        'logo_path' => $klienLsp->logo ? "logos/{$klienLsp->logo}" : 'logos/default.png',
                        'api_key' => Str::random(32),
                    ]
                );
            } else {
                $institution = Institution::firstOrCreate(
                    ['code' => 'kejaksaan'],
                    [
                        'name' => 'Kejaksaan Agung RI',
                        'logo_path' => 'logos/kejaksaan.png',
                        'api_key' => Str::random(32),
                    ]
                );
            }
            $institutionId = $institution->id;
        }

        // 3. Sinkronkan Master Proyek (proyek_produksi)
        $projectId = null;
        if ($proyekProduksiLsp && ! empty($proyekProduksiLsp->kode)) {
            $projectModel = Project::updateOrCreate(
                ['code' => $proyekProduksiLsp->kode],
                [
                    'institution_id' => $institutionId,
                    'name' => $proyekProduksiLsp->nama ?? $proyekProduksiLsp->kode,
                    'year' => (int) ($proyekProduksiLsp->tahun ?? date('Y')),
                    'contract_number' => $proyekProduksiLsp->no_kontrak ?? null,
                    'status' => $proyekProduksiLsp->status ?? 'completed',
                ]
            );
            $projectId = $projectModel->id;
        }

        // 4. Sinkronkan Pelaksanaan (AssessmentEvent)
        $namaPelaksanaan = ! empty($proyekLsp->nama_pelaksanaan)
            ? $proyekLsp->nama_pelaksanaan
            : ($proyekProduksiLsp->nama ?? "Pelaksanaan {$kodeProyek}");
        $tanggalMulai = $proyekLsp->tanggal_pelaksanaan ?? date('Y-m-d');
        $tanggalSelesai = $proyekLsp->sampai_tanggal ?? date('Y-m-d');
        $tahun = (int) date('Y', strtotime($tanggalMulai));

        $event = AssessmentEvent::updateOrCreate(
            ['code' => $kodeProyek],
            [
                'institution_id' => $institutionId,
                'project_id' => $projectId,
                'name' => $namaPelaksanaan,
                'description' => "Imported from LSP DB Execution {$kodeProyek}",
                'year' => $tahun,
                'start_date' => $tanggalMulai,
                'end_date' => $tanggalSelesai,
                'status' => 'completed',
                'last_synced_at' => now(),
            ]
        );

        // 3. Ambil daftar peserta dari DB LSP dan/atau API Online (Jalur B)
        $dbLspUsernames = [];
        try {
            $query = $dbLsp->table('peserta_produksi')
                ->where(function ($q) use ($kodeProyek) {
                    $q->where('kode_pelaksanaan', $kodeProyek)
                        ->orWhere('kode_pelaksanaan', 'LIKE', "%{$kodeProyek}%");
                });

            if ($singleUsername) {
                $query->where('username', $singleUsername);
            }

            $dbLspUsernames = $query->pluck('username')->unique()->toArray();
        } catch (\Throwable $e) {
            // Safe fallback if DB LSP query fails
        }

        $apiUsernames = [];
        if (! $this->isLegacyProject($kodeProyek) && $this->apiTransformer) {
            $apiReports = $this->apiTransformer->getProjectIndividualReports($kodeProyek, $singleUsername);
            if (! empty($apiReports)) {
                $apiUsernames = array_keys($apiReports);
            }
        }

        $allUsernames = array_values(array_unique(array_merge($apiUsernames, $dbLspUsernames)));

        if (empty($allUsernames)) {
            throw new Exception("Tidak ada data peserta ditemukan pada proyek LSP '{$kodeProyek}'".($singleUsername ? " untuk username '{$singleUsername}'" : ''));
        }
        $totalFound = count($allUsernames);

        $importedCount = 0;
        $failedCount = 0;
        $errors = [];

        // 4. Chunking peserta (100 peserta per chunk) untuk batch calculation & bulk upserts
        $chunkSize = 100;
        $chunks = array_chunk($allUsernames, $chunkSize);

        foreach ($chunks as $chunkUsernames) {
            try {
                $chunkImported = DB::transaction(function () use ($chunkUsernames, $kodeProyek, $event) {
                    return $this->importParticipantBatch($chunkUsernames, $kodeProyek, $event);
                });

                $importedCount += $chunkImported;

                if ($progressCallback) {
                    $progressCallback(count($chunkUsernames), $totalFound);
                }
            } catch (Exception $e) {
                // Fallback attempt: reset in-memory registries on rollback to prevent stale FK IDs
                $this->formationRegistry = [];
                $this->batchRegistry = [];
                $this->templateRegistry = [];
                $this->aspectRegistry = [];
                $this->subAspectRegistry = [];

                foreach ($chunkUsernames as $u) {
                    try {
                        DB::transaction(function () use ($u, $kodeProyek, $event) {
                            $this->importSingleParticipant($u, $kodeProyek, $event);
                        });
                        $importedCount++;
                        if ($progressCallback) {
                            $progressCallback(1, $totalFound);
                        }
                    } catch (Exception $ex) {
                        $failedCount++;
                        $errors[] = "Peserta {$u}: ".$ex->getMessage();
                    }
                }
            }
        }

        return [
            'event_id' => $event->id,
            'event_code' => $event->code,
            'event_name' => $event->name,
            'total_found' => $totalFound,
            'imported_count' => $importedCount,
            'failed_count' => $failedCount,
            'errors' => $errors,
        ];
    }

    /**
     * Import sekelompok (batch/chunk) peserta sekaligus dengan bulk upserts ke DB SPSP.
     */
    public function importParticipantBatch(array $usernames, string $kodeProyek, AssessmentEvent $event): int
    {
        if (empty($usernames)) {
            return 0;
        }

        $isLegacy = $this->isLegacyProject($kodeProyek);
        $sourceMarker = $isLegacy ? 'lsp_db' : 'api';

        if ($isLegacy || ! $this->apiTransformer) {
            $reportsMap = $this->reportService->getBatchIndividualReports($usernames, $kodeProyek);
        } else {
            $reportsMap = $this->apiTransformer->getProjectIndividualReports($kodeProyek);
            if (empty($reportsMap)) {
                $reportsMap = $this->reportService->getBatchIndividualReports($usernames, $kodeProyek);
            }
        }

        if (empty($reportsMap)) {
            return 0;
        }

        $now = now()->toDateTimeString();

        $noTestMap = [];
        try {
            $noTestMap = DB::connection('lsp')->table('peserta_produksi')
                ->whereIn('username', $usernames)
                ->pluck('no_test', 'username')
                ->toArray();
        } catch (\Throwable $e) {
            // Non-blocking fallback if DB LSP connection fails
        }

        $getRep = function (string $u) use (&$reportsMap, &$noTestMap): ?array {
            if (isset($reportsMap[$u])) {
                return $reportsMap[$u];
            }
            $noTest = $noTestMap[$u] ?? null;
            if ($noTest && isset($reportsMap[$noTest])) {
                return $reportsMap[$noTest];
            }
            return null;
        };

        // 1. Resolve & Batch Create Master Batches & Formations
        $batchMap = [];
        $formationMap = [];

        foreach ($usernames as $u) {
            $rep = $getRep($u);
            if (! $rep) {
                continue;
            }

            $pesertaInfo = $rep['peserta'];
            $batchName = $rep['peserta']['batch'] ?? '1';
            $location = $rep['metadata_proyek']['lokasi'] ?? ($rep['metadata']['lokasi'] ?? 'Pusat');

            $batchKey = "{$event->id}_{$batchName}";
            if (! isset($this->batchRegistry[$batchKey])) {
                $batchModel = Batch::updateOrCreate(
                    [
                        'event_id' => $event->id,
                        'code' => Str::slug("{$event->code}-{$batchName}"),
                    ],
                    [
                        'name' => "Gelombang {$batchName}",
                        'location' => $location,
                        'batch_number' => is_numeric($batchName) ? (int) $batchName : 1,
                        'start_date' => $event->start_date,
                        'end_date' => $event->end_date,
                    ]
                );
                $this->batchRegistry[$batchKey] = $batchModel;
            }
            $batchMap[$u] = $this->batchRegistry[$batchKey];

            // Master Template & PositionFormation
            $rawJabatan = trim($pesertaInfo['jabatan_pelaksana'] ?? '');
            $levelJabatan = $rawJabatan !== '' ? strtoupper($rawJabatan) : 'STAFF';
            $template = $this->ensureAssessmentTemplate($levelJabatan);

            $formationCode = Str::slug($levelJabatan);
            $minat = trim($pesertaInfo['minat_penempatan'] ?? '');
            $formationName = ($minat !== '' && $minat !== '-') ? $minat : $levelJabatan;
            $formationKey = "{$event->id}_{$formationCode}";

            if (! isset($this->formationRegistry[$formationKey])) {
                $formationModel = PositionFormation::updateOrCreate(
                    [
                        'event_id' => $event->id,
                        'code' => $formationCode,
                    ],
                    [
                        'template_id' => $template->id,
                        'name' => $formationName,
                        'quota' => 100,
                    ]
                );
                $this->formationRegistry[$formationKey] = $formationModel;
            }
            $formationMap[$u] = [
                'template' => $template,
                'formation' => $this->formationRegistry[$formationKey],
            ];
        }

        // 2. Prepare Bulk Upsert Participants
        $participantsBulk = [];
        foreach ($usernames as $u) {
            $rep = $getRep($u);
            if (! $rep) {
                continue;
            }

            $b = $batchMap[$u];
            $f = $formationMap[$u]['formation'];
            $pesertaInfo = $rep['peserta'];

            $participantsBulk[] = [
                'event_id' => $event->id,
                'batch_id' => $b->id,
                'position_formation_id' => $f->id,
                'test_number' => $pesertaInfo['no_test'],
                'skb_number' => $pesertaInfo['no_kjg'] ?? '-',
                'name' => $pesertaInfo['nama_lengkap'],
                'gender' => $pesertaInfo['jenis_kelamin'],
                'username' => $u,
                'photo_path' => $pesertaInfo['pasfoto'],
                'assessment_date' => $rep['metadata_proyek']['tanggal_pelaksanaan'] ?? ($rep['metadata']['tanggal_pelaksanaan'] ?? $event->start_date),
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        if (empty($participantsBulk)) {
            return 0;
        }

        Participant::upsert(
            $participantsBulk,
            ['username'],
            ['event_id', 'batch_id', 'position_formation_id', 'test_number', 'skb_number', 'name', 'gender', 'photo_path', 'assessment_date', 'updated_at']
        );

        // Map participant username to DB participant_id
        $participantModels = Participant::where('event_id', $event->id)
            ->whereIn('username', $usernames)
            ->get()
            ->keyBy('username');

        $pIds = $participantModels->pluck('id')->toArray();

        // 2b. Prepare Bulk Upsert TestResult (Raw Scores from LSP DB)
        $testResultsBulk = [];
        foreach ($usernames as $u) {
            $p = $participantModels[$u] ?? null;
            $rep = $getRep($u);
            if (! $p || ! $rep || empty($rep['raw_scores'])) {
                continue;
            }

            $rawScores = $rep['raw_scores'];

            // Jika terdapat data instrumen lengkap dari REST API (Jalur B)
            if (! empty($rawScores['api_full']) && is_array($rawScores['api_full'])) {
                foreach ($rawScores['api_full'] as $tCode => $tPayload) {
                    $tCodeStr = (string) $tCode;
                    if (TestResult::isExcluded($tCodeStr)) {
                        continue;
                    }
                    $tName = is_array($tPayload) ? ($tPayload['nama_alat_tes'] ?? $tPayload['nama'] ?? "Alat Tes {$tCodeStr}") : "Alat Tes {$tCodeStr}";
                    $testResultsBulk[] = [
                        'participant_id' => $p->id,
                        'event_id' => $event->id,
                        'test_code' => $tCodeStr,
                        'test_name' => $tName,
                        'test_category' => TestResult::getCategoryForCode($tCodeStr),
                        'status' => 'completed',
                        'source' => $sourceMarker,
                        'summary_data' => json_encode($tPayload),
                        'interpretation_data' => null,
                        'raw_response' => json_encode($tPayload),
                        'conversion_status' => 'pending',
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
            } else {
                // Jalur A atau Fallback: IST (A.5)
                if (! empty($rawScores['ist'])) {
                    $testResultsBulk[] = [
                        'participant_id' => $p->id,
                        'event_id' => $event->id,
                        'test_code' => 'A.5',
                        'test_name' => 'Intelligenz Struktur Test (IST)',
                        'test_category' => TestResult::getCategoryForCode('A.5'),
                        'status' => 'completed',
                        'source' => $sourceMarker,
                        'summary_data' => json_encode(['raw_score' => $rawScores['ist'], 'iq' => $rep['potensi']['total_individual_score'] ?? null]),
                        'interpretation_data' => null,
                        'raw_response' => json_encode(['raw' => $rawScores['ist']]),
                        'conversion_status' => 'pending',
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }

                // PAPI Kostik (D.1)
                if (! empty($rawScores['kostik'])) {
                    $testResultsBulk[] = [
                        'participant_id' => $p->id,
                        'event_id' => $event->id,
                        'test_code' => 'D.1',
                        'test_name' => 'PAPI Kostik',
                        'test_category' => TestResult::getCategoryForCode('D.1'),
                        'status' => 'completed',
                        'source' => $sourceMarker,
                        'summary_data' => json_encode(['raw_score' => $rawScores['kostik']]),
                        'interpretation_data' => null,
                        'raw_response' => json_encode(['raw' => $rawScores['kostik']]),
                        'conversion_status' => 'pending',
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }

                // 16PF (B.2)
                if (! empty($rawScores['personality'])) {
                    $testResultsBulk[] = [
                        'participant_id' => $p->id,
                        'event_id' => $event->id,
                        'test_code' => 'B.2',
                        'test_name' => '16 Personality Factor (16PF)',
                        'test_category' => TestResult::getCategoryForCode('B.2'),
                        'status' => 'completed',
                        'source' => $sourceMarker,
                        'summary_data' => json_encode(['raw_score' => $rawScores['personality']]),
                        'interpretation_data' => null,
                        'raw_response' => json_encode(['raw' => $rawScores['personality']]),
                        'conversion_status' => 'pending',
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
            }
        }

        if (! empty($testResultsBulk)) {
            TestResult::upsert(
                $testResultsBulk,
                ['participant_id', 'event_id', 'test_code'],
                ['test_name', 'test_category', 'status', 'source', 'summary_data', 'raw_response', 'conversion_status', 'updated_at']
            );
        }

        // 3. Prepare Bulk Insert Mmpi
        $mmpiBulk = [];
        foreach ($usernames as $u) {
            $p = $participantModels[$u] ?? null;
            $rep = $getRep($u);
            if (! $p || ! $rep) {
                continue;
            }

            $kejiwaan = $rep['mmpi'] ?? $rep['kejiwaan'] ?? null;
            if (! $kejiwaan) {
                continue;
            }

            $rawNilaiPq = $kejiwaan['nilai_pq'] ?? 0;
            $numericNilaiPq = is_numeric(trim((string) $rawNilaiPq)) ? (float) $rawNilaiPq : 0.00;

            $formatStringOrArray = function ($val) {
                if (is_array($val)) {
                    return implode(' ', $val);
                }

                return (string) ($val ?? '-');
            };

            $psikogramVal = $kejiwaan['psikogram'] ?? '-';
            $jsonPsikogram = is_array($psikogramVal) ? json_encode($psikogramVal) : json_encode([$psikogramVal]);

            $mmpiBulk[] = [
                'event_id' => $event->id,
                'participant_id' => $p->id,
                'no_test' => $rep['peserta']['no_test'] ?? $p->test_number,
                'username' => $u,
                'validitas' => $formatStringOrArray($kejiwaan['validitas'] ?? '-'),
                'internal' => $formatStringOrArray($kejiwaan['internal_pribadi'] ?? ($kejiwaan['internal'] ?? '-')),
                'interpersonal' => $formatStringOrArray($kejiwaan['interpersonal'] ?? '-'),
                'kap_kerja' => $formatStringOrArray($kejiwaan['kapasitas_kerja'] ?? ($kejiwaan['kap_kerja'] ?? '-')),
                'klinik' => $formatStringOrArray($kejiwaan['klinis'] ?? ($kejiwaan['klinik'] ?? '-')),
                'kesimpulan' => $formatStringOrArray($kejiwaan['kesimpulan'] ?? '-'),
                'psikogram' => $jsonPsikogram,
                'nilai_pq' => $numericNilaiPq,
                'tingkat_stres' => $kejiwaan['tingkat_stres'] ?? '-',
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        if (! empty($mmpiBulk)) {
            Mmpi::whereIn('participant_id', $pIds)->delete();
            Mmpi::insert($mmpiBulk);
        }

        // 4. Prepare Bulk Upsert Interpretations & CategoryAssessments
        $interpBulk = [];
        $catAssessBulk = [];

        foreach ($usernames as $u) {
            $p = $participantModels[$u] ?? null;
            $rep = $getRep($u);
            if (! $p || ! $rep) {
                continue;
            }

            $template = $formationMap[$u]['template'];
            $b = $batchMap[$u];
            $f = $formationMap[$u]['formation'];

            $potensiCatType = CategoryType::query()->where('template_id', $template->id)->where('code', 'potensi')->first();
            $kompetensiCatType = CategoryType::query()->where('template_id', $template->id)->where('code', 'kompetensi')->first();

            // Interpretations
            if ($potensiCatType && ! empty($rep['interpretasi']['potensi_text'])) {
                $interpBulk[] = [
                    'participant_id' => $p->id,
                    'event_id' => $event->id,
                    'category_type_id' => $potensiCatType->id,
                    'interpretation_text' => $rep['interpretasi']['potensi_text'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
            if ($kompetensiCatType && ! empty($rep['interpretasi']['kompetensi_text'])) {
                $interpBulk[] = [
                    'participant_id' => $p->id,
                    'event_id' => $event->id,
                    'category_type_id' => $kompetensiCatType->id,
                    'interpretation_text' => $rep['interpretasi']['kompetensi_text'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            // CategoryAssessments (Potensi)
            if ($potensiCatType) {
                $potensiData = $rep['potensi'];
                $catAssessBulk[] = [
                    'participant_id' => $p->id,
                    'event_id' => $event->id,
                    'category_type_id' => $potensiCatType->id,
                    'batch_id' => $b->id,
                    'position_formation_id' => $f->id,
                    'total_standard_rating' => $potensiData['total_standard_rating'] ?? ($potensiData['total_skor_standar'] ?? 0),
                    'total_standard_score' => $potensiData['total_standard_score'] ?? ($potensiData['total_skor_standar'] ?? 0),
                    'total_individual_rating' => $potensiData['total_individual_rating'] ?? ($potensiData['total_skor_individu'] ?? 0),
                    'total_individual_score' => $potensiData['total_individual_score'] ?? ($potensiData['total_skor_individu'] ?? 0),
                    'gap_rating' => $potensiData['gap_total_rating'] ?? 0,
                    'gap_score' => $potensiData['gap_total_score'] ?? 0,
                    'conclusion_code' => ($potensiData['kesimpulan_akhir'] ?? 'MS') === 'Memenuhi Standard' ? 'MS' : 'TMS',
                    'conclusion_text' => strtoupper($potensiData['kesimpulan_akhir'] ?? 'MS'),
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            // CategoryAssessments (Kompetensi)
            if ($kompetensiCatType) {
                $kompetensiData = $rep['kompetensi'];
                $catAssessBulk[] = [
                    'participant_id' => $p->id,
                    'event_id' => $event->id,
                    'category_type_id' => $kompetensiCatType->id,
                    'batch_id' => $b->id,
                    'position_formation_id' => $f->id,
                    'total_standard_rating' => $kompetensiData['total_standard_rating'] ?? ($kompetensiData['total_skor_standar'] ?? 0),
                    'total_standard_score' => $kompetensiData['total_standard_score'] ?? ($kompetensiData['total_skor_standar'] ?? 0),
                    'total_individual_rating' => $kompetensiData['total_individual_rating'] ?? ($kompetensiData['total_skor_individu'] ?? 0),
                    'total_individual_score' => $kompetensiData['total_individual_score'] ?? ($kompetensiData['total_skor_individu'] ?? 0),
                    'gap_rating' => $kompetensiData['gap_total_rating'] ?? 0,
                    'gap_score' => $kompetensiData['gap_total_score'] ?? 0,
                    'conclusion_code' => ($kompetensiData['kesimpulan_akhir'] ?? 'SK') === 'Sangat Kompeten' ? 'SK' : (($kompetensiData['kesimpulan_akhir'] ?? 'K') === 'Kompeten' ? 'K' : 'BK'),
                    'conclusion_text' => strtoupper($kompetensiData['kesimpulan_akhir'] ?? 'K'),
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        if (! empty($interpBulk)) {
            Interpretation::whereIn('participant_id', $pIds)->delete();
            Interpretation::insert($interpBulk);
        }

        if (! empty($catAssessBulk)) {
            CategoryAssessment::upsert(
                $catAssessBulk,
                ['participant_id', 'category_type_id'],
                ['event_id', 'batch_id', 'position_formation_id', 'total_standard_rating', 'total_standard_score', 'total_individual_rating', 'total_individual_score', 'gap_rating', 'gap_score', 'conclusion_code', 'conclusion_text', 'updated_at']
            );
        }

        // Map CategoryAssessments
        $catAssessModels = CategoryAssessment::where('event_id', $event->id)
            ->whereIn('participant_id', $pIds)
            ->get();

        $catAssessMap = [];
        foreach ($catAssessModels as $ca) {
            $catAssessMap[$ca->participant_id][$ca->category_type_id] = $ca;
        }

        // 5. Prepare Bulk AspectAssessments & SubAspectAssessments
        $aspectAssessBulk = [];
        $subAspectAssessList = [];

        foreach ($usernames as $u) {
            $p = $participantModels[$u] ?? null;
            $rep = $getRep($u);
            if (! $p || ! $rep) {
                continue;
            }

            $template = $formationMap[$u]['template'];
            $b = $batchMap[$u];
            $f = $formationMap[$u]['formation'];

            $potensiCatType = CategoryType::query()->where('template_id', $template->id)->where('code', 'potensi')->first();
            $kompetensiCatType = CategoryType::query()->where('template_id', $template->id)->where('code', 'kompetensi')->first();

            // Potensi Aspects
            if ($potensiCatType && isset($catAssessMap[$p->id][$potensiCatType->id])) {
                $catAssessPotensi = $catAssessMap[$p->id][$potensiCatType->id];
                foreach ($rep['potensi']['aspek_list'] ?? ($rep['potensi']['aspek'] ?? []) as $aspekKey => $aspekData) {
                    $aspKey = "{$template->id}_{$potensiCatType->id}_".Str::slug($aspekData['nama_aspek']);
                    if (! isset($this->aspectRegistry[$aspKey])) {
                        $this->aspectRegistry[$aspKey] = Aspect::firstOrCreate(
                            [
                                'template_id' => $template->id,
                                'category_type_id' => $potensiCatType->id,
                                'code' => Str::slug($aspekData['nama_aspek']),
                            ],
                            [
                                'name' => $aspekData['nama_aspek'],
                                'description' => "Aspek Potensi {$aspekData['nama_aspek']}",
                                'weight_percentage' => $aspekData['bobot'],
                                'standard_rating' => $aspekData['standard_rating'],
                                'order' => 1,
                            ]
                        );
                    }
                    $aspectModel = $this->aspectRegistry[$aspKey];

                    $aspectAssessBulk[] = [
                        'category_assessment_id' => $catAssessPotensi->id,
                        'participant_id' => $p->id,
                        'aspect_id' => $aspectModel->id,
                        'event_id' => $event->id,
                        'batch_id' => $b->id,
                        'position_formation_id' => $f->id,
                        'standard_rating' => $aspekData['standard_rating_toleransi'],
                        'standard_score' => $aspekData['standard_score_toleransi'],
                        'individual_rating' => $aspekData['individual_rating'],
                        'individual_score' => $aspekData['individual_score'],
                        'gap_rating' => $aspekData['gap_rating'],
                        'gap_score' => $aspekData['gap_score'],
                        'percentage_score' => (int) round(($aspekData['individual_rating'] / max(1, $aspekData['standard_rating_toleransi'])) * 100),
                        'conclusion_code' => Str::slug($aspekData['kesimpulan']),
                        'conclusion_text' => $aspekData['kesimpulan'],
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];

                    foreach ($aspekData['atributs'] as $atribData) {
                        $subKey = "{$aspectModel->id}_".Str::slug($atribData['nama_atribut']);
                        if (! isset($this->subAspectRegistry[$subKey])) {
                            $this->subAspectRegistry[$subKey] = SubAspect::firstOrCreate(
                                [
                                    'aspect_id' => $aspectModel->id,
                                    'code' => Str::slug($atribData['nama_atribut']),
                                ],
                                [
                                    'name' => $atribData['nama_atribut'],
                                    'standard_rating' => $atribData['standard_rating'],
                                    'order' => 1,
                                ]
                            );
                        }
                        $subAspectModel = $this->subAspectRegistry[$subKey];

                        $subAspectAssessList[] = [
                            'participant_id' => $p->id,
                            'category_assessment_id' => $catAssessPotensi->id,
                            'aspect_id' => $aspectModel->id,
                            'sub_aspect_id' => $subAspectModel->id,
                            'event_id' => $event->id,
                            'standard_rating' => $atribData['standard_rating'],
                            'individual_rating' => $atribData['individual_rating'],
                            'rating_label' => match ($atribData['individual_rating']) {
                                5 => 'Baik Sekali',
                                4 => 'Baik',
                                3 => 'Cukup',
                                2 => 'Kurang',
                                default => 'Sangat Kurang'
                            },
                        ];
                    }
                }
            }

            // Kompetensi Aspects
            if ($kompetensiCatType && isset($catAssessMap[$p->id][$kompetensiCatType->id])) {
                $catAssessKompetensi = $catAssessMap[$p->id][$kompetensiCatType->id];
                foreach ($rep['kompetensi']['aspek_list'] ?? ($rep['kompetensi']['aspek'] ?? []) as $komKey => $komData) {
                    $aspKey = "{$template->id}_{$kompetensiCatType->id}_".Str::slug($komData['nama_kompetensi']);
                    if (! isset($this->aspectRegistry[$aspKey])) {
                        $this->aspectRegistry[$aspKey] = Aspect::firstOrCreate(
                            [
                                'template_id' => $template->id,
                                'category_type_id' => $kompetensiCatType->id,
                                'code' => Str::slug($komData['nama_kompetensi']),
                            ],
                            [
                                'name' => $komData['nama_kompetensi'],
                                'description' => "Kompetensi Inti {$komData['nama_kompetensi']}",
                                'weight_percentage' => $komData['bobot'],
                                'standard_rating' => $komData['standard_rating'],
                                'order' => 1,
                            ]
                        );
                    }
                    $aspectModel = $this->aspectRegistry[$aspKey];

                    $aspectAssessBulk[] = [
                        'category_assessment_id' => $catAssessKompetensi->id,
                        'participant_id' => $p->id,
                        'aspect_id' => $aspectModel->id,
                        'event_id' => $event->id,
                        'batch_id' => $b->id,
                        'position_formation_id' => $f->id,
                        'standard_rating' => $komData['standard_rating_toleransi'],
                        'standard_score' => $komData['standard_score_toleransi'],
                        'individual_rating' => $komData['individual_rating'],
                        'individual_score' => $komData['individual_score'],
                        'gap_rating' => $komData['gap_rating'],
                        'gap_score' => $komData['gap_score'],
                        'percentage_score' => (int) round(($komData['individual_rating'] / max(1, $komData['standard_rating_toleransi'])) * 100),
                        'conclusion_code' => Str::slug($komData['kesimpulan']),
                        'conclusion_text' => $komData['kesimpulan'],
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
            }
        }

        if (! empty($aspectAssessBulk)) {
            $catIds = $catAssessModels->pluck('id')->toArray();
            AspectAssessment::whereIn('category_assessment_id', $catIds)->delete();
            AspectAssessment::insert($aspectAssessBulk);
        }

        // SubAspectAssessments Bulk Insert
        if (! empty($subAspectAssessList)) {
            $aspectAssessModels = AspectAssessment::whereIn('participant_id', $pIds)->get();
            $aspAssessMap = [];
            foreach ($aspectAssessModels as $aa) {
                $aspAssessMap[$aa->category_assessment_id][$aa->aspect_id] = $aa;
            }

            $subAspectBulk = [];
            foreach ($subAspectAssessList as $item) {
                $caId = $item['category_assessment_id'];
                $aspId = $item['aspect_id'];
                $pId = $item['participant_id'];
                $aa = $aspAssessMap[$caId][$aspId] ?? null;
                if (! $aa) {
                    continue;
                }

                $subAspectBulk[] = [
                    'aspect_assessment_id' => $aa->id,
                    'participant_id' => $pId,
                    'sub_aspect_id' => $item['sub_aspect_id'],
                    'event_id' => $item['event_id'],
                    'standard_rating' => $item['standard_rating'],
                    'individual_rating' => $item['individual_rating'],
                    'rating_label' => $item['rating_label'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            if (! empty($subAspectBulk)) {
                $aaIds = $aspectAssessModels->pluck('id')->toArray();
                SubAspectAssessment::whereIn('aspect_assessment_id', $aaIds)->delete();
                SubAspectAssessment::insert($subAspectBulk);
            }
        }

        // 6. Bulk Upsert FinalAssessments
        $finalBulk = [];
        foreach ($usernames as $u) {
            $p = $participantModels[$u] ?? null;
            $rep = $reportsMap[$u] ?? null;
            if (! $p || ! $rep) {
                continue;
            }

            $b = $batchMap[$u];
            $f = $formationMap[$u]['formation'];
            $kesimpulanPsikotes = $rep['kesimpulan_psikotest'] ?? [
                'potensi_std_score_akhir' => $rep['potensi']['total_standard_score'] ?? 0,
                'potensi_indiv_score_akhir' => $rep['potensi']['total_individual_score'] ?? ($rep['potensi']['total_skor_individu'] ?? 0),
                'kompetensi_std_score_akhir' => $rep['kompetensi']['total_standard_score'] ?? 0,
                'kompetensi_indiv_score_akhir' => $rep['kompetensi']['total_individual_score'] ?? ($rep['kompetensi']['total_skor_individu'] ?? 0),
                'total_std_score' => $rep['rekap']['total_skor_standar'] ?? 0,
                'total_indiv_score' => $rep['rekap']['total_skor_akhir'] ?? ($rep['rekap']['total_skor_individu'] ?? 0),
            ];
            $rekomAkhir = $rep['rekomendasi_akhir'] ?? [
                'final_code' => $rep['rekap']['kesimpulan_final'] ?? 'MS',
                'final_text' => 'MEMENUHI SYARAT (' . ($rep['rekap']['kesimpulan_final'] ?? 'MS') . ')',
            ];

            $finalBulk[] = [
                'participant_id' => $p->id,
                'event_id' => $event->id,
                'batch_id' => $b->id,
                'position_formation_id' => $f->id,
                'potensi_weight' => 40,
                'potensi_standard_score' => $kesimpulanPsikotes['potensi_std_score_akhir'] ?? 0,
                'potensi_individual_score' => $kesimpulanPsikotes['potensi_indiv_score_akhir'] ?? 0,
                'kompetensi_weight' => 60,
                'kompetensi_standard_score' => $kesimpulanPsikotes['kompetensi_std_score_akhir'] ?? 0,
                'kompetensi_individual_score' => $kesimpulanPsikotes['kompetensi_indiv_score_akhir'] ?? 0,
                'total_standard_score' => $kesimpulanPsikotes['total_std_score'] ?? 0,
                'total_individual_score' => $kesimpulanPsikotes['total_indiv_score'] ?? 0,
                'achievement_percentage' => ($kesimpulanPsikotes['total_std_score'] ?? 0) > 0 ? round((($kesimpulanPsikotes['total_indiv_score'] ?? 0) / $kesimpulanPsikotes['total_std_score']) * 100, 2) : 100,
                'conclusion_code' => $rekomAkhir['final_code'] ?? ($rekomAkhir['rekomendasi_code'] ?? 'MS'),
                'conclusion_text' => $rekomAkhir['final_text'] ?? ($rekomAkhir['rekomendasi_text'] ?? 'MEMENUHI SYARAT (MS)'),
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        if (! empty($finalBulk)) {
            FinalAssessment::upsert(
                $finalBulk,
                ['participant_id'],
                ['event_id', 'batch_id', 'position_formation_id', 'potensi_weight', 'potensi_standard_score', 'potensi_individual_score', 'kompetensi_weight', 'kompetensi_standard_score', 'kompetensi_individual_score', 'total_standard_score', 'total_individual_score', 'achievement_percentage', 'conclusion_code', 'conclusion_text', 'updated_at']
            );
        }

        return count($participantModels);
    }

    /**
     * Import 1 peserta spesifik (digunakan untuk fallback per individu jika terjadi error khusus).
     */
    public function importSingleParticipant(string $username, string $kodeProyek, AssessmentEvent $event): Participant
    {
        $this->importParticipantBatch([$username], $kodeProyek, $event);

        $participant = Participant::where('event_id', $event->id)->where('username', $username)->first();
        if (! $participant) {
            throw new Exception("Gagal mengimpor peserta {$username}");
        }

        return $participant;
    }

    /**
     * Pastikan AssessmentTemplate & CategoryTypes (Potensi & Kompetensi) tersedia.
     */
    protected function ensureAssessmentTemplate(string $levelJabatan): AssessmentTemplate
    {
        $rawJabatan = trim($levelJabatan);
        $levelJabatan = $rawJabatan !== '' ? strtoupper($rawJabatan) : 'STAFF';

        $code = Str::slug("template-{$levelJabatan}");
        if (isset($this->templateRegistry[$code])) {
            return $this->templateRegistry[$code];
        }

        $name = 'Standar Jabatan '.ucfirst(strtolower($levelJabatan));

        $template = AssessmentTemplate::firstOrCreate(
            ['code' => $code],
            [
                'name' => $name,
                'description' => "Template Otomatis Import LSP untuk Jabatan {$levelJabatan}",
            ]
        );

        CategoryType::firstOrCreate(
            ['template_id' => $template->id, 'code' => 'potensi'],
            [
                'name' => 'Potensi',
                'weight_percentage' => 40,
                'order' => 1,
            ]
        );

        CategoryType::firstOrCreate(
            ['template_id' => $template->id, 'code' => 'kompetensi'],
            [
                'name' => 'Kompetensi',
                'weight_percentage' => 60,
                'order' => 2,
            ]
        );

        $this->templateRegistry[$code] = $template;

        return $template;
    }
}
