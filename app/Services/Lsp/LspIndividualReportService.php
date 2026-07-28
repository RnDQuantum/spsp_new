<?php

namespace App\Services\Lsp;

use Exception;
use Illuminate\Support\Facades\DB;

class LspIndividualReportService
{
    /**
     * Cache in-memory static untuk norma JSON (IST, Kostik, Personality)
     */
    protected static ?array $normCache = null;

    /**
     * Cache in-memory instance untuk query master DB LSP (Standar Potensi, Tool Mappings, Kamus, Metadata Proyek)
     */
    protected array $masterCache = [];

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

        $normData = $this->loadNormData();
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

            $hasilIst = $this->processIstNorms($rawScores['ist'], $peserta->pendidikan ?? 'S1', $usia, $normData['ist'] ?? null);
            $hasilKostik = $this->processKostikNorms($rawScores['kostik'], $normData['kostik'] ?? null);
            $hasil16pf = $this->process16pfNorms($rawScores['personality'], $usia, $normData['personality'] ?? null);

            $profilPotensi = $this->calculateProfilPotensiCached($db, $standarFormPenilaian2, $standarFormPenilaian, $hasilIst, $hasilKostik, $hasil16pf);
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
     * Read norm JSON files with static in-memory caching
     */
    protected function loadNormData(): array
    {
        if (static::$normCache !== null) {
            return static::$normCache;
        }

        $norms = ['ist' => null, 'kostik' => null, 'personality' => null];

        $paths = [
            'ist' => resource_path('data/lsp_norms/ist.json'),
            'kostik' => resource_path('data/lsp_norms/kostik.json'),
            'personality' => resource_path('data/lsp_norms/personality.json'),
        ];

        foreach ($paths as $key => $path) {
            if (file_exists($path)) {
                $content = file_get_contents($path);
                $norms[$key] = json_decode($content, true);
            }
        }

        static::$normCache = $norms;

        return static::$normCache;
    }

    /**
     * Process IST Raw Scores to SS and IQ
     */
    protected function processIstNorms(?string $rawIst, string $pendidikan, int $usia, ?array $istNorm): array
    {
        $defaultResult = [
            'iq' => 100,
            'kategori' => 'Rata-rata',
            'scores' => ['SE' => 10, 'WA' => 10, 'AN' => 10, 'GE' => 10, 'ME' => 10, 'RA' => 10, 'ZR' => 10, 'FA' => 10, 'WU' => 10],
        ];

        if (empty($rawIst)) {
            return $defaultResult;
        }

        $arrayIst = explode(',', $rawIst);
        if (count($arrayIst) < 9) {
            return $defaultResult;
        }

        $rsSum = array_sum(array_slice($arrayIst, 0, 9));
        $rsSE = (int) $arrayIst[0];
        $rsWA = (int) $arrayIst[1];
        $rsAN = (int) $arrayIst[2];
        $rsGE = (int) $arrayIst[3];
        $rsRA = (int) $arrayIst[4];
        $rsZR = (int) $arrayIst[5];
        $rsFA = (int) $arrayIst[6];
        $rsWU = (int) $arrayIst[7];
        $rsME = (int) $arrayIst[8];

        if (! $istNorm) {
            $iqEst = 90 + min(40, (int) ($rsSum / 3));

            return [
                'iq' => $iqEst,
                'kategori' => $iqEst >= 110 ? 'Tinggi' : ($iqEst >= 90 ? 'Rata-rata' : 'Rendah'),
                'scores' => [
                    'IQ' => $iqEst, 'SE' => $rsSE, 'WA' => $rsWA, 'AN' => $rsAN, 'GE' => $rsGE,
                    'ME' => $rsME, 'RA' => $rsRA, 'ZR' => $rsZR, 'FA' => $rsFA, 'WU' => $rsWU,
                ],
            ];
        }

        $pendidikanUpper = strtoupper($pendidikan);
        if (in_array($pendidikanUpper, ['SMA', 'SMK'])) {
            $iqIst = $istNorm['hasil_iq_pendidikan'][$istNorm['sw_sma'][$rsSum] ?? 0] ?? 100;
            $ssSE = $istNorm['aspek_pendidikan']['SMA']['SE'][$rsSE] ?? 10;
            $ssWA = $istNorm['aspek_pendidikan']['SMA']['WA'][$rsWA] ?? 10;
            $ssAN = $istNorm['aspek_pendidikan']['SMA']['AN'][$rsAN] ?? 10;
            $ssGE = $istNorm['aspek_pendidikan']['SMA']['GE'][$rsGE] ?? 10;
            $ssME = $istNorm['aspek_pendidikan']['SMA']['ME'][$rsME] ?? 10;
            $ssRA = $istNorm['aspek_pendidikan']['SMA']['RA'][$rsRA] ?? 10;
            $ssZR = $istNorm['aspek_pendidikan']['SMA']['ZR'][$rsZR] ?? 10;
            $ssFA = $istNorm['aspek_pendidikan']['SMA']['FA'][$rsFA] ?? 10;
            $ssWU = $istNorm['aspek_pendidikan']['SMA']['WU'][$rsWU] ?? 10;
        } elseif (in_array($pendidikanUpper, ['S1', 'D3', 'D4', 'S2', 'S3'])) {
            $iqIst = $istNorm['hasil_iq_pendidikan'][$istNorm['sw_si'][$rsSum] ?? 0] ?? 100;
            $ssSE = $istNorm['aspek_pendidikan']['SI']['SE'][$rsSE] ?? 10;
            $ssWA = $istNorm['aspek_pendidikan']['SI']['WA'][$rsWA] ?? 10;
            $ssAN = $istNorm['aspek_pendidikan']['SI']['AN'][$rsAN] ?? 10;
            $ssGE = $istNorm['aspek_pendidikan']['SI']['GE'][$rsGE] ?? 10;
            $ssME = $istNorm['aspek_pendidikan']['SI']['ME'][$rsME] ?? 10;
            $ssRA = $istNorm['aspek_pendidikan']['SI']['RA'][$rsRA] ?? 10;
            $ssZR = $istNorm['aspek_pendidikan']['SI']['ZR'][$rsZR] ?? 10;
            $ssFA = $istNorm['aspek_pendidikan']['SI']['FA'][$rsFA] ?? 10;
            $ssWU = $istNorm['aspek_pendidikan']['SI']['WU'][$rsWU] ?? 10;
        } else {
            $umurKey = $this->getIstAgeKey($usia);
            $iqIst = $istNorm['hasil_iq'][$istNorm['aspek'][$umurKey]['GESAMT'][$rsSum] ?? 0] ?? 100;
            $ssSE = $istNorm['aspek'][$umurKey]['SE'][$rsSE] ?? 10;
            $ssWA = $istNorm['aspek'][$umurKey]['WA'][$rsWA] ?? 10;
            $ssAN = $istNorm['aspek'][$umurKey]['AN'][$rsAN] ?? 10;
            $ssGE = $istNorm['aspek'][$umurKey]['GE'][$rsGE] ?? 10;
            $ssME = $istNorm['aspek'][$umurKey]['ME'][$rsME] ?? 10;
            $ssRA = $istNorm['aspek'][$umurKey]['RA'][$rsRA] ?? 10;
            $ssZR = $istNorm['aspek'][$umurKey]['ZR'][$rsZR] ?? 10;
            $ssFA = $istNorm['aspek'][$umurKey]['FA'][$rsFA] ?? 10;
            $ssWU = $istNorm['aspek'][$umurKey]['WU'][$rsWU] ?? 10;
        }

        $kategoriCode = '4';
        if ($iqIst <= 89) {
            $kategoriCode = '5';
        } elseif ($iqIst <= 109) {
            $kategoriCode = '4';
        } elseif ($iqIst <= 119) {
            $kategoriCode = '3';
        } elseif ($iqIst <= 129) {
            $kategoriCode = '2';
        } elseif ($iqIst >= 130) {
            $kategoriCode = '1';
        }

        $kategoriText = $istNorm['kategori'][$kategoriCode] ?? 'Rata-rata';

        return [
            'iq' => $iqIst,
            'kategori' => $kategoriText,
            'scores' => [
                'IQ' => $iqIst, 'SE' => $ssSE, 'WA' => $ssWA, 'AN' => $ssAN, 'GE' => $ssGE,
                'ME' => $ssME, 'RA' => $ssRA, 'ZR' => $ssZR, 'FA' => $ssFA, 'WU' => $ssWU,
            ],
        ];
    }

    protected function getIstAgeKey(int $usia): string
    {
        if ($usia <= 12) {
            return '12';
        }
        if ($usia <= 18) {
            return (string) $usia;
        }
        if ($usia <= 20) {
            return '19-20';
        }
        if ($usia <= 25) {
            return '21-25';
        }
        if ($usia <= 30) {
            return '26-30';
        }
        if ($usia <= 35) {
            return '31-35';
        }
        if ($usia <= 40) {
            return '36-40';
        }
        if ($usia <= 45) {
            return '41-45';
        }
        if ($usia <= 50) {
            return '46-50';
        }

        return '51-60';
    }

    /**
     * Process PAPI Kostik raw scores
     */
    protected function processKostikNorms(?string $rawKostik, ?array $kostikNorm): array
    {
        $factors = ['A', 'G', 'N', 'R', 'C', 'D', 'T', 'V', 'F', 'W', 'L', 'P', 'I', 'S', 'O', 'B', 'X', 'E', 'K', 'Z'];
        $result = array_fill_keys($factors, 1);

        if (empty($rawKostik)) {
            return $result;
        }

        $arr = explode(',', $rawKostik);
        foreach ($factors as $idx => $factor) {
            if (isset($arr[$idx])) {
                $result[$factor] = (int) $arr[$idx];
            }
        }

        return $result;
    }

    /**
     * Process 16PF raw scores with MD adjustment & Sten lookups
     */
    protected function process16pfNorms(?string $rawPersonality, int $usia, ?array $personalityNorm): array
    {
        $factors = ['A', 'B', 'C', 'E', 'F', 'G', 'H', 'I', 'L', 'M', 'N', 'O', 'Q1', 'Q2', 'Q3', 'Q4'];
        $result = array_fill_keys($factors, 5);

        if (empty($rawPersonality)) {
            return $result;
        }

        $arr = explode(',', $rawPersonality);
        $md = isset($arr[0]) ? (int) $arr[0] : 0;

        $stenScoreGroup = '30';
        if ($usia <= 19) {
            $stenScoreGroup = '17';
        } elseif ($usia <= 29) {
            $stenScoreGroup = '20';
        }

        $allFactors = ['MD', 'A', 'B', 'C', 'E', 'F', 'G', 'H', 'I', 'L', 'M', 'N', 'O', 'Q1', 'Q2', 'Q3', 'Q4'];

        for ($i = 1; $i < count($arr); $i++) {
            if (! isset($allFactors[$i])) {
                continue;
            }
            $fName = $allFactors[$i];
            $rawVal = (int) $arr[$i];

            if ($personalityNorm && isset($personalityNorm['sten_score'][$stenScoreGroup][$fName][$rawVal])) {
                $baseSten = (int) $personalityNorm['sten_score'][$stenScoreGroup][$fName][$rawVal];
            } else {
                $baseSten = min(10, max(1, $rawVal));
            }

            if ($md == 10) {
                if (in_array($fName, ['O', 'Q4'])) {
                    $baseSten += 2;
                } elseif (in_array($fName, ['C', 'Q3'])) {
                    $baseSten -= 2;
                } elseif (in_array($fName, ['L', 'N', 'Q2'])) {
                    $baseSten += 1;
                } elseif (in_array($fName, ['A', 'G', 'H'])) {
                    $baseSten -= 1;
                }
            } elseif ($md == 8 || $md == 9) {
                if (in_array($fName, ['L', 'N', 'O', 'Q2', 'Q4'])) {
                    $baseSten += 1;
                } elseif (in_array($fName, ['A', 'C', 'G', 'H', 'Q3'])) {
                    $baseSten -= 1;
                }
            } elseif ($md == 7) {
                if (in_array($fName, ['O', 'Q4'])) {
                    $baseSten += 1;
                } elseif (in_array($fName, ['C', 'Q3'])) {
                    $baseSten -= 1;
                }
            }

            $result[$fName] = max(1, min(10, $baseSten));
        }

        return $result;
    }

    /**
     * Calculate Potensi Profile with Master Query Caching
     */
    protected function calculateProfilPotensiCached($db, string $standarJabatan, string $standarPenilaian, array $ist, array $kostik, array $pf16): array
    {
        $toleransiPct = 10;

        $cacheKeyPotensi = "potensi_rows_{$standarJabatan}";
        if (! isset($this->masterCache[$cacheKeyPotensi])) {
            $this->masterCache[$cacheKeyPotensi] = $db->table('standar_potensi')
                ->join('standar_aspek', 'standar_aspek.kode_aspek', '=', 'standar_potensi.aspek')
                ->join('standar_atribute', 'standar_atribute.kode_atribute', '=', 'standar_potensi.atribut')
                ->where('standar_potensi.standar_jabatan', $standarJabatan)
                ->where('standar_potensi.level', 'potensi')
                ->select('standar_potensi.*', 'standar_aspek.aspek_penilaian', 'standar_atribute.nama_atribute')
                ->orderBy('standar_potensi.id', 'asc')
                ->orderBy('standar_potensi.urutan', 'asc')
                ->get();
        }
        $standarPotensiRows = $this->masterCache[$cacheKeyPotensi];

        $cacheKeyMappings = "tool_mappings_{$standarJabatan}_{$standarPenilaian}";
        if (! isset($this->masterCache[$cacheKeyMappings])) {
            $this->masterCache[$cacheKeyMappings] = $db->table('standar_atribute_alat_ukur')
                ->join('standar_potensi', 'standar_atribute_alat_ukur.kode_atribute', '=', 'standar_potensi.atribut')
                ->where('standar_potensi.standar_jabatan', $standarJabatan)
                ->where('standar_atribute_alat_ukur.standard', $standarPenilaian)
                ->orderBy('standar_potensi.urutan', 'asc')
                ->get();
        }
        $toolMappings = $this->masterCache[$cacheKeyMappings];

        $aspekAtributRatings = [];
        foreach ($toolMappings as $mapping) {
            $x = 0;
            if ($mapping->alat_ukur === 'ist') {
                $x = $ist['scores'][$mapping->komponen] ?? 0;
            } elseif ($mapping->alat_ukur === 'kostik') {
                $x = $kostik[$mapping->komponen] ?? 0;
            } elseif ($mapping->alat_ukur === '16pf') {
                $x = $pf16[$mapping->komponen] ?? 0;
            }

            $rating = 1;
            if ($mapping->tingkat === '+') {
                if ($x <= $mapping->skala_1) {
                    $rating = 1;
                } elseif ($x <= $mapping->skala_2) {
                    $rating = 2;
                } elseif ($x <= $mapping->skala_3) {
                    $rating = 3;
                } elseif ($x <= $mapping->skala_4) {
                    $rating = 4;
                } elseif ($x >= $mapping->skala_5) {
                    $rating = 5;
                }
            } elseif ($mapping->tingkat === '-') {
                if ($x >= $mapping->skala_1) {
                    $rating = 1;
                } elseif ($x >= $mapping->skala_2) {
                    $rating = 2;
                } elseif ($x >= $mapping->skala_3) {
                    $rating = 3;
                } elseif ($x >= $mapping->skala_4) {
                    $rating = 4;
                } elseif ($x <= $mapping->skala_5) {
                    $rating = 5;
                }
            }

            $aspekAtributRatings[$mapping->aspek][$mapping->atribut][] = $rating;
        }

        $actualAttributeRating = [];
        foreach ($aspekAtributRatings as $aspek => $atributs) {
            foreach ($atributs as $atrib => $ratings) {
                $actualAttributeRating[$aspek][$atrib] = (int) round(array_sum($ratings) / count($ratings));
            }
        }

        $aspekSummary = [];
        $totalStandardRating = 0;
        $totalIndividualRating = 0;
        $totalStandardScore = 0;
        $totalIndividualScore = 0;

        $groupedAspects = [];
        foreach ($standarPotensiRows as $row) {
            $groupedAspects[$row->aspek][] = $row;
        }

        foreach ($groupedAspects as $aspekKode => $rows) {
            $namaAspek = $rows[0]->aspek_penilaian;
            $bobot = (float) $rows[0]->bobot;

            $stdRatings = array_map(fn ($r) => (float) $r->standar_rating, $rows);
            $avgStdRating = array_sum($stdRatings) / count($stdRatings);
            $stdRatingTol = $avgStdRating - ($avgStdRating * ($toleransiPct / 100));

            $indivRatings = [];
            foreach ($rows as $r) {
                $indivRatings[] = $actualAttributeRating[$aspekKode][$r->atribut] ?? (int) $r->standar_rating;
            }
            $avgIndivRating = count($indivRatings) > 0 ? (array_sum($indivRatings) / count($indivRatings)) : $avgStdRating;

            $stdScoreTol = $stdRatingTol * $bobot;
            $indivScore = $avgIndivRating * $bobot;

            $gapRating = $avgIndivRating - $stdRatingTol;
            $gapScore = $indivScore - $stdScoreTol;

            if ($gapScore > 0) {
                $kesimpulan = 'Sangat Memenuhi Standard';
            } elseif ($gapScore == 0) {
                $kesimpulan = 'Memenuhi Standard';
            } else {
                $kesimpulan = ($avgIndivRating >= $stdRatingTol) ? 'Masih Memenuhi Standard' : 'Kurang Memenuhi Standard';
            }

            $totalStandardRating += $stdRatingTol;
            $totalIndividualRating += $avgIndivRating;
            $totalStandardScore += $stdScoreTol;
            $totalIndividualScore += $indivScore;

            $aspekSummary[$aspekKode] = [
                'kode_aspek' => $aspekKode,
                'nama_aspek' => $namaAspek,
                'bobot' => $bobot,
                'standard_rating' => round($avgStdRating, 2),
                'standard_rating_toleransi' => round($stdRatingTol, 2),
                'standard_score_toleransi' => round($stdScoreTol, 2),
                'individual_rating' => round($avgIndivRating, 2),
                'individual_score' => round($indivScore, 2),
                'gap_rating' => round($gapRating, 2),
                'gap_score' => round($gapScore, 2),
                'kesimpulan' => $kesimpulan,
                'atributs' => array_map(fn ($r) => [
                    'kode_atribut' => $r->atribut,
                    'nama_atribut' => $r->nama_atribute,
                    'standard_rating' => (int) $r->standar_rating,
                    'individual_rating' => $actualAttributeRating[$aspekKode][$r->atribut] ?? (int) $r->standar_rating,
                ], $rows),
            ];
        }

        $overallGapRating = $totalIndividualRating - $totalStandardRating;
        $overallGapScore = $totalIndividualScore - $totalStandardScore;

        if ($overallGapScore > 0) {
            $kesimpulanAkhir = 'Memenuhi Standard';
        } elseif ($overallGapScore == 0) {
            $kesimpulanAkhir = 'Memenuhi Standard';
        } else {
            $kesimpulanAkhir = 'Di Bawah Standard';
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
