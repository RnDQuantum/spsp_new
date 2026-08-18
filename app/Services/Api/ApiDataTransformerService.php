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

        // 2. Jika DB LSP lokal memiliki data pendukung wawancara & identitas, gabungkan!
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
            'mmpi' => [
                'validitas' => $mmpiData['validitas'] ?? '-',
                'internal_pribadi' => $mmpiData['internal'] ?? '-',
                'interpersonal' => $mmpiData['interpersonal'] ?? '-',
                'kapasitas_kerja' => $mmpiData['kapasitas_kerja'] ?? '-',
                'klinis' => $mmpiData['klinis'] ?? '-',
                'kesimpulan' => $mmpiData['kesimpulan'] ?? 'TIDAK MENUNJUKKAN GEJALA GANGGUAN JIWA BERAT',
                'psikogram' => $mmpiData['psikogram'] ?? '-',
                'nilai_pq' => (float) ($mmpiData['pq'] ?? 90.0),
                'tingkat_stres' => $mmpiData['stres'] ?? 'Normal',
            ],
            'kejiwaan' => [
                'validitas' => $mmpiData['validitas'] ?? '-',
                'internal_pribadi' => $mmpiData['internal'] ?? '-',
                'interpersonal' => $mmpiData['interpersonal'] ?? '-',
                'kapasitas_kerja' => $mmpiData['kapasitas_kerja'] ?? '-',
                'klinis' => $mmpiData['klinis'] ?? '-',
                'kesimpulan' => $mmpiData['kesimpulan'] ?? 'TIDAK MENUNJUKKAN GEJALA GANGGUAN JIWA BERAT',
                'psikogram' => $mmpiData['psikogram'] ?? '-',
                'nilai_pq' => (float) ($mmpiData['pq'] ?? 90.0),
                'tingkat_stres' => $mmpiData['stres'] ?? 'Normal',
            ],
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
}
