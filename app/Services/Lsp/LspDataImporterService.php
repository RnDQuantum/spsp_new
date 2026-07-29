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
use App\Models\Participant;
use App\Models\PositionFormation;
use App\Models\PsychologicalTest;
use App\Models\SubAspect;
use App\Models\SubAspectAssessment;
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
        protected LspDataTransformerService $reportService
    ) {}

    /**
     * Import seluruh peserta dari proyek LSP tertentu ke database SPSP dengan metode chunking & bulk upsert.
     */
    public function importProject(string $kodeProyek, ?string $singleUsername = null, ?int $institutionId = null, ?Closure $progressCallback = null): array
    {
        $dbLsp = DB::connection('lsp');

        // 1. Dapatkan atau buat Institution default SPSP
        if (! $institutionId) {
            $institution = Institution::firstOrCreate(
                ['code' => 'kejaksaan'],
                [
                    'name' => 'Kejaksaan Agung RI',
                    'logo_path' => 'logos/kejaksaan.png',
                    'api_key' => Str::random(32),
                ]
            );
            $institutionId = $institution->id;
        }

        // 2. Dapatkan proyek dari LSP DB & Sinkronkan AssessmentEvent SPSP
        $proyekLsp = $dbLsp->table('proyek')->where('kode_proyek', $kodeProyek)->first();
        $namaProyek = $proyekLsp->nama_proyek ?? "Proyek LSP {$kodeProyek}";
        $tanggalMulai = $proyekLsp->tanggal_pelaksanaan ?? date('Y-m-d');
        $tanggalSelesai = $proyekLsp->sampai_tanggal ?? date('Y-m-d');
        $tahun = (int) date('Y', strtotime($tanggalMulai));

        $event = AssessmentEvent::updateOrCreate(
            ['code' => $kodeProyek],
            [
                'institution_id' => $institutionId,
                'name' => $namaProyek,
                'description' => "Imported from LSP DB Project {$kodeProyek}",
                'year' => $tahun,
                'start_date' => $tanggalMulai,
                'end_date' => $tanggalSelesai,
                'status' => 'completed',
                'last_synced_at' => now(),
            ]
        );

        // 3. Ambil daftar peserta dari peserta_produksi
        $query = $dbLsp->table('peserta_produksi')
            ->where(function ($q) use ($kodeProyek) {
                $q->where('kode_pelaksanaan', $kodeProyek)
                    ->orWhere('kode_pelaksanaan', 'LIKE', "%{$kodeProyek}%");
            });

        if ($singleUsername) {
            $query->where('username', $singleUsername);
        }

        $pesertaRows = $query->select('username', 'batch', 'jabatan_pelaksana', 'minat_penempatan')->get();

        if ($pesertaRows->isEmpty()) {
            throw new Exception("Tidak ada data peserta ditemukan pada proyek LSP '{$kodeProyek}'".($singleUsername ? " untuk username '{$singleUsername}'" : ''));
        }

        $allUsernames = $pesertaRows->pluck('username')->unique()->toArray();
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
                // Fallback attempt: if bulk transaction fails for chunk, retry individually for error isolation
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

        $reportsMap = $this->reportService->getBatchIndividualReports($usernames, $kodeProyek);
        if (empty($reportsMap)) {
            return 0;
        }

        $now = now()->toDateTimeString();

        // 1. Resolve & Batch Create Master Batches & Formations
        $batchMap = [];
        $formationMap = [];

        foreach ($usernames as $u) {
            $rep = $reportsMap[$u] ?? null;
            if (! $rep) {
                continue;
            }

            $pesertaInfo = $rep['peserta'];
            $batchName = $rep['peserta']['batch'] ?? '1';
            $location = $rep['metadata_proyek']['lokasi'] ?? 'Pusat';

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
            $rep = $reportsMap[$u] ?? null;
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
                'skb_number' => $pesertaInfo['no_kjg'],
                'name' => $pesertaInfo['nama_lengkap'],
                'gender' => $pesertaInfo['jenis_kelamin'],
                'username' => $u,
                'photo_path' => $pesertaInfo['pasfoto'],
                'assessment_date' => $rep['metadata_proyek']['tanggal_pelaksanaan'],
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

        // 3. Prepare Bulk Insert PsychologicalTest
        $psychBulk = [];
        foreach ($usernames as $u) {
            $p = $participantModels[$u] ?? null;
            $rep = $reportsMap[$u] ?? null;
            if (! $p || ! $rep) {
                continue;
            }

            $kejiwaan = $rep['kejiwaan'];
            $rawNilaiPq = $kejiwaan['nilai_pq'] ?? 0;
            $numericNilaiPq = is_numeric(trim((string) $rawNilaiPq)) ? (float) $rawNilaiPq : 0.00;

            $psychBulk[] = [
                'event_id' => $event->id,
                'participant_id' => $p->id,
                'no_test' => $rep['peserta']['no_test'],
                'username' => $u,
                'validitas' => $kejiwaan['validitas'],
                'internal' => implode(' ', $kejiwaan['internal_pribadi']),
                'interpersonal' => implode(' ', $kejiwaan['interpersonal']),
                'kap_kerja' => implode(' ', $kejiwaan['kapasitas_kerja']),
                'klinik' => implode(' ', $kejiwaan['klinis']),
                'kesimpulan' => implode(' ', $kejiwaan['kesimpulan']),
                'psikogram' => json_encode($kejiwaan['psikogram']),
                'nilai_pq' => $numericNilaiPq,
                'tingkat_stres' => $kejiwaan['tingkat_stres'],
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        if (! empty($psychBulk)) {
            PsychologicalTest::whereIn('participant_id', $pIds)->delete();
            PsychologicalTest::insert($psychBulk);
        }

        // 4. Prepare Bulk Upsert Interpretations & CategoryAssessments
        $interpBulk = [];
        $catAssessBulk = [];

        foreach ($usernames as $u) {
            $p = $participantModels[$u] ?? null;
            $rep = $reportsMap[$u] ?? null;
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
                    'total_standard_rating' => $potensiData['total_standard_rating'],
                    'total_standard_score' => $potensiData['total_standard_score'],
                    'total_individual_rating' => $potensiData['total_individual_rating'],
                    'total_individual_score' => $potensiData['total_individual_score'],
                    'gap_rating' => $potensiData['gap_total_rating'],
                    'gap_score' => $potensiData['gap_total_score'],
                    'conclusion_code' => $potensiData['kesimpulan_akhir'] === 'Memenuhi Standard' ? 'MS' : 'TMS',
                    'conclusion_text' => strtoupper($potensiData['kesimpulan_akhir']),
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
                    'total_standard_rating' => $kompetensiData['total_standard_rating'],
                    'total_standard_score' => $kompetensiData['total_standard_score'],
                    'total_individual_rating' => $kompetensiData['total_individual_rating'],
                    'total_individual_score' => $kompetensiData['total_individual_score'],
                    'gap_rating' => $kompetensiData['gap_total_rating'],
                    'gap_score' => $kompetensiData['gap_total_score'],
                    'conclusion_code' => $kompetensiData['kesimpulan_akhir'] === 'Sangat Kompeten' ? 'SK' : ($kompetensiData['kesimpulan_akhir'] === 'Kompeten' ? 'K' : 'BK'),
                    'conclusion_text' => strtoupper($kompetensiData['kesimpulan_akhir']),
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
            $rep = $reportsMap[$u] ?? null;
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
                foreach ($rep['potensi']['aspek_list'] as $aspekKey => $aspekData) {
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
                foreach ($rep['kompetensi']['aspek_list'] as $komKey => $komData) {
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
            $kesimpulanPsikotes = $rep['kesimpulan_psikotest'];
            $rekomAkhir = $rep['rekomendasi_akhir'];

            $finalBulk[] = [
                'participant_id' => $p->id,
                'event_id' => $event->id,
                'batch_id' => $b->id,
                'position_formation_id' => $f->id,
                'potensi_weight' => 40,
                'potensi_standard_score' => $kesimpulanPsikotes['potensi_std_score_akhir'],
                'potensi_individual_score' => $kesimpulanPsikotes['potensi_indiv_score_akhir'],
                'kompetensi_weight' => 60,
                'kompetensi_standard_score' => $kesimpulanPsikotes['kompetensi_std_score_akhir'],
                'kompetensi_individual_score' => $kesimpulanPsikotes['kompetensi_indiv_score_akhir'],
                'total_standard_score' => $kesimpulanPsikotes['total_std_score'],
                'total_individual_score' => $kesimpulanPsikotes['total_indiv_score'],
                'achievement_percentage' => $kesimpulanPsikotes['total_std_score'] > 0 ? round(($kesimpulanPsikotes['total_indiv_score'] / $kesimpulanPsikotes['total_std_score']) * 100, 2) : 100,
                'conclusion_code' => $rekomAkhir['final_code'],
                'conclusion_text' => $rekomAkhir['final_text'],
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
