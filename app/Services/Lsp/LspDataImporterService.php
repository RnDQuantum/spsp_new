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
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class LspDataImporterService
{
    public function __construct(
        protected LspIndividualReportService $reportService
    ) {}

    /**
     * Import seluruh peserta dari proyek LSP tertentu ke database SPSP.
     */
    public function importProject(string $kodeProyek, ?string $singleUsername = null, ?int $institutionId = null): array
    {
        $dbLsp = DB::connection('lsp');

        // 1. Dapatkan atau buat Institution default SPSP (misal Kejaksaan Agung)
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

        $pesertaRows = $query->get();

        if ($pesertaRows->isEmpty()) {
            throw new Exception("Tidak ada data peserta ditemukan pada proyek LSP '{$kodeProyek}'".($singleUsername ? " untuk username '{$singleUsername}'" : ''));
        }

        $importedCount = 0;
        $failedCount = 0;
        $errors = [];

        foreach ($pesertaRows as $pesertaLsp) {
            try {
                DB::transaction(function () use ($pesertaLsp, $kodeProyek, $event) {
                    $this->importSingleParticipant($pesertaLsp->username, $kodeProyek, $event);
                });
                $importedCount++;
            } catch (Exception $e) {
                $failedCount++;
                $errors[] = "Peserta {$pesertaLsp->username}: ".$e->getMessage();
            }
        }

        return [
            'event_id' => $event->id,
            'event_code' => $event->code,
            'event_name' => $event->name,
            'total_found' => $pesertaRows->count(),
            'imported_count' => $importedCount,
            'failed_count' => $failedCount,
            'errors' => $errors,
        ];
    }

    /**
     * Import satu peserta spesifik beserta seluruh agregasi data penilaiatnya.
     */
    public function importSingleParticipant(string $username, string $kodeProyek, AssessmentEvent $event): Participant
    {
        $dbLsp = DB::connection('lsp');
        $pesertaLspRow = $dbLsp->table('peserta_produksi')->where('username', $username)->first();

        // Execute report calculation service to get structured DTO
        $reportData = $this->reportService->getIndividualReport($username, $kodeProyek);

        $pesertaInfo = $reportData['peserta'];

        // 1. Sinkronkan Batch
        $batchName = $pesertaLspRow->batch ?? '1';
        $location = $reportData['metadata_proyek']['lokasi'] ?? 'Pusat';
        $batch = Batch::updateOrCreate(
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

        // 2. Sinkronkan Master Template & PositionFormation
        $levelJabatan = strtoupper($pesertaInfo['jabatan_pelaksana'] ?? 'STAFF');
        $formationCode = Str::slug($levelJabatan);
        $formationName = $pesertaInfo['minat_penempatan'] !== '-' ? $pesertaInfo['minat_penempatan'] : $levelJabatan;

        $template = $this->ensureAssessmentTemplate($levelJabatan);

        $formation = PositionFormation::updateOrCreate(
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

        // 3. Upsert Participant Record
        $participant = Participant::updateOrCreate(
            [
                'event_id' => $event->id,
                'username' => $username,
            ],
            [
                'batch_id' => $batch->id,
                'position_formation_id' => $formation->id,
                'test_number' => $pesertaInfo['no_test'],
                'skb_number' => $pesertaInfo['no_kjg'],
                'name' => $pesertaInfo['nama_lengkap'],
                'gender' => $pesertaInfo['jenis_kelamin'],
                'photo_path' => $pesertaInfo['pasfoto'],
                'assessment_date' => $reportData['metadata_proyek']['tanggal_pelaksanaan'],
            ]
        );

        // 4. Upsert PsychologicalTest (Data MMPI)
        $kejiwaan = $reportData['kejiwaan'];
        $rawNilaiPq = $kejiwaan['nilai_pq'] ?? 0;
        $numericNilaiPq = is_numeric(trim((string) $rawNilaiPq)) ? (float) $rawNilaiPq : 0.00;

        PsychologicalTest::updateOrCreate(
            [
                'event_id' => $event->id,
                'participant_id' => $participant->id,
            ],
            [
                'no_test' => $pesertaInfo['no_test'],
                'username' => $username,
                'validitas' => $kejiwaan['validitas'],
                'internal' => implode(' ', $kejiwaan['internal_pribadi']),
                'interpersonal' => implode(' ', $kejiwaan['interpersonal']),
                'kap_kerja' => implode(' ', $kejiwaan['kapasitas_kerja']),
                'klinik' => implode(' ', $kejiwaan['klinis']),
                'kesimpulan' => implode(' ', $kejiwaan['kesimpulan']),
                'psikogram' => json_encode($kejiwaan['psikogram']),
                'nilai_pq' => $numericNilaiPq,
                'tingkat_stres' => $kejiwaan['tingkat_stres'],
            ]
        );

        // 5. Upsert Interpretations (Potensi & Kompetensi Narrative)
        $potensiCatType = CategoryType::query()->where('template_id', $template->id)->where('code', 'potensi')->first();
        $kompetensiCatType = CategoryType::query()->where('template_id', $template->id)->where('code', 'kompetensi')->first();

        if ($potensiCatType && ! empty($reportData['interpretasi']['potensi_text'])) {
            Interpretation::updateOrCreate(
                [
                    'participant_id' => $participant->id,
                    'event_id' => $event->id,
                    'category_type_id' => $potensiCatType->id,
                ],
                [
                    'interpretation_text' => $reportData['interpretasi']['potensi_text'],
                ]
            );
        }

        if ($kompetensiCatType && ! empty($reportData['interpretasi']['kompetensi_text'])) {
            Interpretation::updateOrCreate(
                [
                    'participant_id' => $participant->id,
                    'event_id' => $event->id,
                    'category_type_id' => $kompetensiCatType->id,
                ],
                [
                    'interpretation_text' => $reportData['interpretasi']['kompetensi_text'],
                ]
            );
        }

        // 6. Upsert CategoryAssessments & AspectAssessments (Potensi)
        if ($potensiCatType) {
            $potensiData = $reportData['potensi'];

            $catAssessPotensi = CategoryAssessment::updateOrCreate(
                [
                    'participant_id' => $participant->id,
                    'event_id' => $event->id,
                    'category_type_id' => $potensiCatType->id,
                ],
                [
                    'batch_id' => $batch->id,
                    'position_formation_id' => $formation->id,
                    'total_standard_rating' => $potensiData['total_standard_rating'],
                    'total_standard_score' => $potensiData['total_standard_score'],
                    'total_individual_rating' => $potensiData['total_individual_rating'],
                    'total_individual_score' => $potensiData['total_individual_score'],
                    'gap_rating' => $potensiData['gap_total_rating'],
                    'gap_score' => $potensiData['gap_total_score'],
                    'conclusion_code' => $potensiData['kesimpulan_akhir'] === 'Memenuhi Standard' ? 'MS' : 'TMS',
                    'conclusion_text' => strtoupper($potensiData['kesimpulan_akhir']),
                ]
            );

            foreach ($potensiData['aspek_list'] as $aspekKey => $aspekData) {
                $aspectModel = Aspect::query()->where('template_id', $template->id)
                    ->where('category_type_id', $potensiCatType->id)
                    ->where('name', $aspekData['nama_aspek'])
                    ->first();

                if (! $aspectModel) {
                    $aspectModel = Aspect::create([
                        'template_id' => $template->id,
                        'category_type_id' => $potensiCatType->id,
                        'code' => Str::slug($aspekData['nama_aspek']),
                        'name' => $aspekData['nama_aspek'],
                        'description' => "Aspek Potensi {$aspekData['nama_aspek']}",
                        'weight_percentage' => $aspekData['bobot'],
                        'standard_rating' => $aspekData['standard_rating'],
                        'order' => 1,
                    ]);
                }

                $aspAssess = AspectAssessment::updateOrCreate(
                    [
                        'category_assessment_id' => $catAssessPotensi->id,
                        'participant_id' => $participant->id,
                        'aspect_id' => $aspectModel->id,
                    ],
                    [
                        'event_id' => $event->id,
                        'batch_id' => $batch->id,
                        'position_formation_id' => $formation->id,
                        'standard_rating' => $aspekData['standard_rating_toleransi'],
                        'standard_score' => $aspekData['standard_score_toleransi'],
                        'individual_rating' => $aspekData['individual_rating'],
                        'individual_score' => $aspekData['individual_score'],
                        'gap_rating' => $aspekData['gap_rating'],
                        'gap_score' => $aspekData['gap_score'],
                        'percentage_score' => (int) round(($aspekData['individual_rating'] / max(1, $aspekData['standard_rating_toleransi'])) * 100),
                        'conclusion_code' => Str::slug($aspekData['kesimpulan']),
                        'conclusion_text' => $aspekData['kesimpulan'],
                    ]
                );

                // Upsert SubAspectAssessments
                foreach ($aspekData['atributs'] as $atribData) {
                    $subAspectModel = SubAspect::query()->where('aspect_id', $aspectModel->id)
                        ->where('name', $atribData['nama_atribut'])
                        ->first();

                    if (! $subAspectModel) {
                        $subAspectModel = SubAspect::create([
                            'aspect_id' => $aspectModel->id,
                            'code' => Str::slug($atribData['nama_atribut']),
                            'name' => $atribData['nama_atribut'],
                            'standard_rating' => $atribData['standard_rating'],
                            'order' => 1,
                        ]);
                    }

                    SubAspectAssessment::updateOrCreate(
                        [
                            'aspect_assessment_id' => $aspAssess->id,
                            'participant_id' => $participant->id,
                            'sub_aspect_id' => $subAspectModel->id,
                        ],
                        [
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
                        ]
                    );
                }
            }
        }

        // 7. Upsert CategoryAssessments & AspectAssessments (Kompetensi)
        if ($kompetensiCatType) {
            $kompetensiData = $reportData['kompetensi'];

            $catAssessKompetensi = CategoryAssessment::updateOrCreate(
                [
                    'participant_id' => $participant->id,
                    'event_id' => $event->id,
                    'category_type_id' => $kompetensiCatType->id,
                ],
                [
                    'batch_id' => $batch->id,
                    'position_formation_id' => $formation->id,
                    'total_standard_rating' => $kompetensiData['total_standard_rating'],
                    'total_standard_score' => $kompetensiData['total_standard_score'],
                    'total_individual_rating' => $kompetensiData['total_individual_rating'],
                    'total_individual_score' => $kompetensiData['total_individual_score'],
                    'gap_rating' => $kompetensiData['gap_total_rating'],
                    'gap_score' => $kompetensiData['gap_total_score'],
                    'conclusion_code' => $kompetensiData['kesimpulan_akhir'] === 'Sangat Kompeten' ? 'SK' : ($kompetensiData['kesimpulan_akhir'] === 'Kompeten' ? 'K' : 'BK'),
                    'conclusion_text' => strtoupper($kompetensiData['kesimpulan_akhir']),
                ]
            );

            foreach ($kompetensiData['aspek_list'] as $komKey => $komData) {
                $aspectModel = Aspect::query()->where('template_id', $template->id)
                    ->where('category_type_id', $kompetensiCatType->id)
                    ->where('name', $komData['nama_kompetensi'])
                    ->first();

                if (! $aspectModel) {
                    $aspectModel = Aspect::create([
                        'template_id' => $template->id,
                        'category_type_id' => $kompetensiCatType->id,
                        'code' => Str::slug($komData['nama_kompetensi']),
                        'name' => $komData['nama_kompetensi'],
                        'description' => "Kompetensi Inti {$komData['nama_kompetensi']}",
                        'weight_percentage' => $komData['bobot'],
                        'standard_rating' => $komData['standard_rating'],
                        'order' => 1,
                    ]);
                }

                AspectAssessment::updateOrCreate(
                    [
                        'category_assessment_id' => $catAssessKompetensi->id,
                        'participant_id' => $participant->id,
                        'aspect_id' => $aspectModel->id,
                    ],
                    [
                        'event_id' => $event->id,
                        'batch_id' => $batch->id,
                        'position_formation_id' => $formation->id,
                        'standard_rating' => $komData['standard_rating_toleransi'],
                        'standard_score' => $komData['standard_score_toleransi'],
                        'individual_rating' => $komData['individual_rating'],
                        'individual_score' => $komData['individual_score'],
                        'gap_rating' => $komData['gap_rating'],
                        'gap_score' => $komData['gap_score'],
                        'percentage_score' => (int) round(($komData['individual_rating'] / max(1, $komData['standard_rating_toleransi'])) * 100),
                        'conclusion_code' => Str::slug($komData['kesimpulan']),
                        'conclusion_text' => $komData['kesimpulan'],
                    ]
                );
            }
        }

        // 8. Upsert FinalAssessments
        $kesimpulanPsikotes = $reportData['kesimpulan_psikotest'];
        $rekomAkhir = $reportData['rekomendasi_akhir'];

        FinalAssessment::updateOrCreate(
            [
                'participant_id' => $participant->id,
                'event_id' => $event->id,
            ],
            [
                'batch_id' => $batch->id,
                'position_formation_id' => $formation->id,
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
            ]
        );

        return $participant;
    }

    /**
     * Pastikan AssessmentTemplate & CategoryTypes (Potensi & Kompetensi) tersedia.
     */
    protected function ensureAssessmentTemplate(string $levelJabatan): AssessmentTemplate
    {
        $code = Str::slug("template-{$levelJabatan}");
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

        return $template;
    }
}
