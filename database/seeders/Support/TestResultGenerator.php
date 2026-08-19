<?php

declare(strict_types=1);

namespace Database\Seeders\Support;

use App\Models\AssessmentEvent;
use App\Models\Participant;
use App\Models\TestResult;
use DateTimeInterface;

/**
 * TestResultGenerator - Generator Riwayat Hasil Tes Psikometri Otentik
 *
 * Menghasilkan riwayat alat tes (CFIT, IST, 16PF, PAPI Kostik, Kraepelin, EQ, Behavior, RMIB)
 * dengan struktur data otentik sesuai response API resmi psikotes.qhrmi.id.
 */
class TestResultGenerator
{
    /**
     * Format assessment_date safely into standard MySQL datetime string: 'Y-m-d H:i:s'.
     */
    private static function formatTestDateTime(mixed $date, string $defaultTime): string
    {
        if (empty($date)) {
            return now()->format('Y-m-d').' '.$defaultTime;
        }

        if ($date instanceof DateTimeInterface) {
            return $date->format('Y-m-d').' '.$defaultTime;
        }

        $dateStr = substr((string) $date, 0, 10);

        return $dateStr.' '.$defaultTime;
    }

    /**
     * Generate seluruh data test_results untuk 1 peserta.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function generateForParticipant(
        Participant $participant,
        AssessmentEvent $event,
        string $boxCategory
    ): array {
        $now = now();
        $isLegacy = $event->code === 'PR-A-313' || str_starts_with($event->code, 'PR-A-30') || str_starts_with($event->code, 'PR-A-31');

        $performanceLevel = match ($boxCategory) {
            'K-7', 'K-8', 'K-9' => 'high',
            'K-1', 'K-3' => 'low',
            default => 'medium',
        };

        if ($isLegacy) {
            return self::generateLegacyTestResults($participant, $event, $performanceLevel, $now);
        }

        return self::generateApiOnlineTestResults($participant, $event, $performanceLevel, $now);
    }

    /**
     * Generate riwayat tes untuk Proyek Legacy (Jalur A - Standar IST, PAPI, 16PF).
     */
    private static function generateLegacyTestResults(
        Participant $participant,
        AssessmentEvent $event,
        string $performanceLevel,
        $now
    ): array {
        $source = 'api';
        $results = [];

        // 1. IST (A.5)
        [$iq, $kategoriIq] = match ($performanceLevel) {
            'high' => [fake()->numberBetween(115, 128), 'Rata-rata Atas'],
            'medium' => [fake()->numberBetween(96, 114), 'Rata-rata'],
            'low' => [fake()->numberBetween(82, 95), 'Rata-rata Bawah'],
        };
        $labelValues = [
            'SE' => max(70, min(130, (int) round($iq * 0.95) + fake()->numberBetween(-3, 5))),
            'WA' => max(70, min(130, (int) round($iq * 1.02) + fake()->numberBetween(-3, 4))),
            'AN' => max(70, min(130, (int) round($iq * 0.92) + fake()->numberBetween(-4, 3))),
            'GE' => max(70, min(130, (int) round($iq * 0.98) + fake()->numberBetween(-2, 4))),
            'ME' => max(70, min(130, (int) round($iq * 1.08) + fake()->numberBetween(-1, 5))),
            'RA' => max(70, min(130, (int) round($iq * 0.91) + fake()->numberBetween(-4, 3))),
            'ZR' => max(70, min(130, (int) round($iq * 0.89) + fake()->numberBetween(-3, 4))),
            'FA' => max(70, min(130, (int) round($iq * 1.01) + fake()->numberBetween(-2, 3))),
            'WU' => max(70, min(130, (int) round($iq * 0.88) + fake()->numberBetween(-4, 2))),
        ];
        $istTime = self::formatTestDateTime($participant->assessment_date, '08:30:00');
        $istInterp = [
            'interpretasi_hasil' => [
                'Kecerdasan Umum' => "Kapasitas struktur inteligensi umum berada pada kategori {$kategoriIq}, mencerminkan kemampuan pemecahan masalah kognitif yang memadai.",
                'Verbal & Bahasa (SE, WA, AN, GE)' => 'Kemampuan memahami logika bahasa, kosakata, pembentukan konsep, dan penalaran analogis berfungsi secara optimal.',
                'Numerik & Berhitung (RA, ZR)' => 'Daya nalar matematis dan ketajaman logika deret angka menunjukkan cara berpikir kuantitatif yang runtut.',
                'Spasial & Ruang (FA, WU)' => 'Daya imajinasi konstruksi ruang dan visualisasi pola dua/tiga dimensi berada pada taraf proporsional.',
                'Daya Ingat (ME)' => 'Kemampuan retensi memori dan konsentrasi terhadap informasi verbal/faktual tergolong baik.',
            ],
            'saran_pengembangan' => [
                'Tingkatkan eksplorasi analisis data numerik untuk mempertajam akurasi perkiraan perencanaan anggaran/proyek.',
                'Perbanyak diskusi konseptual untuk memperkaya perbendaharaan solusi dalam mengatasi permasalahan strategis.',
            ],
        ];
        $istRaw = [
            'status' => true,
            'hasil_ist' => implode(',', array_values($labelValues)),
            'kategori' => 4,
            'rs' => (int) round($iq * 0.75),
            'index' => 7,
            'umur' => '26-30',
            'iq' => (string) $iq,
            'hasil_kategori' => $kategoriIq,
            'label_values' => $labelValues,
            'nama_alat_tes' => 'Typical IST',
        ];

        $results[] = [
            'participant_id' => $participant->id,
            'event_id' => $event->id,
            'test_code' => 'A.5',
            'test_name' => 'Typical IST',
            'test_category' => TestResult::getCategoryForCode('A.5'),
            'status' => 'completed',
            'source' => $source,
            'test_started_at' => $istTime,
            'summary_data' => json_encode([
                'iq' => $iq,
                'rs' => (int) round($iq * 0.75),
                'index' => 7,
                'kategori' => $kategoriIq,
                'hasil_kategori' => $kategoriIq,
                'umur' => '26-30',
                'hasil_ist' => implode(',', array_values($labelValues)),
                'label_values' => $labelValues,
            ]),
            'interpretation_data' => json_encode($istInterp),
            'raw_response' => json_encode($istRaw),
            'conversion_status' => 'converted',
            'converted_at' => $now,
            'created_at' => $now,
            'updated_at' => $now,
        ];

        // 2. PAPI Kostik (D.1)
        $kBase = match ($performanceLevel) {
            'high' => 6,
            'medium' => 4,
            'low' => 2,
        };
        $kostikFactors = [
            'hasil_G' => max(1, min(9, $kBase + fake()->numberBetween(-1, 2))),
            'hasil_L' => max(1, min(9, $kBase + fake()->numberBetween(-1, 2))),
            'hasil_I' => max(1, min(9, $kBase + fake()->numberBetween(-2, 1))),
            'hasil_T' => max(1, min(9, $kBase + fake()->numberBetween(-1, 2))),
            'hasil_V' => max(1, min(9, $kBase + fake()->numberBetween(-1, 1))),
            'hasil_S' => max(1, min(9, $kBase + fake()->numberBetween(0, 2))),
            'hasil_R' => max(1, min(9, $kBase + fake()->numberBetween(-2, 1))),
            'hasil_D' => max(1, min(9, $kBase + fake()->numberBetween(-1, 2))),
            'hasil_C' => max(1, min(9, $kBase + fake()->numberBetween(-1, 1))),
            'hasil_E' => max(1, min(9, $kBase + fake()->numberBetween(-1, 2))),
            'hasil_N' => max(1, min(9, $kBase + fake()->numberBetween(-1, 1))),
            'hasil_A' => max(1, min(9, $kBase + fake()->numberBetween(0, 2))),
            'hasil_P' => max(1, min(9, $kBase + fake()->numberBetween(-1, 2))),
            'hasil_X' => max(1, min(9, $kBase + fake()->numberBetween(-1, 1))),
            'hasil_B' => max(1, min(9, $kBase + fake()->numberBetween(-2, 1))),
            'hasil_O' => max(1, min(9, $kBase + fake()->numberBetween(0, 2))),
            'hasil_Z' => max(1, min(9, $kBase + fake()->numberBetween(-1, 1))),
            'hasil_K' => max(1, min(9, $kBase + fake()->numberBetween(-1, 2))),
            'hasil_F' => max(1, min(9, $kBase + fake()->numberBetween(-2, 1))),
            'hasil_W' => max(1, min(9, $kBase + fake()->numberBetween(0, 2))),
        ];
        $labelsAspek = [
            'hasil_N' => 'Ketekunan', 'hasil_G' => 'Keinginan Bekerja Keras', 'hasil_A' => 'Semangat Berprestasi',
            'hasil_L' => 'Kepemimpinan', 'hasil_P' => 'Mengelola Orang Lain', 'hasil_I' => 'Pengambilan Keputusan',
            'hasil_T' => 'Kecekatan', 'hasil_V' => 'Semangat Kerja', 'hasil_X' => 'Kepercayaan Diri',
            'hasil_S' => 'Interaksi Sosial', 'hasil_B' => 'Kerjasama', 'hasil_O' => 'Hubungan Hangat dan Akrab',
            'hasil_C' => 'Sistematika Kerja', 'hasil_D' => 'Ketelitian', 'hasil_R' => 'Kemampuan Berpikir Analitis',
            'hasil_F' => 'Loyalitas', 'hasil_W' => 'Kepatuhan', 'hasil_Z' => 'Toleransi terhadap Perubahan',
            'hasil_E' => 'Mengelola Emosi', 'hasil_K' => 'Asertifitas',
        ];
        $papiNarratives = [
            'arah_kerja_1' => 'Menunjukkan semangat kerja dan komitmen tinggi dalam menuntaskan target operasional.',
            'arah_kerja_2' => 'Memiliki dorongan berprestasi yang kuat untuk mencapai standar mutu dan hasil yang prima.',
            'gaya_kerja_1' => 'Menyeimbangkan aspek praktikal dan keteraturan kerja secara sistematis dan terstruktur.',
            'gaya_kerja_2' => 'Cermat dan memperhatikan detail pekerjaan dengan tingkat kehati-hatian yang memadai.',
            'activity_1' => 'Dapat menyesuaikan kecepatan ritme kerja sesuai kebutuhan dan dinamika beban tugas.',
            'activity_2' => 'Nyaman bekerja dengan fokus operasional terarah dan alur kerja yang jelas.',
            'followership_1' => 'Menunjukkan loyalitas, integritas, dan kepatuhan tinggi terhadap arahan pimpinan serta regulasi.',
            'followership_2' => 'Mampu menyelaraskan inisiatif personal dengan kebijakan dan SOP institusi secara disiplin.',
            'leadership_1' => 'Mampu memimpin, mengoordinasikan tim, dan membagi tanggung jawab kerja secara proporsional.',
            'leadership_2' => 'Memiliki ketegasan dalam mengawal pencapaian sasaran tim dan mengarahkan bawahan.',
            'social_1' => 'Mampu menciptakan suasana kerja yang hangat, bersahabat, dan kooperatif dengan rekan kerja.',
            'social_2' => 'Mudah beradaptasi dalam menjalin komunikasi aktif dalam tim lintas bidang.',
            'temprament_1' => 'Mampu mengelola emosi dengan matang dan tetap tenang di bawah situasi tekanan kerja.',
            'temprament_2' => 'Menyelesaikan silang pendapat dengan pendekatan musyawarah yang konstruktif dan solutif.',
        ];
        $papiTime = self::formatTestDateTime($participant->assessment_date, '10:00:00');
        $papiRaw = array_merge(
            ['status' => true, 'mulai_tes' => $papiTime],
            $kostikFactors,
            $papiNarratives,
            ['labels_aspek' => $labelsAspek, 'nama_alat_tes' => 'PAPI Kostik']
        );

        $results[] = [
            'participant_id' => $participant->id,
            'event_id' => $event->id,
            'test_code' => 'D.1',
            'test_name' => 'PAPI Kostik',
            'test_category' => TestResult::getCategoryForCode('B.1'),
            'status' => 'completed',
            'source' => $source,
            'test_started_at' => $papiTime,
            'summary_data' => json_encode([
                'hasil' => $kostikFactors,
                'nilaiAspek' => $kostikFactors,
                'labels_aspek' => $labelsAspek,
            ]),
            'interpretation_data' => json_encode($papiNarratives),
            'raw_response' => json_encode($papiRaw),
            'conversion_status' => 'converted',
            'converted_at' => $now,
            'created_at' => $now,
            'updated_at' => $now,
        ];

        // 3. 16PF (B.2)
        $stenBase = match ($performanceLevel) {
            'high' => 7,
            'medium' => 5,
            'low' => 4,
        };
        $sten16pf = [
            'A' => max(1, min(10, $stenBase + fake()->numberBetween(-1, 2))),
            'B' => max(1, min(10, $stenBase + fake()->numberBetween(-1, 1))),
            'C' => max(1, min(10, $stenBase + fake()->numberBetween(0, 2))),
            'E' => max(1, min(10, $stenBase + fake()->numberBetween(-1, 1))),
            'F' => max(1, min(10, $stenBase + fake()->numberBetween(-2, 1))),
            'G' => max(1, min(10, $stenBase + fake()->numberBetween(0, 2))),
            'H' => max(1, min(10, $stenBase + fake()->numberBetween(-1, 1))),
            'I' => max(1, min(10, $stenBase + fake()->numberBetween(-1, 2))),
            'L' => max(1, min(10, $stenBase + fake()->numberBetween(-2, 1))),
            'M' => max(1, min(10, $stenBase + fake()->numberBetween(-1, 1))),
            'N' => max(1, min(10, $stenBase + fake()->numberBetween(0, 2))),
            'O' => max(1, min(10, $stenBase + fake()->numberBetween(-1, 1))),
            'Q1' => max(1, min(10, $stenBase + fake()->numberBetween(-1, 2))),
            'Q2' => max(1, min(10, $stenBase + fake()->numberBetween(-1, 1))),
            'Q3' => max(1, min(10, $stenBase + fake()->numberBetween(0, 2))),
            'Q4' => max(1, min(10, $stenBase + fake()->numberBetween(-2, 1))),
        ];
        $stenValues = array_values($sten16pf);
        $deskripsi16pf = [
            'deskripsi_aspek' => [
                'A' => ['nama' => 'Warmth (Kehangatan)', 'rendah' => 'Berhati-hati, pendiam, suka menyendiri, kritis.', 'tinggi' => 'Ramah tamah, hangat, mudah berpartisipasi sosial.'],
                'B' => ['nama' => 'Reasoning (Penalaran)', 'rendah' => 'Pemikiran konkret, kapasitas mental skolastik sederhana.', 'tinggi' => 'Pandai, cerdas, kapasitas penalaran abstrak tinggi.'],
                'C' => ['nama' => 'Emotional Stability', 'rendah' => 'Reaktif, emosi kurang mantap, rentan stres.', 'tinggi' => 'Emosi mantap, matang, tenang menghadapi realitas.'],
                'E' => ['nama' => 'Dominance', 'rendah' => 'Kooperatif, patuh, menghindari konfrontasi.', 'tinggi' => 'Asertif, kompetitif, percaya diri tinggi, dominan.'],
                'F' => ['nama' => 'Liveliness', 'rendah' => 'Serius, berhati-hati, tenang, terencana.', 'tinggi' => 'Antusias, spontan, ekspresif, ceria.'],
                'G' => ['nama' => 'Rule-Consciousness', 'rendah' => 'Fleksibel, bebas aturan baku, mengabaikan aturan.', 'tinggi' => 'Teliti, gigih, tekun, taat aturan, superego kuat.'],
                'H' => ['nama' => 'Social Boldness', 'rendah' => 'Pemalu, peka terhadap ancaman sosial, berhati-hati.', 'tinggi' => 'Berani mengambil inisiatif sosial, tegas, percaya diri.'],
                'I' => ['nama' => 'Sensitivity', 'rendah' => 'Keras hati, praktis, mengutamakan logika faktual.', 'tinggi' => 'Sensitif, intuitif, berempati tinggi, lembut hati.'],
                'L' => ['nama' => 'Vigilance', 'rendah' => 'Mudah percaya, menerima keadaan orang lain apa adanya.', 'tinggi' => 'Waspada, berhati-hati, kritis terhadap motif tersembunyi.'],
                'M' => ['nama' => 'Abstractedness', 'rendah' => 'Praktikal, mengutamakan hal sederhana dan konkret.', 'tinggi' => 'Imajinatif, konseptual, berorientasi ide dan visi.'],
                'N' => ['nama' => 'Privateness', 'rendah' => 'Jujur, berterus terang, blak-blakan, polos.', 'tinggi' => 'Diplomatis, cermat menjaga privasi, cerdik.'],
                'O' => ['nama' => 'Apprehension', 'rendah' => 'Yakin akan diri sendiri, tenang, tidak mudah cemas.', 'tinggi' => 'Rentan khawatir, gelisah, kritis terhadap diri sendiri.'],
                'Q1' => ['nama' => 'Openness to Change', 'rendah' => 'Konservatif, tradisional, nyaman dengan pola mapan.', 'tinggi' => 'Liberal, terbuka pada inovasi, berpikir bebas dan radikal.'],
                'Q2' => ['nama' => 'Self-Reliance', 'rendah' => 'Tergantung pada kelompok, butuh dukungan rekan.', 'tinggi' => 'Mandiri, mengambil keputusan sendiri, soliter.'],
                'Q3' => ['nama' => 'Perfectionism', 'rendah' => 'Toleran pada ketidakteraturan, santai.', 'tinggi' => 'Disiplin diri tinggi, teratur, mengutamakan kesempurnaan.'],
                'Q4' => ['nama' => 'Tension', 'rendah' => 'Santai, tenang, penyabar, ketegangan energi rendah.', 'tinggi' => 'Tegang, dinamis, mudah terstimulasi, dorongan energi tinggi.'],
            ],
        ];
        $pfTime = self::formatTestDateTime($participant->assessment_date, '11:15:00');
        $pfRaw = [
            'status' => true,
            'kode' => '16PF',
            'kategori' => 'Normal',
            'standart_final' => $stenValues,
            'aspekList' => array_keys($sten16pf),
            'deskripsi_aspek' => $deskripsi16pf['deskripsi_aspek'],
            'nilai' => $stenValues,
            'MDStenScore' => fake()->numberBetween(5, 7),
            'WS' => 10,
            'nilaiAspek' => $sten16pf,
            'nama_alat_tes' => 'Typical 16PF',
        ];

        $results[] = [
            'participant_id' => $participant->id,
            'event_id' => $event->id,
            'test_code' => 'B.2',
            'test_name' => 'Typical 16PF',
            'test_category' => TestResult::getCategoryForCode('B.2'),
            'status' => 'completed',
            'source' => $source,
            'test_started_at' => $pfTime,
            'summary_data' => json_encode([
                'kode' => '16PF',
                'kategori' => 'Normal',
                'standart_final' => $stenValues,
                'aspekList' => array_keys($sten16pf),
                'nilaiAspek' => $sten16pf,
                'MDStenScore' => fake()->numberBetween(5, 7),
                'nilai' => $stenValues,
                'WS' => 10,
            ]),
            'interpretation_data' => json_encode($deskripsi16pf),
            'raw_response' => json_encode($pfRaw),
            'conversion_status' => 'converted',
            'converted_at' => $now,
            'created_at' => $now,
            'updated_at' => $now,
        ];

        return $results;
    }

    /**
     * Generate riwayat tes untuk Proyek REST API Online (Jalur B ≥ PR-A-338).
     */
    private static function generateApiOnlineTestResults(
        Participant $participant,
        AssessmentEvent $event,
        string $performanceLevel,
        $now
    ): array {
        $source = 'api';
        $results = [];

        // 1. CFIT 3A (A.1)
        [$iq, $kategoriIq] = match ($performanceLevel) {
            'high' => [fake()->numberBetween(115, 130), 'Istimewa'],
            'medium' => [fake()->numberBetween(95, 114), 'Rata-rata'],
            'low' => [fake()->numberBetween(70, 94), 'Borderline'],
        };
        $sub1Rating = match ($performanceLevel) {
            'high' => 4, 'medium' => 3, 'low' => 2
        };
        $sub2Rating = match ($performanceLevel) {
            'high' => 4, 'medium' => 3, 'low' => 1
        };
        $sub3Rating = match ($performanceLevel) {
            'high' => 5, 'medium' => 3, 'low' => 2
        };
        $sub4Rating = match ($performanceLevel) {
            'high' => 4, 'medium' => 2, 'low' => 1
        };

        $hasilSub = [
            'sub1' => ['nilai' => $sub1Rating * 3, 'total_soal' => 13, 'persentase' => round(($sub1Rating / 5) * 100, 1), 'rating' => $sub1Rating, 'deskripsi' => $sub1Rating >= 4 ? 'Baik' : ($sub1Rating >= 3 ? 'Cukup' : 'Kurang')],
            'sub2' => ['nilai' => $sub2Rating * 3, 'total_soal' => 14, 'persentase' => round(($sub2Rating / 5) * 100, 1), 'rating' => $sub2Rating, 'deskripsi' => $sub2Rating >= 4 ? 'Baik' : ($sub2Rating >= 3 ? 'Cukup' : 'Kurang')],
            'sub3' => ['nilai' => $sub3Rating * 2, 'total_soal' => 13, 'persentase' => round(($sub3Rating / 5) * 100, 1), 'rating' => $sub3Rating, 'deskripsi' => $sub3Rating >= 4 ? 'Baik' : ($sub3Rating >= 3 ? 'Cukup' : 'Kurang')],
            'sub4' => ['nilai' => $sub4Rating * 2, 'total_soal' => 10, 'persentase' => round(($sub4Rating / 5) * 100, 1), 'rating' => $sub4Rating, 'deskripsi' => $sub4Rating >= 4 ? 'Baik' : ($sub4Rating >= 3 ? 'Cukup' : 'Kurang')],
        ];

        $cfitInterp = [
            'interpretasi_hasil' => [
                'Kecerdasan Umum' => $kategoriIq === 'Istimewa' || $kategoriIq === 'Sangat Superior'
                    ? 'Individu memiliki kemampuan abstraksi yang sangat luar biasa dan kecepatan berpikir yang sangat tinggi dalam mengintegrasikan berbagai informasi kompleks.'
                    : ($kategoriIq === 'Rata-rata'
                        ? 'Individu memiliki kemampuan intelektual yang cukup memadai dalam menyelesaikan tugas kerja umum dan memecahkan persoalan operasional standar.'
                        : 'Kapasitas inteligensi umum berada pada rentang batas bawah, memerlukan pendampingan pada tugas-tugas konseptual kompleks.'),
                'Subtes 1 (Series/Serial Reasoning)' => 'Individu memiliki logika berpikir yang runtut dan sistematis dalam menghadapi persoalan yang dinamis.',
                'Subtes 2 (Classification/Grouping)' => 'Individu cukup teliti dalam melihat perbedaan esensial dan mengklasifikasikan pola data.',
                'Subtes 3 (Matrices/Analisis Visual)' => 'Mampu melakukan analisis yang mendalam terhadap sebuah situasi dan menyimpulkan hubungan antar bagian dengan tepat.',
                'Subtes 4 (Conditions/Topologi)' => 'Memiliki pemahaman yang kuat terhadap simbol-simbol dan logika yang bersifat teoritis atau kondisional.',
            ],
            'saran_pengembangan' => [
                'Banyak membaca buku analisis atau mengisi TTS/Sudoku untuk membiasakan otak bekerja dengan pola terstruktur.',
                'Saat melihat stimulus kompleks, fokus pada satu elemen terlebih dahulu untuk mengenali arah pergerakan.',
                'Berlatih mengelompokkan objek berdasarkan ciri fisik secara cepat dan akurat.',
                'Bermain catur atau board games berbasis strategi untuk melatih kemampuan prediksi langkah.',
                'Berlatih melihat denah atau peta untuk melatih orientasi ruang dan topologi objek.',
            ],
        ];

        $cfitTime = self::formatTestDateTime($participant->assessment_date, '08:15:00');
        $cfitRaw = [
            'status' => true,
            'mulai_tes' => $cfitTime,
            'total' => (int) round($iq / 4),
            'hasil_sub' => $hasilSub,
            'iq' => $iq,
            'kategori' => $kategoriIq,
            'umur_format' => '24_0',
            'index_umur' => 'ge17',
            'index_kecerdasan_umum' => $sub1Rating,
            'versi' => 1,
            'INTERPRETASI_HASIL' => $cfitInterp['interpretasi_hasil'],
            'SARAN_PENGEMBANGAN' => $cfitInterp['saran_pengembangan'],
            'umur_asli' => '24 tahun, 0 bulan',
            'nama_alat_tes' => 'Typical CFIT3A',
        ];

        $results[] = [
            'participant_id' => $participant->id,
            'event_id' => $event->id,
            'test_code' => 'A.1',
            'test_name' => 'Typical CFIT3A',
            'test_category' => TestResult::getCategoryForCode('A.1'),
            'status' => 'completed',
            'source' => $source,
            'test_started_at' => $cfitTime,
            'summary_data' => json_encode([
                'iq' => $iq,
                'kategori' => $kategoriIq,
                'total' => (int) round($iq / 4),
                'index_kecerdasan_umum' => $sub1Rating,
                'umur_format' => '24_0',
                'index_umur' => 'ge17',
                'umur_asli' => '24 tahun, 0 bulan',
                'versi' => 1,
                'hasil_sub' => $hasilSub,
            ]),
            'interpretation_data' => json_encode($cfitInterp),
            'raw_response' => json_encode($cfitRaw),
            'conversion_status' => 'converted',
            'converted_at' => $now,
            'created_at' => $now,
            'updated_at' => $now,
        ];

        // 2. KOMPETENSI KARAKTER (B.1)
        $fVal = match ($performanceLevel) {
            'high' => 4, 'medium' => 3, 'low' => 2
        };
        $papiScores = [
            'hasil_A' => max(1, min(5, $fVal + fake()->numberBetween(-1, 1))),
            'hasil_G' => max(1, min(5, $fVal + fake()->numberBetween(0, 1))),
            'hasil_N' => max(1, min(5, $fVal + fake()->numberBetween(-1, 1))),
            'hasil_R' => max(1, min(5, $fVal + fake()->numberBetween(-1, 1))),
            'hasil_C' => max(1, min(5, $fVal + fake()->numberBetween(0, 1))),
            'hasil_D' => max(1, min(5, $fVal + fake()->numberBetween(-1, 1))),
            'hasil_T' => max(1, min(5, $fVal + fake()->numberBetween(0, 1))),
            'hasil_V' => max(1, min(5, $fVal + fake()->numberBetween(-1, 1))),
            'hasil_F' => max(1, min(5, $fVal + fake()->numberBetween(-1, 1))),
            'hasil_W' => max(1, min(5, $fVal + fake()->numberBetween(0, 1))),
            'hasil_L' => max(1, min(5, $fVal + fake()->numberBetween(-1, 1))),
            'hasil_P' => max(1, min(5, $fVal + fake()->numberBetween(0, 1))),
            'hasil_I' => max(1, min(5, $fVal + fake()->numberBetween(-1, 1))),
            'hasil_S' => max(1, min(5, $fVal + fake()->numberBetween(0, 1))),
            'hasil_O' => max(1, min(5, $fVal + fake()->numberBetween(-1, 1))),
            'hasil_B' => max(1, min(5, $fVal + fake()->numberBetween(-1, 1))),
            'hasil_X' => max(1, min(5, $fVal + fake()->numberBetween(0, 1))),
            'hasil_E' => max(1, min(5, $fVal + fake()->numberBetween(-1, 1))),
            'hasil_K' => max(1, min(5, $fVal + fake()->numberBetween(0, 1))),
            'hasil_Z' => max(1, min(5, $fVal + fake()->numberBetween(-1, 1))),
        ];
        $labelsAspek = [
            'hasil_N' => 'Ketekunan', 'hasil_G' => 'Keinginan Bekerja Keras', 'hasil_A' => 'Semangat Berprestasi',
            'hasil_L' => 'Kepemimpinan', 'hasil_P' => 'Mengelola Orang Lain', 'hasil_I' => 'Pengambilan Keputusan',
            'hasil_T' => 'Kecekatan', 'hasil_V' => 'Semangat Kerja', 'hasil_X' => 'Kepercayaan Diri',
            'hasil_S' => 'Interaksi Sosial', 'hasil_B' => 'Kerjasama', 'hasil_O' => 'Hubungan Hangat dan Akrab',
            'hasil_C' => 'Sistematika Kerja', 'hasil_D' => 'Ketelitian', 'hasil_R' => 'Kemampuan Berpikir Analitis',
            'hasil_F' => 'Loyalitas', 'hasil_W' => 'Kepatuhan', 'hasil_Z' => 'Toleransi terhadap Perubahan',
            'hasil_E' => 'Mengelola Emosi', 'hasil_K' => 'Asertifitas',
        ];
        $papiNarratives = [
            'arah_kerja_1' => 'Menunjukkan semangat kerja dan komitmen tinggi dalam menuntaskan target operasional.',
            'arah_kerja_2' => 'Memiliki dorongan berprestasi yang kuat untuk mencapai standar mutu dan hasil yang prima.',
            'gaya_kerja_1' => 'Menyeimbangkan aspek praktikal dan keteraturan kerja secara sistematis dan terstruktur.',
            'gaya_kerja_2' => 'Cermat dan memperhatikan detail pekerjaan dengan tingkat kehati-hatian yang memadai.',
            'activity_1' => 'Dapat menyesuaikan kecepatan ritme kerja sesuai kebutuhan dan dinamika beban tugas.',
            'activity_2' => 'Nyaman bekerja dengan fokus operasional terarah dan alur kerja yang jelas.',
            'followership_1' => 'Menunjukkan loyalitas, integritas, dan kepatuhan tinggi terhadap arahan pimpinan serta regulasi.',
            'followership_2' => 'Mampu menyelaraskan inisiatif personal dengan kebijakan dan SOP institusi secara disiplin.',
            'leadership_1' => 'Mampu memimpin, mengoordinasikan tim, dan membagi tanggung jawab kerja secara proporsional.',
            'leadership_2' => 'Memiliki ketegasan dalam mengawal pencapaian sasaran tim dan mengarahkan bawahan.',
            'social_1' => 'Mampu menciptakan suasana kerja yang hangat, bersahabat, dan kooperatif dengan rekan kerja.',
            'social_2' => 'Mudah beradaptasi dalam menjalin komunikasi aktif dalam tim lintas bidang.',
            'temprament_1' => 'Mampu mengelola emosi dengan matang dan tetap tenang di bawah situasi tekanan kerja.',
            'temprament_2' => 'Menyelesaikan silang pendapat dengan pendekatan musyawarah yang konstruktif dan solutif.',
        ];
        $papiApiTime = self::formatTestDateTime($participant->assessment_date, '09:45:00');
        $papiApiRaw = array_merge(
            ['status' => true, 'mulai_tes' => $papiApiTime],
            $papiScores,
            $papiNarratives,
            ['labels_aspek' => $labelsAspek, 'nama_alat_tes' => 'KOMPETENSI KARAKTER']
        );

        $results[] = [
            'participant_id' => $participant->id,
            'event_id' => $event->id,
            'test_code' => 'B.1',
            'test_name' => 'KOMPETENSI KARAKTER',
            'test_category' => TestResult::getCategoryForCode('B.1'),
            'status' => 'completed',
            'source' => $source,
            'test_started_at' => $papiApiTime,
            'summary_data' => json_encode([
                'hasil' => $papiScores,
                'labels_aspek' => $labelsAspek,
            ]),
            'interpretation_data' => json_encode($papiNarratives),
            'raw_response' => json_encode($papiApiRaw),
            'conversion_status' => 'converted',
            'converted_at' => $now,
            'created_at' => $now,
            'updated_at' => $now,
        ];

        // 3. 16PF (B.2)
        $stenBase = match ($performanceLevel) {
            'high' => 7, 'medium' => 5, 'low' => 4
        };
        $sten16pf = [
            'A' => max(1, min(10, $stenBase + fake()->numberBetween(-1, 2))),
            'B' => max(1, min(10, $stenBase + fake()->numberBetween(-1, 1))),
            'C' => max(1, min(10, $stenBase + fake()->numberBetween(0, 2))),
            'E' => max(1, min(10, $stenBase + fake()->numberBetween(-1, 1))),
            'F' => max(1, min(10, $stenBase + fake()->numberBetween(-2, 1))),
            'G' => max(1, min(10, $stenBase + fake()->numberBetween(0, 2))),
            'H' => max(1, min(10, $stenBase + fake()->numberBetween(-1, 1))),
            'I' => max(1, min(10, $stenBase + fake()->numberBetween(-1, 2))),
            'L' => max(1, min(10, $stenBase + fake()->numberBetween(-2, 1))),
            'M' => max(1, min(10, $stenBase + fake()->numberBetween(-1, 1))),
            'N' => max(1, min(10, $stenBase + fake()->numberBetween(0, 2))),
            'O' => max(1, min(10, $stenBase + fake()->numberBetween(-1, 1))),
            'Q1' => max(1, min(10, $stenBase + fake()->numberBetween(-1, 2))),
            'Q2' => max(1, min(10, $stenBase + fake()->numberBetween(-1, 1))),
            'Q3' => max(1, min(10, $stenBase + fake()->numberBetween(0, 2))),
            'Q4' => max(1, min(10, $stenBase + fake()->numberBetween(-2, 1))),
        ];
        $stenValues = array_values($sten16pf);
        $deskripsi16pf = [
            'deskripsi_aspek' => [
                'A' => ['nama' => 'Warmth (Kehangatan)', 'rendah' => 'Berhati-hati, pendiam, suka menyendiri, kritis.', 'tinggi' => 'Ramah tamah, hangat, mudah berpartisipasi sosial.'],
                'B' => ['nama' => 'Reasoning (Penalaran)', 'rendah' => 'Pemikiran konkret, kapasitas mental skolastik sederhana.', 'tinggi' => 'Pandai, cerdas, kapasitas penalaran abstrak tinggi.'],
                'C' => ['nama' => 'Emotional Stability', 'rendah' => 'Reaktif, emosi kurang mantap, rentan stres.', 'tinggi' => 'Emosi mantap, matang, tenang menghadapi realitas.'],
                'E' => ['nama' => 'Dominance', 'rendah' => 'Kooperatif, patuh, menghindari konfrontasi.', 'tinggi' => 'Asertif, kompetitif, percaya diri tinggi, dominan.'],
                'F' => ['nama' => 'Liveliness', 'rendah' => 'Serius, berhati-hati, tenang, terencana.', 'tinggi' => 'Antusias, spontan, ekspresif, ceria.'],
                'G' => ['nama' => 'Rule-Consciousness', 'rendah' => 'Fleksibel, bebas aturan baku, mengabaikan aturan.', 'tinggi' => 'Teliti, gigih, tekun, taat aturan, superego kuat.'],
                'H' => ['nama' => 'Social Boldness', 'rendah' => 'Pemalu, peka terhadap ancaman sosial, berhati-hati.', 'tinggi' => 'Berani mengambil inisiatif sosial, tegas, percaya diri.'],
                'I' => ['nama' => 'Sensitivity', 'rendah' => 'Keras hati, praktis, mengutamakan logika faktual.', 'tinggi' => 'Sensitif, intuitif, berempati tinggi, lembut hati.'],
                'L' => ['nama' => 'Vigilance', 'rendah' => 'Mudah percaya, menerima keadaan orang lain apa adanya.', 'tinggi' => 'Waspada, berhati-hati, kritis terhadap motif tersembunyi.'],
                'M' => ['nama' => 'Abstractedness', 'rendah' => 'Praktikal, mengutamakan hal sederhana dan konkret.', 'tinggi' => 'Imajinatif, konseptual, berorientasi ide dan visi.'],
                'N' => ['nama' => 'Privateness', 'rendah' => 'Jujur, berterus terang, blak-blakan, polos.', 'tinggi' => 'Diplomatis, cermat menjaga privasi, cerdik.'],
                'O' => ['nama' => 'Apprehension', 'rendah' => 'Yakin akan diri sendiri, tenang, tidak mudah cemas.', 'tinggi' => 'Rentan khawatir, gelisah, kritis terhadap diri sendiri.'],
                'Q1' => ['nama' => 'Openness to Change', 'rendah' => 'Konservatif, tradisional, nyaman dengan pola mapan.', 'tinggi' => 'Liberal, terbuka pada inovasi, berpikir bebas dan radikal.'],
                'Q2' => ['nama' => 'Self-Reliance', 'rendah' => 'Tergantung pada kelompok, butuh dukungan rekan.', 'tinggi' => 'Mandiri, mengambil keputusan sendiri, soliter.'],
                'Q3' => ['nama' => 'Perfectionism', 'rendah' => 'Toleran pada ketidakteraturan, santai.', 'tinggi' => 'Disiplin diri tinggi, teratur, mengutamakan kesempurnaan.'],
                'Q4' => ['nama' => 'Tension', 'rendah' => 'Santai, tenang, penyabar, ketegangan energi rendah.', 'tinggi' => 'Tegang, dinamis, mudah terstimulasi, dorongan energi tinggi.'],
            ],
        ];
        $pfApiTime = self::formatTestDateTime($participant->assessment_date, '10:30:00');
        $pfApiRaw = [
            'status' => true,
            'kode' => '16PF',
            'kategori' => 'Normal',
            'standart_final' => $stenValues,
            'aspekList' => array_keys($sten16pf),
            'deskripsi_aspek' => $deskripsi16pf['deskripsi_aspek'],
            'nilai' => $stenValues,
            'MDStenScore' => fake()->numberBetween(5, 7),
            'WS' => 10,
            'nilaiAspek' => $sten16pf,
            'nama_alat_tes' => 'Typical 16PF',
        ];

        $results[] = [
            'participant_id' => $participant->id,
            'event_id' => $event->id,
            'test_code' => 'B.2',
            'test_name' => 'Typical 16PF',
            'test_category' => TestResult::getCategoryForCode('B.2'),
            'status' => 'completed',
            'source' => $source,
            'test_started_at' => $pfApiTime,
            'summary_data' => json_encode([
                'kode' => '16PF',
                'kategori' => 'Normal',
                'standart_final' => $stenValues,
                'aspekList' => array_keys($sten16pf),
                'nilaiAspek' => $sten16pf,
                'MDStenScore' => fake()->numberBetween(5, 7),
                'nilai' => $stenValues,
                'WS' => 10,
            ]),
            'interpretation_data' => json_encode($deskripsi16pf),
            'raw_response' => json_encode($pfApiRaw),
            'conversion_status' => 'converted',
            'converted_at' => $now,
            'created_at' => $now,
            'updated_at' => $now,
        ];

        // 4. Kraepelin (D.2)
        [$panker, $janker, $hanker, $tianker] = match ($performanceLevel) {
            'high' => [8.2, 1.4, 3.8, 68],
            'medium' => [6.5, 2.5, 3.0, 55],
            'low' => [4.2, 4.8, 1.8, 38],
        };
        $kRating = match ($performanceLevel) {
            'high' => 4, 'medium' => 3, 'low' => 2
        };
        $jankerRange = fake()->numberBetween(4, 8);
        $kraepelinSummary = [
            'panker' => $panker,
            'janker_average' => $janker,
            'janker_range' => $jankerRange,
            'hanker' => $hanker,
            'tianker' => $tianker,
        ];
        $kraepelinAkhir = [
            'panker' => $kRating,
            'janker_average' => $kRating,
            'janker_range' => 3,
            'hanker' => $kRating,
            'tianker' => $kRating,
        ];
        $kraepelinInterp = [
            'interpretasi_hasil' => [
                'Kecepatan Kerja (PANKER)' => 'Menggambarkan tempo kecepatan output kerja per satuan waktu saat menghadapi beban tugas rutin.',
                'Ketelitian Kerja (JANKER)' => 'Tingkat akurasi kerja tinggi dengan rasio kekeliruan perhitungan yang terkontrol baik.',
                'Kestabilan Kerja (HANKER)' => 'Menunjukkan konsistensi kurva ritme kerja yang stabil tanpa fluktuasi penurunan drastis.',
                'Ketahanan Kerja (TIANKER)' => 'Daya tahan stamina kerja dan fokus mental terpelihara secara prima saat menghadapi kejenuhan.',
            ],
            'saran_pengembangan' => [
                'Pertahankan ritme kerja stabil dengan penerapan manajemen waktu dan jeda istirahat mikro.',
                'Lakukan verifikasi berkala pada tugas-tugas administratif bervolume tinggi guna mencegah kelelahan mental.',
            ],
        ];
        $kraeplinTime = self::formatTestDateTime($participant->assessment_date, '11:15:00');
        $kraepelinRaw = [
            'status' => true,
            'mulai_tes' => $kraeplinTime,
            'skor_b' => 0.0408,
            'skor_a' => 6.4979,
            'skor_X45' => 8.5411,
            'skor_X0' => 6.4979,
            'pendidikan' => $participant->pendidikan ?? 'S1',
            'kesimpulan' => $kraepelinSummary,
            'kesimpulan_SMA' => $kraepelinAkhir,
            'kesimpulan_D3' => $kraepelinAkhir,
            'kesimpulan_S1' => $kraepelinAkhir,
            'kesimpulan_akhir' => $kraepelinAkhir,
            'nama_alat_tes' => 'Typical Kraeplin',
        ];

        $results[] = [
            'participant_id' => $participant->id,
            'event_id' => $event->id,
            'test_code' => 'D.2',
            'test_name' => 'Typical Kraeplin',
            'test_category' => TestResult::getCategoryForCode('D.2'),
            'status' => 'completed',
            'source' => $source,
            'test_started_at' => $kraeplinTime,
            'summary_data' => json_encode([
                'kesimpulan' => $kraepelinSummary,
                'kesimpulan_akhir' => $kraepelinAkhir,
                'pendidikan' => $participant->pendidikan ?? 'S1',
                'skor_b' => 0.0408,
                'skor_a' => 6.4979,
                'skor_X45' => 8.5411,
                'skor_X0' => 6.4979,
            ]),
            'interpretation_data' => json_encode($kraepelinInterp),
            'raw_response' => json_encode($kraepelinRaw),
            'conversion_status' => 'converted',
            'converted_at' => $now,
            'created_at' => $now,
            'updated_at' => $now,
        ];

        // 5. Typical EQ (F.1)
        [$eqScore, $eqCat] = match ($performanceLevel) {
            'high' => [fake()->numberBetween(340, 375), 'Istimewa'],
            'medium' => [fake()->numberBetween(290, 339), 'Tinggi'],
            'low' => [fake()->numberBetween(220, 289), 'Cukup'],
        };
        $dimensiList = [
            '4' => ['nama' => 'Kesadaran Emosi Diri', 'skor' => (int) round($eqScore / 13)],
            '5' => ['nama' => 'Pengungkapan Emosi', 'skor' => (int) round($eqScore / 14)],
            '6' => ['nama' => 'Kesadaran Emosi Orang Lain', 'skor' => (int) round($eqScore / 13)],
            '7' => ['nama' => 'Keluwesan', 'skor' => (int) round($eqScore / 12)],
            '8' => ['nama' => 'Kemandirian', 'skor' => (int) round($eqScore / 14)],
            '9' => ['nama' => 'Penghargaan Diri', 'skor' => (int) round($eqScore / 13)],
            '10' => ['nama' => 'Hubungan Antar Pribadi', 'skor' => (int) round($eqScore / 12)],
            '11' => ['nama' => 'Tanggung Jawab Sosial', 'skor' => (int) round($eqScore / 14)],
            '12' => ['nama' => 'Penyelesaian Masalah', 'skor' => (int) round($eqScore / 12)],
            '13' => ['nama' => 'Uji Realitas', 'skor' => (int) round($eqScore / 11)],
            '14' => ['nama' => 'Pengendalian Dorongan Hati', 'skor' => (int) round($eqScore / 15)],
            '15' => ['nama' => 'Ketahanan Terhadap Stres', 'skor' => (int) round($eqScore / 13)],
            '16' => ['nama' => 'Daya Pribadi', 'skor' => (int) round($eqScore / 12)],
            '17' => ['nama' => 'Integritas', 'skor' => (int) round($eqScore / 15)],
        ];
        $hasilAkhir = [
            '4' => 3, '5' => 3, '6' => 3, '7' => 4, '8' => 3, '9' => 3, '10' => 3,
            '11' => 3, '12' => 4, '13' => 3, '14' => 2, '15' => 3, '16' => 3, '17' => 3,
        ];
        $eqInterp = [
            'interpretasi_hasil' => [
                'Kematangan Emosi' => 'Menunjukkan kesadaran emosional yang baik dalam mengenali kekuatan diri dan mengendalikan impuls.',
                'Hubungan Interpersonal' => 'Mampu membangun empati dan menjalin hubungan kerja yang harmonis dan saling mendukung.',
                'Ketahanan Stres' => 'Memiliki fleksibilitas dan daya adaptasi yang tinggi dalam menghadapi situasi konflik organisasi.',
            ],
            'saran_pengembangan' => [
                'Kembangkan komunikasi asertif secara konsisten dalam menyampaikan gagasan solutif kepada rekan kerja.',
            ],
        ];
        $eqTime = self::formatTestDateTime($participant->assessment_date, '13:00:00');
        $eqRaw = [
            'status' => true,
            'mulai_tes' => $eqTime,
            'skor_akhir' => $eqScore,
            'kategori' => $eqCat,
            'dimensi' => $dimensiList,
            'hasil_akhir' => $hasilAkhir,
            'nama_alat_tes' => 'Typical EQ',
        ];

        $results[] = [
            'participant_id' => $participant->id,
            'event_id' => $event->id,
            'test_code' => 'F.1',
            'test_name' => 'Typical EQ',
            'test_category' => TestResult::getCategoryForCode('F.1'),
            'status' => 'completed',
            'source' => $source,
            'test_started_at' => $eqTime,
            'summary_data' => json_encode([
                'skor_akhir' => $eqScore,
                'kategori' => $eqCat,
                'dimensi' => $dimensiList,
                'hasil_akhir' => $hasilAkhir,
            ]),
            'interpretation_data' => json_encode($eqInterp),
            'raw_response' => json_encode($eqRaw),
            'conversion_status' => 'converted',
            'converted_at' => $now,
            'created_at' => $now,
            'updated_at' => $now,
        ];

        // 6. Behavior Tendencies (G.1)
        $tipeBehavior = fake()->randomElement(['ILMUWAN', 'PEMIMPIN', 'PENGAYOM', 'PELAKSANA']);
        $iman = fake()->numberBetween(15, 25);
        $pikiran = fake()->numberBetween(25, 45);
        $perasaan = fake()->numberBetween(15, 25);
        $interpretasiKebiasaan = 'Kecenderungan perilaku bertipe <strong>'.$tipeBehavior.'</strong> ditandai dengan pola pikir logis, sistematis, analitis, dan kemampuan beradaptasi secara objektif terhadap tuntutan lingkungan kerja.';
        $behaviorTime = self::formatTestDateTime($participant->assessment_date, '14:00:00');
        $behaviorRaw = [
            'status' => true,
            'iman' => (string) $iman,
            'pikiran' => (string) $pikiran,
            'perasaan' => (string) $perasaan,
            'hasil_kecenderungan' => $tipeBehavior,
            'interpretasi_kebiasaan' => $interpretasiKebiasaan,
            'nama_alat_tes' => 'Typical Behavior Tendencies',
        ];

        $results[] = [
            'participant_id' => $participant->id,
            'event_id' => $event->id,
            'test_code' => 'G.1',
            'test_name' => 'Typical Behavior Tendencies',
            'test_category' => TestResult::getCategoryForCode('G.1'),
            'status' => 'completed',
            'source' => $source,
            'test_started_at' => $behaviorTime,
            'summary_data' => json_encode([
                'iman' => (string) $iman,
                'pikiran' => (string) $pikiran,
                'perasaan' => (string) $perasaan,
                'hasil_kecenderungan' => $tipeBehavior,
            ]),
            'interpretation_data' => json_encode(['interpretasi_kebiasaan' => $interpretasiKebiasaan]),
            'raw_response' => json_encode($behaviorRaw),
            'conversion_status' => 'converted',
            'converted_at' => $now,
            'created_at' => $now,
            'updated_at' => $now,
        ];

        // 7. RMIB (H.1)
        $minatPool = ['Clerical', 'Scientific', 'Computational', 'Executive', 'Social Service', 'Persuasive', 'Literary', 'Musical', 'Practical', 'Medical'];
        $selectedMinat = fake()->randomElements($minatPool, 3);
        $rmibInterp = [
            'interpretasi_hasil' => [
                'Minat Utama' => "Kandidat memiliki preferensi minat karir terkuat pada bidang {$selectedMinat[0]}, {$selectedMinat[1]}, dan {$selectedMinat[2]}.",
            ],
        ];
        $rmibTime = self::formatTestDateTime($participant->assessment_date, '14:45:00');
        $rmibRaw = [
            'status' => true,
            'nilai_1' => $selectedMinat[0],
            'nilai_2' => $selectedMinat[1],
            'nilai_3' => $selectedMinat[2],
            'nilai' => '10,8,3',
            'nama_alat_tes' => 'RMIB',
        ];

        $results[] = [
            'participant_id' => $participant->id,
            'event_id' => $event->id,
            'test_code' => 'H.1',
            'test_name' => 'RMIB',
            'test_category' => TestResult::getCategoryForCode('H.1'),
            'status' => 'completed',
            'source' => $source,
            'test_started_at' => $rmibTime,
            'summary_data' => json_encode([
                'nilai_1' => $selectedMinat[0],
                'nilai_2' => $selectedMinat[1],
                'nilai_3' => $selectedMinat[2],
                'nilai' => '10,8,3',
            ]),
            'interpretation_data' => json_encode($rmibInterp),
            'raw_response' => json_encode($rmibRaw),
            'conversion_status' => 'converted',
            'converted_at' => $now,
            'created_at' => $now,
            'updated_at' => $now,
        ];

        return $results;
    }
}
