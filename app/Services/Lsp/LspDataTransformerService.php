<?php

namespace App\Services\Lsp;

use Exception;
use Illuminate\Support\Facades\DB;

class LspDataTransformerService
{
    /**
     * Cache in-memory instance untuk query master DB LSP
     */
    protected array $masterCache = [];

    public function __construct(
        protected LspNormEngineService $normEngine
    ) {}

    /**
     * Mendapatkan koneksi DB LSP (koneksi 'lsp' yang terhubung ke DB_LSP_LOCAL)
     */
    protected function getLspConnection()
    {
        return DB::connection('lsp');
    }

    /**
     * Mengambil dan mengolah seluruh data Laporan Individu P3K KJG 2025 untuk 1 peserta.
     */
    public function getIndividualReport(string $username, string $kodeProyek): array
    {
        $batch = $this->getBatchIndividualReports([$username], $kodeProyek);

        if (! isset($batch[$username])) {
            throw new Exception("Data peserta dengan username '{$username}' dan kode proyek '{$kodeProyek}' tidak ditemukan.");
        }

        return $batch[$username];
    }

    /**
     * Mengambil dan mengolah data Laporan Individu untuk sekelompok (batch/chunk) peserta sekaligus.
     * Mengurangi query N+1 hingga 95% dengan batch pre-fetching.
     */
    public function getBatchIndividualReports(array $usernames, string $kodeProyek): array
    {
        if (empty($usernames)) {
            return [];
        }

        $db = $this->getLspConnection();

        // 1. Pre-fetch Peserta Produksi
        $pesertaRows = $db->table('peserta_produksi')
            ->whereIn('username', $usernames)
            ->where(function ($q) use ($kodeProyek) {
                $q->where('kode_pelaksanaan', $kodeProyek)
                    ->orWhere('kode_pelaksanaan', 'LIKE', "%{$kodeProyek}%");
            })
            ->get()
            ->keyBy('username');

        // Fallback untuk username yang mungkin tidak pas di kode_pelaksanaan
        $missingUsernames = array_diff($usernames, $pesertaRows->keys()->toArray());
        if (! empty($missingUsernames)) {
            $fallbackPeserta = $db->table('peserta_produksi')
                ->whereIn('username', $missingUsernames)
                ->get()
                ->keyBy('username');

            foreach ($fallbackPeserta as $u => $row) {
                $pesertaRows[$u] = $row;
            }
        }

        // 2. Pre-fetch Users (Tanggal Lahir)
        $usersRows = $db->table('users')
            ->whereIn('username', $usernames)
            ->get()
            ->keyBy('username');

        // 3. Pre-fetch Skor Mentah Ujian Psikometri
        $ujianRows = $db->table('ujian_peserta_produksi')
            ->where('kode_proyek', $kodeProyek)
            ->whereIn('username', $usernames)
            ->whereIn('typesoal', ['ist', 'kostik', 'personality'])
            ->select('username', 'typesoal', 'nilai')
            ->get();

        $rawScoresMap = [];
        foreach ($ujianRows as $row) {
            $rawScoresMap[$row->username][$row->typesoal] = $row->nilai;
        }

        // 4. Pre-fetch Hasil Wawancara (Kompetensi Inti)
        $wawancaraRows = $db->table('hasil_aspek_yang_digali')
            ->whereIn('username', $usernames)
            ->where('kode_proyek', $kodeProyek)
            ->where('simulasi', 'interview')
            ->get();

        $wawancaraMap = [];
        foreach ($wawancaraRows as $wr) {
            $wawancaraMap[$wr->username][$wr->aspek_penilaian] = $wr;
        }

        // 5. Pre-fetch MMPI / Kejiwaan
        $mmpiRows = $db->table('rekapmmpi_p3kkjg')
            ->whereIn('username', $usernames)
            ->where('kode_proyek', $kodeProyek)
            ->get()
            ->keyBy('username');

        // 6. Pre-fetch Hasil Kelebihan & Kelemahan
        $kelebihanRows = $db->table('hasil_aspek_kelebihan')
            ->whereIn('username', $usernames)
            ->where('simulasi', 'interview')
            ->get()
            ->keyBy('username');

        // 7. Pre-fetch Hasil Rekomendasi Wawancara
        $rekomendasiRows = $db->table('hasil_rekomendasi')
            ->whereIn('username', $usernames)
            ->where('simulasi', 'interview')
            ->get()
            ->keyBy('username');

        // 8. Pre-fetch Hasil Aspek Tambahan Wawancara
        $tambahanRows = $db->table('hasil_aspek_tambahan')
            ->whereIn('username', $usernames)
            ->where('kode_proyek', $kodeProyek)
            ->where('simulasi', 'interview')
            ->get();

        $tambahanMap = [];
        foreach ($tambahanRows as $tr) {
            $tambahanMap[$tr->username][$tr->aspek_tambahan] = $tr;
        }

        // 9. Pre-fetch Validasi TTD Dokumen
        $validasiRows = $db->table('validasi_ttd_report')
            ->where('kode_proyek', $kodeProyek)
            ->where('jenis_dokumen', 'LAPORAN INDIVIDU')
            ->whereIn('untuk', $usernames)
            ->get()
            ->keyBy('untuk');

        // 10. Pre-fetch Asesor TA
        $asesorPjList = $pesertaRows->pluck('asesor_pj')->filter()->unique()->toArray();
        $taMap = [];
        if (! empty($asesorPjList)) {
            $taRows = $db->table('users_personil as up')
                ->leftJoin('penugasan as p', 'p.username', '=', 'up.username')
                ->whereIn('up.username', $asesorPjList)
                ->where('p.kode_proyek', $kodeProyek)
                ->select('up.username', 'up.gelar_depan', 'up.nama_lengkap', 'up.gelar_belakang', 'p.jabatan')
                ->get();

            foreach ($taRows as $tr) {
                $taMap[$tr->username] = $tr;
            }
        }

        // 11. Pre-fetch Metadata Proyek Komon
        $metadataProyekCommon = $this->getMetadataProyekCached($db, $kodeProyek);

        $normData = $this->normEngine->loadNormData();
        $reports = [];

        foreach ($usernames as $u) {
            $peserta = $pesertaRows[$u] ?? null;
            if (! $peserta) {
                continue;
            }

            $userObj = $usersRows[$u] ?? null;
            $tanggalLahir = $peserta->tanggal_lahir ?? ($userObj->tanggal_lahir ?? '1990-01-01');
            $usia = $this->hitungUmurDalamTahun($tanggalLahir);

            $rawStandarForm = trim($peserta->standar_form_penilaian ?? '');
            $standarFormPenilaian = $rawStandarForm !== '' ? $rawStandarForm : 'p3k_kjg_2025';
            $rawJabatan = trim($peserta->jabatan_pelaksana ?? '');
            $levelJabatan = $rawJabatan !== '' ? strtoupper($rawJabatan) : 'STAFF';

            if ($standarFormPenilaian === 'p3k_kjg_2025') {
                if ($levelJabatan === 'TERAMPIL') {
                    $standarFormPenilaian2 = 'p3k_kjg_-_jf_terampil_2025';
                } else {
                    $standarFormPenilaian2 = 'p3k_kjg_-_jf_muda_&_pertama_2025';
                }
            } else {
                $standarFormPenilaian2 = $standarFormPenilaian;
            }

            $rawScores = [
                'ist' => $rawScoresMap[$u]['ist'] ?? null,
                'kostik' => $rawScoresMap[$u]['kostik'] ?? null,
                'personality' => $rawScoresMap[$u]['personality'] ?? null,
            ];

            $hasilIst = $this->normEngine->processIstNorms($rawScores['ist'], $peserta->pendidikan ?? 'S1', $usia, $normData['ist'] ?? null);
            $hasilKostik = $this->normEngine->processKostikNorms($rawScores['kostik'], $normData['kostik'] ?? null);
            $hasil16pf = $this->normEngine->process16pfNorms($rawScores['personality'], $usia, $normData['personality'] ?? null);

            $profilPotensi = $this->normEngine->calculateProfilPotensiCached($db, $standarFormPenilaian2, $standarFormPenilaian, $hasilIst, $hasilKostik, $hasil16pf);
            $profilKompetensi = $this->calculateProfilKompetensiCached($db, $wawancaraMap[$u] ?? [], $standarFormPenilaian2, $hasilIst, $hasilKostik, $hasil16pf);

            $kesimpulanPsikotest = $this->calculateKesimpulanPsikotest($profilPotensi, $profilKompetensi, $hasilIst['iq']);
            $dataKejiwaan = $this->formatKejiwaanRow($mmpiRows[$u] ?? null);
            $dataWawancara = $this->formatWawancaraData($db, $u, $kodeProyek, $standarFormPenilaian2, $peserta, $taMap[$peserta->asesor_pj ?? ''] ?? null, $kelebihanRows[$u] ?? null, $rekomendasiRows[$u] ?? null, $tambahanMap[$u] ?? []);
            $rekomendasiAkhir = $this->evaluateFinalRecommendation($kesimpulanPsikotest, $dataWawancara['rekomendasi_asesor']);
            $interpretasiNarasi = $this->getInterpretasiNarasiCached($db, $peserta, $standarFormPenilaian2, $profilPotensi, $profilKompetensi);

            $validasi = $validasiRows[$u] ?? null;
            $metadataProyek = array_merge($metadataProyekCommon, [
                'no_dokumen' => $validasi->no_dokumen ?? '001/LI-QHRM/2025',
                'kode_validasi' => $validasi->kode_validasi ?? null,
                'qr_code' => $validasi->qr_code ?? null,
            ]);

            $reports[$u] = [
                'peserta' => [
                    'no_test' => $peserta->no_test ?? '-',
                    'no_kjg' => $peserta->no_kjg ?? '-',
                    'username' => $peserta->username,
                    'nama_lengkap' => trim(($peserta->gelar_depan ?? '').' '.($peserta->nama_lengkap ?? '').', '.($peserta->gelar_belakang ?? ''), ' ,'),
                    'jenis_kelamin' => $peserta->jenis_kelamin ?? 'L',
                    'pendidikan' => $peserta->pendidikan ?? '-',
                    'jabatan_pelaksana' => $peserta->jabatan_pelaksana ?? '-',
                    'minat_penempatan' => $peserta->minat_penempatan ?? '-',
                    'pasfoto' => $peserta->pasfoto ?? null,
                    'usia' => $usia,
                ],
                'metadata_proyek' => $metadataProyek,
                'header_scores' => [
                    'psikotest_percent' => round($kesimpulanPsikotest['hasil_psikotest_percent'], 2),
                    'wawancara_percent' => round($kesimpulanPsikotest['hasil_wawancara_percent'], 2),
                    'kejiwaan_score' => $dataKejiwaan['nilai_kejiwaan'],
                ],
                'raw_scores' => $rawScores,
                'potensi' => $profilPotensi,
                'kompetensi' => $profilKompetensi,
                'kesimpulan_psikotest' => $kesimpulanPsikotest,
                'kejiwaan' => $dataKejiwaan,
                'wawancara' => $dataWawancara,
                'rekomendasi_akhir' => $rekomendasiAkhir,
                'interpretasi' => $interpretasiNarasi,
            ];
        }

        return $reports;
    }

    /**
     * Hitung umur dalam tahun dari tanggal lahir
     */
    protected function hitungUmurDalamTahun(string $tanggalLahir): int
    {
        try {
            $birthDate = new \DateTime($tanggalLahir);
            $today = new \DateTime('today');
            if ($birthDate > $today) {
                return 0;
            }

            return (int) $today->diff($birthDate)->y;
        } catch (Exception $e) {
            return 25; // default fallback age
        }
    }

    /**
     * Calculate Kompetensi Profile with Cached Master Rows & Pre-fetched Interview Data
     */
    protected function calculateProfilKompetensiCached($db, array $hasilWawancaraUserMap, string $standarJabatan, array $ist, array $kostik, array $pf16): array
    {
        $toleransiPct = 10;

        $cacheKeyKompetensi = "kompetensi_rows_{$standarJabatan}";
        if (! isset($this->masterCache[$cacheKeyKompetensi])) {
            $this->masterCache[$cacheKeyKompetensi] = $db->table('standard_aspek_yang_digali')
                ->join('aspek_yang_digali', 'aspek_yang_digali.kode_kompetensi', '=', 'standard_aspek_yang_digali.kode_kompetensi')
                ->where('standard_aspek_yang_digali.jenis_standar', $standarJabatan)
                ->where('standard_aspek_yang_digali.kompetensi', 'inti')
                ->orderBy('standard_aspek_yang_digali.urutan', 'asc')
                ->select('standard_aspek_yang_digali.*', 'aspek_yang_digali.nama_kompetensi')
                ->get();
        }
        $standarKompetensiRows = $this->masterCache[$cacheKeyKompetensi];

        $aspekSummary = [];
        $totalStandardRating = 0;
        $totalIndividualRating = 0;
        $totalStandardScore = 0;
        $totalIndividualScore = 0;

        foreach ($standarKompetensiRows as $row) {
            $kodeKom = $row->kode_kompetensi;
            $namaKom = $row->nama_kompetensi;
            $bobot = (float) $row->bobot;
            $stdRating = (float) $row->standar_rating;
            $stdRatingTol = $stdRating - ($stdRating * ($toleransiPct / 100));

            $indivRating = isset($hasilWawancaraUserMap[$kodeKom]) ? (float) $hasilWawancaraUserMap[$kodeKom]->nilai_rating : $stdRating;

            $stdScoreTol = $stdRatingTol * $bobot;
            $indivScore = $indivRating * $bobot;

            $gapRating = $indivRating - $stdRatingTol;
            $gapScore = $indivScore - $stdScoreTol;

            if ($gapRating >= 1.0) {
                $kesimpulan = 'Sangat Baik';
            } elseif ($gapRating >= 0.5) {
                $kesimpulan = 'Baik';
            } elseif ($gapRating >= 0) {
                $kesimpulan = 'Cukup';
            } else {
                $kesimpulan = 'Perlu Peningkatan';
            }

            $totalStandardRating += $stdRatingTol;
            $totalIndividualRating += $indivRating;
            $totalStandardScore += $stdScoreTol;
            $totalIndividualScore += $indivScore;

            $aspekSummary[$kodeKom] = [
                'kode_kompetensi' => $kodeKom,
                'nama_kompetensi' => $namaKom,
                'bobot' => $bobot,
                'standard_rating' => round($stdRating, 2),
                'standard_rating_toleransi' => round($stdRatingTol, 2),
                'standard_score_toleransi' => round($stdScoreTol, 2),
                'individual_rating' => round($indivRating, 2),
                'individual_score' => round($indivScore, 2),
                'gap_rating' => round($gapRating, 2),
                'gap_score' => round($gapScore, 2),
                'kesimpulan' => $kesimpulan,
            ];
        }

        $overallGapRating = $totalIndividualRating - $totalStandardRating;
        $overallGapScore = $totalIndividualScore - $totalStandardScore;

        if ($totalIndividualScore >= $totalStandardScore) {
            $kesimpulanAkhir = 'Sangat Kompeten';
        } elseif ($totalIndividualScore >= $totalStandardScore * 0.9) {
            $kesimpulanAkhir = 'Kompeten';
        } else {
            $kesimpulanAkhir = 'Belum Kompeten';
        }

        return [
            'aspek_list' => $aspekSummary,
            'total_standard_rating' => round($totalStandardRating, 2),
            'total_individual_rating' => round($totalIndividualRating, 2),
            'total_standard_score' => round($totalStandardScore, 2),
            'total_individual_score' => round($totalIndividualScore, 2),
            'gap_total_rating' => round($overallGapRating, 2),
            'gap_total_score' => round($overallGapScore, 2),
            'kesimpulan_akhir' => $kesimpulanAkhir,
        ];
    }

    /**
     * Evaluate Combined Psikotest Kesimpulan
     */
    protected function calculateKesimpulanPsikotest(array $potensi, array $kompetensi, int $iq): array
    {
        $toleransiPct = 10;

        $potensiStdScore = $potensi['total_standard_score'];
        $potensiIndivScore = $potensi['total_individual_score'];

        $kompetensiStdScore = $kompetensi['total_standard_score'];
        $kompetensiIndivScore = $kompetensi['total_individual_score'];

        $potensiStdScoreAkhir = ($potensiStdScore * 40) / 100;
        $potensiIndivScoreAkhir = ($potensiIndivScore * 40) / 100;

        $kompetensiStdScoreAkhir = ($kompetensiStdScore * 60) / 100;
        $kompetensiIndivScoreAkhir = ($kompetensiIndivScore * 60) / 100;

        $totalStdScore = $potensiStdScoreAkhir + $kompetensiStdScoreAkhir;
        $totalIndivScore = $potensiIndivScoreAkhir + $kompetensiIndivScoreAkhir;
        $totalStdScoreTol = $totalStdScore - ($totalStdScore * ($toleransiPct / 100));

        if ($iq >= 90) {
            if ($totalIndivScore >= $totalStdScore) {
                $kesimpulanCode = 'MS';
                $kesimpulanText = 'MEMENUHI SYARAT (MS)';
            } elseif ($totalIndivScore >= $totalStdScoreTol) {
                $kesimpulanCode = 'MMS';
                $kesimpulanText = 'MASIH MEMENUHI SYARAT (MMS)';
            } else {
                $kesimpulanCode = 'TMS';
                $kesimpulanText = 'TIDAK MEMENUHI SYARAT (TMS)';
            }
        } else {
            $kesimpulanCode = 'TMS';
            $kesimpulanText = 'TIDAK MEMENUHI SYARAT (TMS)';
        }

        $hasilPsikotestPct = $potensiStdScore > 0 ? (($potensiIndivScore / $potensiStdScore) * 100) - 30 : 0;
        $hasilWawancaraPct = $kompetensiStdScore > 0 ? (($kompetensiIndivScore / $kompetensiStdScore) * 100) - 20 : 0;

        return [
            'potensi_std_score' => round($potensiStdScore, 2),
            'potensi_indiv_score' => round($potensiIndivScore, 2),
            'potensi_std_score_akhir' => round($potensiStdScoreAkhir, 2),
            'potensi_indiv_score_akhir' => round($potensiIndivScoreAkhir, 2),

            'kompetensi_std_score' => round($kompetensiStdScore, 2),
            'kompetensi_indiv_score' => round($kompetensiIndivScore, 2),
            'kompetensi_std_score_akhir' => round($kompetensiStdScoreAkhir, 2),
            'kompetensi_indiv_score_akhir' => round($kompetensiIndivScoreAkhir, 2),

            'total_std_score' => round($totalStdScore, 2),
            'total_indiv_score' => round($totalIndivScore, 2),
            'total_std_score_toleransi' => round($totalStdScoreTol, 2),

            'kesimpulan_code' => $kesimpulanCode,
            'kesimpulan_text' => $kesimpulanText,
            'hasil_psikotest_percent' => max(0, $hasilPsikotestPct),
            'hasil_wawancara_percent' => max(0, $hasilWawancaraPct),
        ];
    }

    /**
     * Format Kejiwaan Row
     */
    protected function formatKejiwaanRow(?object $row): array
    {
        if (! $row) {
            return [
                'validitas' => '-',
                'internal_pribadi' => [],
                'interpersonal' => [],
                'kapasitas_kerja' => [],
                'klinis' => [],
                'kesimpulan' => [],
                'psikogram' => [],
                'nilai_pq' => 0,
                'tingkat_stres' => '-',
                'nilai_kejiwaan' => 0,
                'kesimpulan_mmpi' => 'BELUM ADA REKOMENDASI',
            ];
        }

        $kesimpulanText = $row->kesimpulan ?? '';
        $nilaiKejiwaan = 0;

        $kataNilai = [
            'tidak mengalami stres' => 90,
            'stress ringan' => 77.5,
            'stres sedang' => 65,
            'stres berat' => 52.5,
            'gejala kejiwaan' => 40,
        ];

        foreach ($kataNilai as $kata => $val) {
            if (stripos($kesimpulanText, $kata) !== false) {
                $nilaiKejiwaan = $val;
                break;
            }
        }

        if ($nilaiKejiwaan == 90) {
            $kesimpulanMmpi = 'MEMENUHI SYARAT (MS)';
        } elseif ($nilaiKejiwaan == 77.5 || $nilaiKejiwaan == 65) {
            $kesimpulanMmpi = 'MASIH MEMENUHI SYARAT (MMS)';
        } elseif ($nilaiKejiwaan == 52.5 || $nilaiKejiwaan == 40) {
            $kesimpulanMmpi = 'TIDAK MEMENUHI SYARAT (TMS)';
        } else {
            $kesimpulanMmpi = 'BELUM ADA REKOMENDASI';
        }

        return [
            'validitas' => $row->validitas ?? '-',
            'internal_pribadi' => preg_split('/\d+\.\s+/', trim($row->internal_pribadi ?? ''), -1, PREG_SPLIT_NO_EMPTY),
            'interpersonal' => preg_split('/\d+\.\s+/', trim($row->interpersonal ?? ''), -1, PREG_SPLIT_NO_EMPTY),
            'kapasitas_kerja' => preg_split('/\d+\.\s+/', trim($row->kapasitas_kerja ?? ''), -1, PREG_SPLIT_NO_EMPTY),
            'klinis' => preg_split('/\d+\.\s+/', trim($row->klinis ?? ''), -1, PREG_SPLIT_NO_EMPTY),
            'kesimpulan' => preg_split('/\d+\.\s+/', trim($row->kesimpulan ?? ''), -1, PREG_SPLIT_NO_EMPTY),
            'psikogram' => preg_split('/\d+\.\s+/', trim($row->psikogram ?? ''), -1, PREG_SPLIT_NO_EMPTY),
            'nilai_pq' => is_numeric(trim((string) ($row->nilai_pq ?? ''))) ? (float) $row->nilai_pq : 0.00,
            'tingkat_stres' => $row->tingkat_stres ?? '-',
            'nilai_kejiwaan' => $nilaiKejiwaan,
            'kesimpulan_mmpi' => $kesimpulanMmpi,
        ];
    }

    /**
     * Format Wawancara Data
     */
    protected function formatWawancaraData($db, string $username, string $kodeProyek, string $standarJabatan, $peserta, ?object $taRow, ?object $kelebihanRow, ?object $rekomRow, array $tambahanMap): array
    {
        $namaTa = $taRow ? trim(($taRow->gelar_depan ?? '').' '.($taRow->nama_lengkap ?? '').', '.($taRow->gelar_belakang ?? ''), ' ,') : 'Asesor Penanggung Jawab';
        $jabatanTa = $taRow->jabatan ?? 'Technical Advisor';

        $rekomCode = $rekomRow->rekomendasi ?? 'MS';
        $rekomText = match ($rekomCode) {
            'TMS' => 'TIDAK MEMENUHI SYARAT (TMS)',
            'MMS' => 'MASIH MEMENUHI SYARAT (MMS)',
            default => 'MEMENUHI SYARAT (MS)'
        };

        $cacheKeyAspekTambahan = "aspek_tambahan_{$standarJabatan}";
        if (! isset($this->masterCache[$cacheKeyAspekTambahan])) {
            $this->masterCache[$cacheKeyAspekTambahan] = $db->table('aspek_tambahan')
                ->join('standard_aspek_yang_digali', 'standard_aspek_yang_digali.kode_kompetensi', '=', 'aspek_tambahan.kode_aspek_tambahan')
                ->where('standard_aspek_yang_digali.jenis_standar', $standarJabatan)
                ->where('standard_aspek_yang_digali.kompetensi', 'tambahan')
                ->select('aspek_tambahan.*', 'standard_aspek_yang_digali.standar_rating')
                ->get();
        }
        $aspekTambahanRows = $this->masterCache[$cacheKeyAspekTambahan];

        $aspekTambahanList = [];
        foreach ($aspekTambahanRows as $at) {
            $atKode = $at->kode_aspek_tambahan;
            $h = $tambahanMap[$atKode] ?? null;
            $aspekTambahanList[] = [
                'kode_aspek_tambahan' => $atKode,
                'nama_aspek_tambahan' => $at->nama_aspek_tambahan,
                'definisi' => $at->definisi ?? '',
                'standar_rating' => (int) $at->standar_rating,
                'nilai' => $h->nilai ?? (int) $at->standar_rating,
                'keterangan' => $h->keterangan ?? '-',
            ];
        }

        return [
            'nama_asesor_ta' => $namaTa,
            'jabatan_asesor_ta' => $jabatanTa,
            'kekuatan' => $kelebihanRow->aspek_kelebihan ?? '-',
            'kelemahan' => $kelebihanRow->aspek_kelemahan ?? '-',
            'catatan_khusus' => $rekomRow->catatan_wajib ?? '-',
            'saran_pengembangan' => $rekomRow->saran_pengembangan ?? '-',
            'rekomendasi_asesor' => $rekomCode,
            'rekomendasi_asesor_text' => $rekomText,
            'aspek_tambahan' => $aspekTambahanList,
        ];
    }

    /**
     * Evaluate Final Combined Recommendation
     */
    protected function evaluateFinalRecommendation(array $psikotest, string $rekomWawancara): array
    {
        $psikotestCode = $psikotest['kesimpulan_code'];

        if ($rekomWawancara === 'TMS' && $psikotestCode === 'TMS') {
            $finalCode = 'TMS';
            $finalText = 'TIDAK MEMENUHI SYARAT (TMS)';
        } elseif (($rekomWawancara === 'TMS' && $psikotestCode === 'MMS') ||
                  ($rekomWawancara === 'MMS' && $psikotestCode === 'TMS') ||
                  ($rekomWawancara === 'MS' && $psikotestCode === 'TMS') ||
                  ($rekomWawancara === 'TMS' && $psikotestCode === 'MS') ||
                  ($rekomWawancara === 'MMS' && $psikotestCode === 'MMS')) {
            $finalCode = 'MMS';
            $finalText = 'MASIH MEMENUHI SYARAT (MMS)';
        } else {
            $finalCode = 'MS';
            $finalText = 'MEMENUHI SYARAT (MS)';
        }

        return [
            'final_code' => $finalCode,
            'final_text' => $finalText,
        ];
    }

    /**
     * Get Narrative Interpretation with Caching
     */
    protected function getInterpretasiNarasiCached($db, $peserta, string $standarJabatan, array $potensi, array $kompetensi): array
    {
        $angka = $peserta->angka ?? 1;

        $cacheKeyPotensi = "kamus_potensi_{$standarJabatan}_{$angka}";
        if (! isset($this->masterCache[$cacheKeyPotensi])) {
            $kamusRows = $db->table('kamus_potensi')
                ->where('standard', $standarJabatan)
                ->where('versi', $angka)
                ->get();

            $map = [];
            foreach ($kamusRows as $item) {
                $key = $item->kode_atribute.'_'.$item->rating;
                $map[$key] = $item->interpretasi;
            }
            $this->masterCache[$cacheKeyPotensi] = $map;
        }
        $mapPotensi = $this->masterCache[$cacheKeyPotensi];

        $potensiNarasi = [];
        foreach ($potensi['aspek_list'] as $aspek) {
            foreach ($aspek['atributs'] as $atrib) {
                $key = $atrib['kode_atribut'].'_'.$atrib['individual_rating'];
                if (isset($mapPotensi[$key])) {
                    $potensiNarasi[] = $mapPotensi[$key];
                }
            }
        }

        $cacheKeyKompetensi = "kamus_kompetensi_{$standarJabatan}_{$angka}";
        if (! isset($this->masterCache[$cacheKeyKompetensi])) {
            $kamusRows = $db->table('kamus_kompetensi')
                ->where('standard', $standarJabatan)
                ->where('versi', $angka)
                ->get();

            $map = [];
            foreach ($kamusRows as $item) {
                $key = $item->kode_kompetensi.'_'.$item->rating;
                $map[$key] = $item->interpretasi;
            }
            $this->masterCache[$cacheKeyKompetensi] = $map;
        }
        $mapKompetensi = $this->masterCache[$cacheKeyKompetensi];

        $kompetensiNarasi = [];
        foreach ($kompetensi['aspek_list'] as $kom) {
            $ratingBulat = (int) round($kom['individual_rating']);
            $key = $kom['kode_kompetensi'].'_'.$ratingBulat;
            if (isset($mapKompetensi[$key])) {
                $kompetensiNarasi[] = $mapKompetensi[$key];
            }
        }

        return [
            'potensi_text' => implode(' ', $potensiNarasi),
            'kompetensi_text' => implode('<br>', $kompetensiNarasi),
        ];
    }

    /**
     * Get Common Project Metadata Cached
     */
    protected function getMetadataProyekCached($db, string $kodeProyek): array
    {
        $cacheKey = "metadata_proyek_common_{$kodeProyek}";
        if (! isset($this->masterCache[$cacheKey])) {
            $proyek = $db->table('proyek')->where('kode_proyek', $kodeProyek)->first();
            $proyekProduksi = $proyek ? $db->table('proyek_produksi')->where('kode', $proyek->nama_proyek)->first() : null;
            $klien = $proyekProduksi ? $db->table('klien')->where('kode_klien', $proyekProduksi->instansi)->first() : null;

            $this->masterCache[$cacheKey] = [
                'nama_proyek' => $proyek->nama_proyek ?? '-',
                'lokasi' => $proyek->lokasi ?? '-',
                'tanggal_pelaksanaan' => $proyek->tanggal_pelaksanaan ?? date('Y-m-d'),
                'sampai_tanggal' => $proyek->sampai_tanggal ?? date('Y-m-d'),
                'nama_klien' => $klien->nama_klien ?? '-',
            ];
        }

        return $this->masterCache[$cacheKey];
    }
}
