<?php

namespace App\Services;

use App\Models\TestResult;
use App\Services\Lsp\LspNormEngineService;
use Exception;

/**
 * TestReportService - Service penyedia Laporan Alat Tes (Detail per Instrumen Tes)
 *
 * Bertanggung jawab menyusun laporan detail per alat tes (IST, PAPI Kostik, 16PF, CFIT, Kraepelin, dll.)
 * baik dari data mentah SPSP (tabel test_results) maupun dari norm engine.
 */
class TestReportService
{
    public function __construct(
        protected LspNormEngineService $normEngine
    ) {}

    /**
     * Ambil data laporan detail alat tes tertentu untuk seorang peserta di event tertentu.
     */
    public function getTestReport(int $participantId, int $eventId, string $testCode): array
    {
        $testResult = TestResult::query()
            ->where('participant_id', $participantId)
            ->where('event_id', $eventId)
            ->where('test_code', $testCode)
            ->first();

        if (! $testResult) {
            throw new Exception("Data alat tes dengan kode '{$testCode}' tidak ditemukan untuk peserta ID {$participantId}.");
        }

        return [
            'test_code' => $testResult->test_code,
            'test_name' => $testResult->test_name,
            'test_category' => $testResult->test_category,
            'status' => $testResult->status,
            'test_started_at' => $testResult->test_started_at,
            'summary_data' => $testResult->summary_data,
            'interpretation_data' => $testResult->interpretation_data,
        ];
    }

    /**
     * Ambil seluruh laporan detail alat tes yang dimiliki peserta pada event tertentu.
     */
    public function getParticipantAllTestReports(int $participantId, int $eventId): array
    {
        $testResults = TestResult::query()
            ->where('participant_id', $participantId)
            ->where('event_id', $eventId)
            ->get();

        $reports = [];
        foreach ($testResults as $tr) {
            $reports[$tr->test_code] = [
                'test_code' => $tr->test_code,
                'test_name' => $tr->test_name,
                'test_category' => $tr->test_category,
                'status' => $tr->status,
                'source' => $tr->source,
                'summary_data' => $tr->summary_data,
                'interpretation_data' => $tr->interpretation_data,
                'formatted' => $this->formatTestDataForDisplay($tr),
            ];
        }

        return $reports;
    }

    /**
     * Dictionaries metadata instrumen tes untuk penyajian deskriptif & profesional
     */
    public static array $cfitSubtestMeta = [
        'sub1' => ['code' => 'Subtes 1', 'name' => 'Series (Penalaran Serial)', 'desc' => 'Mengukur kemampuan penalaran sekuensial, sistematika berpikir, dan kemampuan mengenali keteraturan pola logis berkelanjutan.'],
        'sub2' => ['code' => 'Subtes 2', 'name' => 'Classification (Klasifikasi Visual)', 'desc' => 'Mengukur ketelitian diskriminasi visual dan kemampuan mengidentifikasi perbedaan esensial antar elemen/stimulus.'],
        'sub3' => ['code' => 'Subtes 3', 'name' => 'Matrices (Analisis Matriks)', 'desc' => 'Mengukur kemampuan pemecahan masalah non-verbal, analisis mendalam, dan penalaran matriks relasional.'],
        'sub4' => ['code' => 'Subtes 4', 'name' => 'Conditions (Topologi / Kondisi)', 'desc' => 'Mengukur daya abstraksi logika spasial, pemahaman syarat geometris, dan penalaran aturan kondisi ruang.'],
    ];

    public static array $istSubtestMeta = [
        'SE' => ['code' => 'SE', 'name' => 'Satzergänzung (Pembentukan Keputusan)', 'domain' => 'Verbal', 'desc' => 'Penilaian praktis verbal, pembentukan keputusan mandiri, dan pertimbangan akal sehat (common sense).'],
        'WA' => ['code' => 'WA', 'name' => 'Wortauswahl (Fleksibilitas Bahasa)', 'domain' => 'Verbal', 'desc' => 'Kekayaan kosakata, pemahaman makna linguistik, dan kepekaan menangkap nuansa bahasa.'],
        'AN' => ['code' => 'AN', 'name' => 'Analogien (Analogi & Korelasi)', 'domain' => 'Verbal', 'desc' => 'Daya kombinasi relasional, fleksibilitas berpikir, dan penalaran analogis antar konsep.'],
        'GE' => ['code' => 'GE', 'name' => 'Gemeinsamkeiten (Abstraksi Konseptual)', 'domain' => 'Verbal', 'desc' => 'Kemampuan berpikir abstrak, generalisasi ide, dan menemukan esensi kesamaan prinsip.'],
        'ME' => ['code' => 'ME', 'name' => 'Merkaufgaben (Daya Ingat & Retensi)', 'domain' => 'Memori', 'desc' => 'Kapasitas konsentrasi, retensi ingatan jangka pendek, dan daya serap informasi baru secara presisi.'],
        'RA' => ['code' => 'RA', 'name' => 'Rechenaufgaben (Hitungan Praktis)', 'domain' => 'Numerik', 'desc' => 'Penalaran matematis praktis dalam memecahkan masalah kuantitatif dan logika hitungan terapan.'],
        'ZR' => ['code' => 'ZR', 'name' => 'Zahlenreihen (Logika Deret Angka)', 'domain' => 'Numerik', 'desc' => 'Logika induktif angka, analisis pola ritmis deret angka, dan keteraturan matematis.'],
        'FA' => ['code' => 'FA', 'name' => 'Figurenauswahl (Visualisasi Spasial 2D)', 'domain' => 'Spasial', 'desc' => 'Imajinasi visual dua dimensi, sintesis bentuk bidang datar, dan rekonstruksi pola grafis.'],
        'WU' => ['code' => 'WU', 'name' => 'Würfelaufgaben (Orientasi Ruang 3D)', 'domain' => 'Spasial', 'desc' => 'Daya orientasi ruang tiga dimensi, visualisasi struktur objek, dan pemetaan kedalaman bentuk.'],
    ];

    public static array $papiDomainMeta = [
        'Arah Kerja (Work Direction)' => [
            'N' => ['name' => 'Ketekunan (Need to finish task)', 'code' => 'N', 'desc' => 'Dorongan menyelesaikan tugas sampai tuntas dan tekun menghadapi rintangan kerja.'],
            'G' => ['name' => 'Keinginan Bekerja Keras (Hard intense worker)', 'code' => 'G', 'desc' => 'Intensitas energi kerja, daya tahan fisik, dan dorongan kerja keras.'],
            'A' => ['name' => 'Semangat Berprestasi (Need to achieve)', 'code' => 'A', 'desc' => 'Ambisi mencapai standar target tinggi dan dorongan keberhasilan personal.'],
        ],
        'Kepemimpinan (Leadership Role)' => [
            'L' => ['name' => 'Peran Kepemimpinan (Leadership role)', 'code' => 'L', 'desc' => 'Keyakinan memimpin, mengarahkan kelompok, dan mengambil inisiatif pemimpin.'],
            'P' => ['name' => 'Mengelola Orang Lain (Need to control others)', 'code' => 'P', 'desc' => 'Hasrat mengatur, mengawasi, dan mengarahkan bawahan.'],
            'I' => ['name' => 'Pengambilan Keputusan (Ease in decision making)', 'code' => 'I', 'desc' => 'Ketegasan, keberanian, dan kecepatan memutuskan tindakan.'],
        ],
        'Aktivitas & Kecekatan (Activity)' => [
            'T' => ['name' => 'Kecekatan Kerja (Pace)', 'code' => 'T', 'desc' => 'Tempo kecepatan eksekusi tugas dan kecekatan bertindak.'],
            'V' => ['name' => 'Semangat Kerja (Vigorous type)', 'code' => 'V', 'desc' => 'Stamina fisik, vitalitas energi, dan ketahanan beraktivitas dinamis.'],
        ],
        'Interaksi Sosial (Social Nature)' => [
            'X' => ['name' => 'Kepercayaan Diri (Need to be noticed)', 'code' => 'X', 'desc' => 'Kebutuhan tampil percaya diri di depan publik dan eksistensi sosial.'],
            'S' => ['name' => 'Interaksi Sosial (Social extension)', 'code' => 'S', 'desc' => 'Kemudahan membina pergaulan ramah dan keterbukaan sosial.'],
            'B' => ['name' => 'Kerjasama Tim (Need to belong to groups)', 'code' => 'B', 'desc' => 'Orientasi kerjasama tim, rasa memiliki, dan kebersamaan kelompok.'],
            'O' => ['name' => 'Hubungan Hangat (Need for closeness)', 'code' => 'O', 'desc' => 'Kebutuhan membina hubungan hangat, personal, dan intim dengan rekan.'],
        ],
        'Gaya Kerja (Work Style)' => [
            'R' => ['name' => 'Berpikir Analitis (Theoretical type)', 'code' => 'R', 'desc' => 'Preferensi telaah analitis, penalaran konseptual, dan dasar teoritis.'],
            'D' => ['name' => 'Ketelitian Kerja (Working with details)', 'code' => 'D', 'desc' => 'Perhatian cermat pada rincian data, akurasi, dan kontrol mutu kerja.'],
            'C' => ['name' => 'Sistematika Kerja (Organized type)', 'code' => 'C', 'desc' => 'Keteraturan metodis, perencanaan sistematis, dan kerapihan kerja.'],
        ],
        'Adaptabilitas & Temperamen (Temperament)' => [
            'Z' => ['name' => 'Toleransi Perubahan (Need for change)', 'code' => 'Z', 'desc' => 'Kesiapan menerima variasi tugas, inovasi, dan adaptasi perubahan.'],
            'E' => ['name' => 'Pengendalian Emosi (Emotional restraint)', 'code' => 'E', 'desc' => 'Pengendalian diri, ketenangan emosional, dan stabilitas suasana hati.'],
            'K' => ['name' => 'Asertifitas (Need to be forceful)', 'code' => 'K', 'desc' => 'Keberanian bersikap asertif, daya juang membela argumen, dan daya dorong.'],
        ],
        'Kepatuhan & Loyalitas (Followership)' => [
            'F' => ['name' => 'Loyalitas Otoritas (Need to support authority)', 'code' => 'F', 'desc' => 'Kepatuhan dan kesetiaan mendukung pimpinan dan kebijakan institusi.'],
            'W' => ['name' => 'Kepatuhan Aturan (Need for rules & supervision)', 'code' => 'W', 'desc' => 'Kebutuhan akan panduan kerja jelas, SOP baku, dan supervisi terarah.'],
        ],
    ];

    public static array $sixteenPfFactorMeta = [
        'A' => ['name' => 'Kehangatan (Warmth)', 'low' => 'Pendiam, kritis, suka menyendiri, berhati-hati', 'high' => 'Ramah tamah, hangat, mudah berpartisipasi sosial'],
        'B' => ['name' => 'Penalaran (Reasoning)', 'low' => 'Pemikiran konkret, kapasitas mental praktis sederhana', 'high' => 'Cepat menyerap ide abstrak, cerdas, berwawasan luas'],
        'C' => ['name' => 'Kestabilan Emosi (Emotional Stability)', 'low' => 'Reaktif, mudah terpengaruh perasaan, rentan stres', 'high' => 'Matang secara emosional, tenang, realistis menghadapi masalah'],
        'E' => ['name' => 'Dominansi (Dominance)', 'low' => 'Kooperatif, patuh, menghindari konfrontasi', 'high' => 'Asertif, kompetitif, percaya diri tinggi, dominan'],
        'F' => ['name' => 'Kegembiraan (Liveliness)', 'low' => 'Serius, berhati-hati, tenang, terencana', 'high' => 'Antusias, spontan, ekspresif, ceria'],
        'G' => ['name' => 'Kepatuhan Aturan (Rule-Consciousness)', 'low' => 'Fleksibel, bebas aturan baku, oportunistik', 'high' => 'Taat asas, bermoral kuat, berdisiplin tinggi'],
        'H' => ['name' => 'Keberanian Sosial (Social Boldness)', 'low' => 'Pemalu, peka terhadap ancaman sosial, berhati-hati', 'high' => 'Berani mengambil inisiatif sosial, tegas, tidak canggung'],
        'I' => ['name' => 'Kepekaan Perasaan (Sensitivity)', 'low' => 'Tangguh, praktis, mengutamakan logika faktual', 'high' => 'Sensitif, intuitif, berempati tinggi, artistik'],
        'L' => ['name' => 'Kewaspadaan (Vigilance)', 'low' => 'Mudah percaya, menerima orang lain apa adanya', 'high' => 'Waspada, kritis, skeptis terhadap motif tersembunyi'],
        'M' => ['name' => 'Abstraksi / Imajinasi (Abstractedness)', 'low' => 'Praktis, berpijak pada kenyataan konkret', 'high' => 'Imajinatif, konseptual, berorientasi ide dan visi'],
        'N' => ['name' => 'Kehati-hatian Sosial (Privateness)', 'low' => 'Terbuka, lugas, polos, apa adanya', 'high' => 'Diplomatis, cermat menjaga privasi pribadi'],
        'O' => ['name' => 'Kecemasan Diri (Apprehension)', 'low' => 'Percaya diri, tenang, tidak mudah cemas', 'high' => 'Rentan khawatir, kritis terhadap diri sendiri'],
        'Q1' => ['name' => 'Keterbukaan Perubahan (Openness to Change)', 'low' => 'Tradisional, nyaman dengan pola kerja mapan', 'high' => 'Terbuka pada inovasi, eksperimentatif, fleksibel'],
        'Q2' => ['name' => 'Kemandirian (Self-Reliance)', 'low' => 'Berorientasi kelompok, butuh dukungan rekan', 'high' => 'Mandiri, soliter, mandiri dalam mengambil keputusan'],
        'Q3' => ['name' => 'Perfeksionisme (Perfectionism)', 'low' => 'Toleran pada ketidakteraturan, santai', 'high' => 'Disiplin diri tinggi, teratur, mengutamakan kesempurnaan'],
        'Q4' => ['name' => 'Ketegangan Energi (Tension)', 'low' => 'Rileks, sabar, tenang', 'high' => 'Dorongan energi tinggi, dinamis, tidak sabaran'],
    ];

    public static array $kraepelinMeta = [
        'pspeed' => ['name' => 'Kecepatan Kerja (PANKER)', 'unit' => 'item/menit', 'desc' => 'Mengukur tempo kecepatan output kerja per satuan waktu di bawah target.'],
        'pacc' => ['name' => 'Ketelitian Kerja (JANKER)', 'unit' => 'rata-rata kesalahan', 'desc' => 'Mengukur derajat akurasi kerja dan tingkat kekebalan terhadap kekeliruan (error rate).'],
        'pstab' => ['name' => 'Kestabilan Kerja (HANKER)', 'unit' => 'indeks ritme', 'desc' => 'Mengukur konsistensi ritme kerja tanpa fluktuasi grafik energi yang ekstrem.'],
        'pstn' => ['name' => 'Ketahanan Kerja (TIANKER)', 'unit' => 'indeks daya tahan', 'desc' => 'Mengukur daya tahan stamina kerja dan pemeliharaan fokus saat mengalami kejenuhan mental.'],
    ];

    /**
     * Format payload summary_data & interpretation_data untuk kebutuhan tampilan UI Laporan Alat Tes.
     */
    public function formatTestDataForDisplay(TestResult $tr): array
    {
        $data = $tr->summary_data ?? [];
        $interp = $tr->interpretation_data ?? [];

        // Format per kode tes
        return match ($tr->test_code) {
            'A.1', 'A.2', 'A.5' => (function () use ($data, $interp, $tr) {
                $rawKategori = $data['kategori'] ?? ($data['hasil_kategori']['IQ'] ?? ($data['hasil_kategori'] ?? 'Rata-rata'));
                $kategori = is_array($rawKategori) ? ($rawKategori['IQ'] ?? implode(', ', array_values($rawKategori))) : (string) $rawKategori;
                $rawIq = $data['iq'] ?? $data['index_kecerdasan_umum'] ?? '100';
                $iq = is_array($rawIq) ? ($rawIq['iq'] ?? ($rawIq['nilai'] ?? '100')) : (string) $rawIq;

                $rawSub = $data['label_values'] ?? $data['hasil_sub'] ?? $data['hasil_ist'] ?? [];
                $enrichedSub = [];

                if (in_array($tr->test_code, ['A.1', 'A.2'])) {
                    foreach ($rawSub as $subKey => $subVal) {
                        $meta = self::$cfitSubtestMeta[$subKey] ?? [
                            'code' => strtoupper($subKey),
                            'name' => 'Subtes '.strtoupper($subKey),
                            'desc' => 'Subkomponen kecerdasan kognitif',
                        ];
                        $valScore = is_array($subVal) ? ($subVal['nilai'] ?? ($subVal['persentase'] ?? 0)) : $subVal;
                        $enrichedSub[$subKey] = [
                            'code' => $meta['code'],
                            'name' => $meta['name'],
                            'desc' => $meta['desc'],
                            'score' => $valScore,
                            'rating' => is_array($subVal) ? ($subVal['rating'] ?? null) : null,
                            'deskripsi' => is_array($subVal) ? ($subVal['deskripsi'] ?? null) : null,
                        ];
                    }
                } else {
                    // IST (A.5)
                    foreach ($rawSub as $subKey => $subVal) {
                        $meta = self::$istSubtestMeta[$subKey] ?? [
                            'code' => $subKey,
                            'name' => "Subtes {$subKey}",
                            'domain' => 'Kognitif',
                            'desc' => 'Dimensi struktur inteligensi IST',
                        ];
                        $valScore = is_array($subVal) ? ($subVal['sw'] ?? ($subVal['nilai'] ?? $subVal['rs'] ?? 0)) : $subVal;
                        $enrichedSub[$subKey] = [
                            'code' => $meta['code'],
                            'name' => $meta['name'],
                            'domain' => $meta['domain'] ?? 'Kognitif',
                            'desc' => $meta['desc'],
                            'score' => $valScore,
                            'kategori' => is_array($subVal) ? ($subVal['kategori'] ?? null) : null,
                        ];
                    }
                }

                $interpretasi = $interp['interpretasi_hasil'] ?? ($data['INTERPRETASI_HASIL'] ?? ($data['interpretasi_hasil'] ?? null));
                $saran = $interp['saran_pengembangan'] ?? ($data['SARAN_PENGEMBANGAN'] ?? ($data['saran_pengembangan'] ?? null));

                return [
                    'iq' => $iq,
                    'kategori' => $kategori,
                    'subtests' => $enrichedSub,
                    'interpretasi' => $interpretasi,
                    'saran' => $saran,
                ];
            })(),
            'B.1', 'D.1' => (function () use ($data, $interp) {
                $rawFactors = ! empty($data['nilaiAspek']) ? $data['nilaiAspek'] : (! empty($data['hasil']) ? $data['hasil'] : array_filter($data, fn ($k) => str_starts_with((string) $k, 'hasil_'), ARRAY_FILTER_USE_KEY));
                $labels = $data['labels_aspek'] ?? [];

                $groupedDomains = [];
                foreach (self::$papiDomainMeta as $domainName => $factors) {
                    $domainList = [];
                    foreach ($factors as $fCode => $fMeta) {
                        $rawKey = "hasil_{$fCode}";
                        $score = $rawFactors[$rawKey] ?? ($rawFactors[$fCode] ?? 0);
                        $domainList[$fCode] = [
                            'code' => $fCode,
                            'name' => $fMeta['name'],
                            'desc' => $fMeta['desc'],
                            'score' => is_array($score) ? ($score['nilai'] ?? 0) : (int) $score,
                        ];
                    }
                    $groupedDomains[$domainName] = $domainList;
                }

                $allNarratives = array_merge($data, $interp);
                $narratives = array_filter($allNarratives, fn ($k) => str_contains((string) $k, '_1') || str_contains((string) $k, '_2') || str_contains((string) $k, '_3') || str_contains((string) $k, '_4'), ARRAY_FILTER_USE_KEY);

                return [
                    'factors' => $rawFactors,
                    'labels' => $labels,
                    'grouped_domains' => $groupedDomains,
                    'narratives' => $narratives,
                ];
            })(),
            'B.2' => (function () use ($data, $interp) {
                $rawSten = $data['standart_final'] ?? $data['nilaiAspek'] ?? $data['nilai'] ?? [];
                $enrichedSten = [];

                if (is_array($rawSten)) {
                    $factorKeys = array_keys(self::$sixteenPfFactorMeta);
                    $isAssoc = ! isset($rawSten[0]);

                    foreach ($factorKeys as $idx => $fCode) {
                        $score = $isAssoc ? ($rawSten[$fCode] ?? 5) : ($rawSten[$idx] ?? 5);
                        $meta = self::$sixteenPfFactorMeta[$fCode] ?? [
                            'name' => "Faktor {$fCode}",
                            'low' => 'Skor rendah',
                            'high' => 'Skor tinggi',
                        ];

                        $scoreNum = (int) $score;
                        $interpretation = $scoreNum >= 8 ? $meta['high'] : ($scoreNum <= 3 ? $meta['low'] : 'Keseimbangan dalam rentang rata-rata.');

                        $enrichedSten[$fCode] = [
                            'code' => $fCode,
                            'name' => $meta['name'],
                            'score' => $scoreNum,
                            'level' => $scoreNum >= 8 ? 'Tinggi' : ($scoreNum <= 3 ? 'Rendah' : 'Rata-rata'),
                            'interpretation' => $interpretation,
                        ];
                    }
                }

                $descriptions = $interp['deskripsi_aspek'] ?? ($data['deskripsi_aspek'] ?? []);

                return [
                    'sten_scores' => $rawSten,
                    'enriched_sten' => $enrichedSten,
                    'md_score' => is_array($data['MDStenScore'] ?? null) ? ($data['MDStenScore']['nilai'] ?? 5) : ($data['MDStenScore'] ?? 5),
                    'descriptions' => $descriptions,
                ];
            })(),
            'D.2' => [
                'pspeed' => $data['kesimpulan_akhir']['panker'] ?? $data['kesimpulan']['panker'] ?? $data['pspeed'] ?? $data['kecepatan'] ?? 0,
                'pacc' => $data['kesimpulan_akhir']['janker_average'] ?? $data['kesimpulan']['janker_average'] ?? $data['pacc'] ?? $data['ketelitian'] ?? 0,
                'pstab' => $data['kesimpulan_akhir']['hanker'] ?? $data['kesimpulan']['hanker'] ?? $data['pstab'] ?? $data['kestabilan'] ?? 0,
                'pstn' => $data['kesimpulan_akhir']['tianker'] ?? $data['kesimpulan']['tianker'] ?? $data['pstn'] ?? $data['ketahanan'] ?? 0,
                'janker_range' => $data['kesimpulan_akhir']['janker_range'] ?? $data['kesimpulan']['janker_range'] ?? 0,
                'pendidikan' => $data['pendidikan'] ?? null,
                'kesimpulan_akhir' => $data['kesimpulan_akhir'] ?? [],
                'interpretasi' => $interp['interpretasi_hasil'] ?? ($data['interpretasi_hasil'] ?? null),
                'saran' => $interp['saran_pengembangan'] ?? ($data['saran_pengembangan'] ?? null),
                'meta' => self::$kraepelinMeta,
            ],
            'F.1' => [
                'eq_score' => $data['skor_akhir'] ?? $data['eq_score'] ?? $data['skor_eq'] ?? 0,
                'kategori' => is_array($data['kategori'] ?? null) ? implode(', ', $data['kategori']) : ($data['kategori'] ?? 'Cukup'),
                'dimensions' => $data['dimensi'] ?? [],
                'final_ratings' => $data['hasil_akhir'] ?? [],
                'aspects' => $data['labels_aspek'] ?? $data['aspek'] ?? [],
                'interpretasi' => $interp['interpretasi_hasil'] ?? ($data['interpretasi_hasil'] ?? null),
                'saran' => $interp['saran_pengembangan'] ?? ($data['saran_pengembangan'] ?? null),
            ],
            'G.1' => (function () use ($data, $interp) {
                $rawTipe = $data['hasil_kecenderungan'] ?? ($data['tipe'] ?? 'Perilaku');
                $tipe = is_array($rawTipe) ? ($rawTipe['tipe'] ?? implode(', ', $rawTipe)) : (string) $rawTipe;

                return [
                    'tipe' => $tipe,
                    'iman' => $data['iman'] ?? null,
                    'pikiran' => $data['pikiran'] ?? null,
                    'perasaan' => $data['perasaan'] ?? null,
                    'interpretasi' => $interp['interpretasi_kebiasaan'] ?? ($data['interpretasi_kebiasaan'] ?? null),
                    'most' => $data['most'] ?? [],
                    'least' => $data['least'] ?? [],
                    'change' => $data['change'] ?? [],
                ];
            })(),
            'H.1' => [
                'top_interests' => array_filter([$data['nilai_1'] ?? null, $data['nilai_2'] ?? null, $data['nilai_3'] ?? null]),
                'scores' => is_array($data['nilai'] ?? null) ? implode(', ', $data['nilai']) : ($data['nilai'] ?? null),
                'interpretasi' => $interp['interpretasi_hasil'] ?? ($data['interpretasi_hasil'] ?? null),
            ],
            default => [
                'raw_payload' => $data,
            ],
        };
    }

    /**
     * Hitung norma IST secara langsung dari raw score string.
     */
    public function evaluateIstNorms(?string $rawIst, string $pendidikan = 'S1', int $usia = 25): array
    {
        return $this->normEngine->processIstNorms($rawIst, $pendidikan, $usia);
    }

    /**
     * Hitung norma PAPI Kostik secara langsung dari raw score string.
     */
    public function evaluateKostikNorms(?string $rawKostik): array
    {
        return $this->normEngine->processKostikNorms($rawKostik);
    }

    /**
     * Hitung norma 16PF secara langsung dari raw score string.
     */
    public function evaluate16pfNorms(?string $rawPersonality, int $usia = 25): array
    {
        return $this->normEngine->process16pfNorms($rawPersonality, $usia);
    }
}
