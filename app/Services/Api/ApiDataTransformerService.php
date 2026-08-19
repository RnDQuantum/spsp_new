<?php

namespace App\Services\Api;

use App\Services\Lsp\LspDataTransformerService;
use Illuminate\Support\Facades\Log;

class ApiDataTransformerService
{
    public function __construct(
        protected QuantumApiClient $apiClient,
        protected LspDataTransformerService $lspTransformer
    ) {}

    /**
     * Mengambil & mentransformasi data laporan individu seluruh peserta pada satu proyek Jalur B dari API psikotes.qhrmi.id.
     */
    public function getProjectIndividualReports(string $kodeProyek, ?string $singleUsername = null): array
    {
        $apiResponse = $this->apiClient->getProjectData($kodeProyek);

        if (! $apiResponse || empty($apiResponse['peserta'])) {
            Log::warning("ApiDataTransformerService: Data API kosong untuk proyek {$kodeProyek}. Membuka fallback...");

            return [];
        }

        $pesertaMap = $apiResponse['peserta'];
        $results = [];

        foreach ($pesertaMap as $noTes => $pesertaData) {
            $username = $pesertaData['username'] ?? $noTes;

            if ($singleUsername && $username !== $singleUsername && $noTes !== $singleUsername) {
                continue;
            }

            $transformed = $this->transformSingleParticipant($kodeProyek, $username, $pesertaData);
            if ($transformed) {
                $results[$username] = $transformed;
                if ((string) $noTes !== (string) $username) {
                    $results[(string) $noTes] = $transformed;
                }
            }
        }

        return $results;
    }

    /**
     * Transformasi 1 peserta dari response JSON API psikotes.qhrmi.id ke DTO SPSP.
     */
    public function transformSingleParticipant(string $kodeProyek, string $username, array $pesertaData): ?array
    {
        $nama = $pesertaData['nama'] ?? $username;
        $tesMap = $pesertaData['tes'] ?? [];

        // 1. Ekstraksi Komponen Matang IST/CFIT, 16PF, Kostik, MMPI dari API
        $istData = $tesMap['A.5'] ?? $tesMap['A.2'] ?? $tesMap['A.1'] ?? [];
        $pfData = $tesMap['B.2'] ?? [];
        $kostikData = $tesMap['B.1'] ?? [];
        $mmpiData = $tesMap['E.2'] ?? $tesMap['E.1'] ?? [];

        // IQ & Subtest SS (IST / CFIT)
        $iq = (float) ($istData['iq'] ?? $istData['index_kecerdasan_umum'] ?? 100);
        $kategoriIq = $istData['hasil_kategori'] ?? $istData['kategori'] ?? 'Rata-rata';
        $subtestSs = $istData['label_values'] ?? $istData['hasil_sub'] ?? [
            'SE' => 10, 'WA' => 10, 'AN' => 10, 'GE' => 10, 'ME' => 10,
            'RA' => 10, 'ZR' => 10, 'FA' => 10, 'WU' => 10,
        ];

        // Sten Scores (16PF)
        $sten16pf = $pfData['standart_final'] ?? $pfData['nilaiAspek'] ?? [
            'A' => 5, 'B' => 5, 'C' => 5, 'E' => 5, 'F' => 5, 'G' => 5, 'H' => 5,
            'I' => 5, 'L' => 5, 'M' => 5, 'N' => 5, 'O' => 5, 'Q1' => 5, 'Q2' => 5, 'Q3' => 5, 'Q4' => 5,
        ];
        $mdScore = (int) ($pfData['MDStenScore'] ?? 5);

        // Kostik Factors (PAPI Kostik: cek nilaiAspek, hasil, atau key hasil_*)
        $kostikFactors = $kostikData['nilaiAspek'] ?? $kostikData['hasil'] ?? [
            'A' => $kostikData['hasil_A'] ?? 1,
            'G' => $kostikData['hasil_G'] ?? 1,
            'N' => $kostikData['hasil_N'] ?? 1,
            'R' => $kostikData['hasil_R'] ?? 1,
            'C' => $kostikData['hasil_C'] ?? 1,
            'D' => $kostikData['hasil_D'] ?? 1,
            'T' => $kostikData['hasil_T'] ?? 1,
            'V' => $kostikData['hasil_V'] ?? 1,
            'F' => $kostikData['hasil_F'] ?? 1,
            'W' => $kostikData['hasil_W'] ?? 1,
            'L' => $kostikData['hasil_L'] ?? 1,
            'P' => $kostikData['hasil_P'] ?? 1,
            'I' => $kostikData['hasil_I'] ?? 1,
            'S' => $kostikData['hasil_S'] ?? 1,
            'O' => $kostikData['hasil_O'] ?? 1,
            'B' => $kostikData['hasil_B'] ?? 1,
            'X' => $kostikData['hasil_X'] ?? 1,
            'E' => $kostikData['hasil_E'] ?? 1,
            'K' => $kostikData['hasil_K'] ?? 1,
            'Z' => $kostikData['hasil_Z'] ?? 1,
        ];

        // 2. Ekstraksi Detail MMPI (E.1 / E.2) Berdasarkan Glosarium Psikometri
        $mmpiDetails = $this->extractMmpiDetails($mmpiData);

        // 3. Jika DB LSP lokal memiliki data pendukung wawancara & identitas, gabungkan!
        $fallbackReport = null;
        try {
            $fallbackReport = $this->lspTransformer->getIndividualReport($username, $kodeProyek);
        } catch (\Throwable $e) {
            // Log fallback exception non-blocking
        }

        // Susun DTO Terstandar SPSP
        $dto = $fallbackReport ?? [
            'peserta' => [
                'username' => $username,
                'no_test' => $pesertaData['no_test'] ?? ($noTes ?? $username),
                'no_kjg' => $pesertaData['no_kjg'] ?? null,
                'nama_lengkap' => $nama,
                'tempat_lahir' => $pesertaData['tempat_lahir'] ?? null,
                'tanggal_lahir' => $pesertaData['tanggal_lahir'] ?? '1990-01-01',
                'gelar_depan' => $pesertaData['gelar_depan'] ?? null,
                'gelar_belakang' => $pesertaData['gelar_belakang'] ?? null,
                'pendidikan' => $pesertaData['pendidikan'] ?? 'S1',
                'agama' => $pesertaData['agama'] ?? null,
                'status_perkawinan' => $pesertaData['status_perkawinan'] ?? null,
                'umur' => (int) ($pesertaData['umur'] ?? 25),
                'jenis_kelamin' => $pesertaData['jenis_kelamin'] ?? 'L',
                'jabatan_pelaksana' => $pesertaData['jabatan_pelaksana'] ?? 'STAFF',
                'jbt_fungsional' => $pesertaData['jbt_fungsional'] ?? null,
                'jbt_struktural' => $pesertaData['jbt_struktural'] ?? null,
                'pangkat' => $pesertaData['pangkat'] ?? null,
                'golongan' => $pesertaData['golongan'] ?? ($pesertaData['gol'] ?? null),
                'status_kepegawaian' => $pesertaData['status_kepegawaian'] ?? null,
                'unit_kerja' => $pesertaData['unit_kerja'] ?? null,
                'minat_penempatan' => $pesertaData['minat_penempatan'] ?? null,
                'pengalaman_kerja' => $pesertaData['pengalaman_kerja'] ?? null,
                'batch' => $pesertaData['batch'] ?? '1',
                'kode_pelaksanaan' => $kodeProyek,
                'pasfoto' => $pesertaData['pasfoto'] ?? null,
            ],
            'potensi' => [
                'aspek' => [],
                'total_skor_individu' => 0.0,
                'total_skor_standar' => 0.0,
                'total_skor_toleransi' => 0.0,
            ],
            'kompetensi' => [
                'aspek' => [],
                'total_skor_individu' => 0.0,
                'total_skor_standar' => 0.0,
                'total_skor_toleransi' => 0.0,
            ],
            'rekap' => [
                'skor_potensi' => 0.0,
                'skor_kompetensi' => 0.0,
                'total_skor_akhir' => 0.0,
                'total_skor_standar' => 0.0,
                'total_skor_toleransi' => 0.0,
                'persentase_potensi' => 0.0,
                'persentase_kompetensi' => 0.0,
                'persentase_akhir' => 0.0,
                'kesimpulan_potensi' => 'MS',
                'kesimpulan_kompetensi' => 'MS',
                'kesimpulan_psikotes' => 'MS',
                'kesimpulan_wawancara' => 'MS',
                'kesimpulan_final' => 'MS',
            ],
            'mmpi' => $mmpiDetails,
            'kejiwaan' => $mmpiDetails,
            'raw_scores' => [
                'ist' => $istData,
                'pf16' => $pfData,
                'kostik' => $kostikData,
                'api_full' => $tesMap,
            ],
            'interpretasi' => [
                'kelebihan' => '-',
                'kelemahan' => '-',
                'catatan_wajib' => '-',
                'saran_pengembangan' => '-',
            ],
            'ta' => [
                'nama' => 'Technical Advisor',
                'jabatan' => 'Asesor Penanggung Jawab',
            ],
            'legalitas' => [
                'no_dokumen' => "001/LI-SPSP/{$kodeProyek}/".date('Y'),
                'kode_validasi' => strtoupper(substr(md5($username.$kodeProyek), 0, 16)),
                'qr_code' => null,
            ],
        ];

        // Pastikan IQ & komponen tes di-update dari API (Source of Truth)
        $dto['raw_scores']['ist_components'] = [
            'iq' => $iq,
            'kategori' => $kategoriIq,
            'subtest_ss' => $subtestSs,
        ];
        $dto['raw_scores']['pf16_components'] = [
            'sten' => $sten16pf,
            'md' => $mdScore,
        ];

        return $dto;
    }

    /**
     * Ekstraksi dan sintesis data MMPI (E.1 / E.2) berdasarkan Glosarium Psikometri.
     *
     * @param  array<string, mixed>  $mmpiData
     * @return array<string, mixed>
     */
    private function extractMmpiDetails(array $mmpiData): array
    {
        if (empty($mmpiData)) {
            return [
                'validitas' => '-',
                'internal_pribadi' => '-',
                'interpersonal' => '-',
                'kapasitas_kerja' => '-',
                'klinis' => '-',
                'kesimpulan' => 'TIDAK MENUNJUKKAN GEJALA GANGGUAN JIWA BERAT',
                'psikogram' => '-',
                'nilai_pq' => 90.0,
                'tingkat_stres' => 'Normal',
            ];
        }

        // 1. Ekstraksi seluruh T-Score dari skore_bro jika tersedia
        $scoresMap = [];
        $skoreBro = $mmpiData['skore_bro'] ?? ($mmpiData['datafix']['json']['skore_bro'] ?? []);
        if (is_array($skoreBro)) {
            foreach ($skoreBro as $scaleCode => $scaleObj) {
                if (is_array($scaleObj) && isset($scaleObj['t_score'])) {
                    $scoresMap[$scaleCode] = (int) $scaleObj['t_score'];
                } elseif (is_numeric($scaleObj)) {
                    $scoresMap[$scaleCode] = (int) $scaleObj;
                }
            }
        }

        // Tentukan jenis MMPI (E.1 MMPI-2-RF vs E.2 MMPI-2 Klasik)
        $isRf = isset($scoresMap['VRIN-r']) || isset($scoresMap['EID']) || isset($scoresMap['RC1']);
        $type = $isRf ? 'MMPI-2-RF' : 'MMPI-2';

        // 2. Kelompokkan Skala
        $validityKeys = $isRf
            ? ['VRIN-r', 'TRIN-r', 'F-r', 'Fp-r', 'FBS-r', 'L-r', 'K-r']
            : ['VRIN', 'TRIN', 'F', 'Fb', 'Fp', 'Fs', 'FBS', 'L', 'K', 'S'];

        $clinicalKeys = $isRf
            ? ['EID', 'THD', 'BXD', 'RC1', 'RC2', 'RC3', 'RC4', 'RC6', 'RC7', 'RC8', 'RC9']
            : ['Hs', 'D', 'Hy', 'Pd', 'Mf', 'Pa', 'Pt', 'Sc', 'Ma', 'Si'];

        $supplementaryKeys = $isRf
            ? ['GIC', 'HPC', 'NUC', 'ANP', 'JCP', 'AGG', 'ACT', 'PSYC-r', 'DISC-r', 'INTR-r']
            : ['ANX', 'FRS', 'OBS', 'DEP', 'HEA', 'BIZ', 'ANG', 'CYN', 'ASP', 'TPA', 'LSE', 'SOD', 'FAM', 'WRK', 'TRT', 'Es', 'A', 'R', 'Do', 'Re', 'PK'];

        $skalaValiditas = [];
        $skalaKlinis = [];
        $skalaSuplementer = [];
        $elevatedScales = [];

        foreach ($validityKeys as $k) {
            if (isset($scoresMap[$k])) {
                $skalaValiditas[$k] = $scoresMap[$k];
                if ($scoresMap[$k] >= 65) {
                    $elevatedScales[] = $k;
                }
            }
        }

        foreach ($clinicalKeys as $k) {
            if (isset($scoresMap[$k])) {
                $skalaKlinis[$k] = $scoresMap[$k];
                if ($scoresMap[$k] >= 65) {
                    $elevatedScales[] = $k;
                }
            }
        }

        foreach ($supplementaryKeys as $k) {
            if (isset($scoresMap[$k])) {
                $skalaSuplementer[$k] = $scoresMap[$k];
                if ($scoresMap[$k] >= 65) {
                    $elevatedScales[] = $k;
                }
            }
        }

        // 3. Sintesis Validitas & Kelaikan
        $hasInvalidF = ($scoresMap['F'] ?? 0) >= 75 || ($scoresMap['F-r'] ?? 0) >= 75;
        $hasInvalidL = ($scoresMap['L'] ?? 0) >= 75 || ($scoresMap['L-r'] ?? 0) >= 75;
        $validitasText = $hasInvalidF || $hasInvalidL
            ? 'Validitas Perlu Perhatian - Terindikasi sikap defensif atau melebih-lebihkan keluhan'
            : 'Valid - Protokol pengisian tes konsisten dan dapat dipercaya';

        // 4. Sintesis Domain Psikologis
        $anxietyScore = $scoresMap['A'] ?? ($scoresMap['Pt'] ?? ($scoresMap['RC7'] ?? ($scoresMap['ANX'] ?? 50)));
        $depressionScore = $scoresMap['D'] ?? ($scoresMap['RC2'] ?? ($scoresMap['DEP'] ?? 50));
        $internalText = ($anxietyScore >= 65 || $depressionScore >= 65)
            ? 'Terdapat indikasi ketegangan emosional atau kecemasan yang memerlukan pengelolaan stres aktif.'
            : 'Stabilitas emosi terjaga dengan baik, memiliki daya adaptasi personal yang matang.';

        $interpersonalScore = $scoresMap['Pd'] ?? ($scoresMap['RC3'] ?? ($scoresMap['CYN'] ?? ($scoresMap['SOD'] ?? 50)));
        $interpersonalText = ($interpersonalScore >= 65)
            ? 'Cenderung kritis dan defensif dalam interaksi sosial; memerlukan penguatan komunikasi persuasif.'
            : 'Mampu menjalin relasi kerja yang kooperatif, harmonis, dan adaptif di lingkungan organisasi.';

        $workScore = $scoresMap['WRK'] ?? ($scoresMap['BXD'] ?? ($scoresMap['TPA'] ?? 50));
        $kapKerjaText = ($workScore >= 65)
            ? 'Kapasitas kerja memadai namun rentan mengalami hambatan kerja di bawah beban multi-tasking intensif.'
            : 'Kapasitas dan ketahanan kerja sangat optimal dalam menyelesaikan target-target operasional.';

        $hasClinicalElevation = false;
        foreach (['Pa', 'Sc', 'Ma', 'THD', 'RC6', 'RC8', 'BIZ'] as $ck) {
            if (($scoresMap[$ck] ?? 0) >= 65) {
                $hasClinicalElevation = true;
                break;
            }
        }
        $klinikText = $hasClinicalElevation
            ? 'Terdapat elevasi pada beberapa indikator klinis yang disarankan untuk observasi suportif berkelanjutan.'
            : 'Tidak terdeteksi adanya indikasi klinis bermakna yang mengganggu stabilitas mental di tempat kerja.';

        $kesimpulanText = $hasClinicalElevation
            ? 'Kandidat dapat menjalankan tugas dengan pendampingan dan manajemen beban kerja yang terukur.'
            : 'TIDAK MENUNJUKKAN GEJALA GANGGUAN JIWA BERAT';

        // 5. Kalkulasi Nilai PQ & Tingkat Stres
        $egoStrength = $scoresMap['Es'] ?? 55;
        $nilaiPq = (float) ($mmpiData['pq'] ?? ($mmpiData['nilai_pq'] ?? round(max(50.0, min(95.0, ($egoStrength * 1.4))), 2)));

        $tingkatStres = $anxietyScore >= 65 ? 'Tinggi' : ($anxietyScore >= 55 ? 'Sedang' : 'Normal');
        if (isset($mmpiData['stres']) && is_string($mmpiData['stres'])) {
            $tingkatStres = $mmpiData['stres'];
        }

        // 6. Susun Struktur Psikogram Terstandar
        $psikogramData = [
            'tipe' => $type,
            'skala_validitas' => $skalaValiditas,
            'skala_klinis' => $skalaKlinis,
            'skala_suplementer' => $skalaSuplementer,
            'elevated_scales' => array_unique($elevatedScales),
        ];

        return [
            'validitas' => $validitasText,
            'internal_pribadi' => $internalText,
            'interpersonal' => $interpersonalText,
            'kapasitas_kerja' => $kapKerjaText,
            'klinis' => $klinikText,
            'kesimpulan' => $kesimpulanText,
            'psikogram' => $psikogramData,
            'nilai_pq' => $nilaiPq,
            'tingkat_stres' => $tingkatStres,
        ];
    }
}
