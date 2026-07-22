<?php

namespace App\Console\Commands;

use App\Services\Lsp\LspIndividualReportService;
use Exception;
use Illuminate\Console\Command;

class TestLspIndividualReport extends Command
{
    protected $signature = 'lsp:test-report {username : Username peserta LSP} {kode_proyek : Kode proyek pelaksanaan}';

    protected $description = 'Test LspIndividualReportService calculation engine against local LSP database';

    public function handle(LspIndividualReportService $service): int
    {
        $username = $this->argument('username');
        $kodeProyek = $this->argument('kode_proyek');

        $this->info('=== MEMUAT LAPORAN INDIVIDU LSP ===');
        $this->line("Username    : {$username}");
        $this->line("Kode Proyek : {$kodeProyek}");

        try {
            $report = $service->getIndividualReport($username, $kodeProyek);

            $this->newLine();
            $this->info('--- 1. IDENTITAS PESERTA ---');
            $this->table(
                ['No Test', 'No SKB', 'Nama Lengkap', 'Pendidikan', 'Jabatan Pelaksana', 'Usia'],
                [[
                    $report['peserta']['no_test'],
                    $report['peserta']['no_kjg'],
                    $report['peserta']['nama_lengkap'],
                    $report['peserta']['pendidikan'],
                    $report['peserta']['jabatan_pelaksana'],
                    $report['peserta']['usia'].' Tahun',
                ]]
            );

            $this->newLine();
            $this->info('--- 2. RINGKASAN SCORE HEADER ---');
            $this->table(
                ['Hasil Psikotest (%)', 'Hasil Wawancara (%)', 'Skor Kejiwaan (MMPI)'],
                [[
                    $report['header_scores']['psikotest_percent'].'%',
                    $report['header_scores']['wawancara_percent'].'%',
                    $report['header_scores']['kejiwaan_score'],
                ]]
            );

            $this->newLine();
            $this->info('--- 3. REKAP PROFIL POTENSI (40%) ---');
            $potensiRows = [];
            foreach ($report['potensi']['aspek_list'] as $aspek) {
                $potensiRows[] = [
                    $aspek['nama_aspek'],
                    $aspek['bobot'],
                    $aspek['standard_rating_toleransi'],
                    $aspek['individual_rating'],
                    $aspek['gap_rating'],
                    $aspek['kesimpulan'],
                ];
            }
            $this->table(['Aspek Potensi', 'Bobot', 'Std (Tol)', 'Indiv Rating', 'GAP Rating', 'Kesimpulan'], $potensiRows);
            $this->line("Total Std Rating: {$report['potensi']['total_standard_rating']} | Total Indiv Rating: {$report['potensi']['total_individual_rating']} | Total Indiv Score: {$report['potensi']['total_individual_score']}");
            $this->info("Kesimpulan Akhir Potensi: {$report['potensi']['kesimpulan_akhir']}");

            $this->newLine();
            $this->info('--- 4. REKAP PROFIL KOMPETENSI (60%) ---');
            $kompetensiRows = [];
            foreach ($report['kompetensi']['aspek_list'] as $kom) {
                $kompetensiRows[] = [
                    $kom['nama_kompetensi'],
                    $kom['bobot'],
                    $kom['standard_rating_toleransi'],
                    $kom['individual_rating'],
                    $kom['gap_rating'],
                    $kom['kesimpulan'],
                ];
            }
            $this->table(['Kompetensi Inti', 'Bobot', 'Std (Tol)', 'Indiv Rating', 'GAP Rating', 'Kesimpulan'], $kompetensiRows);
            $this->line("Total Std Score: {$report['kompetensi']['total_standard_score']} | Total Indiv Score: {$report['kompetensi']['total_individual_score']}");
            $this->info("Kesimpulan Akhir Kompetensi: {$report['kompetensi']['kesimpulan_akhir']}");

            $this->newLine();
            $this->info('--- 5. KESIMPULAN REKOMENDASI GABUNGAN ---');
            $this->table(
                ['Std Score Akhir', 'Indiv Score Akhir', 'Kesimpulan Psikotes', 'Rekomendasi Wawancara', 'FINAL RESULT'],
                [[
                    $report['kesimpulan_psikotest']['total_std_score'],
                    $report['kesimpulan_psikotest']['total_indiv_score'],
                    $report['kesimpulan_psikotest']['kesimpulan_code'],
                    $report['wawancara']['rekomendasi_asesor'],
                    $report['rekomendasi_akhir']['final_text'],
                ]]
            );

            $this->newLine();
            $this->info('--- 6. DATA WAWANSI & ASESOR TA ---');
            $this->line("Penanggung Jawab Asesor : {$report['wawancara']['nama_asesor_ta']} ({$report['wawancara']['jabatan_asesor_ta']})");
            $this->line('Kekuatan                : '.substr($report['wawancara']['kekuatan'], 0, 80).'...');
            $this->line('Kelemahan               : '.substr($report['wawancara']['kelemahan'], 0, 80).'...');
            $this->line('Catatan Khusus          : '.substr($report['wawancara']['catatan_khusus'], 0, 80).'...');

            $this->newLine();
            $this->info('--- 7. TES KEJIWAAN (MMPI) ---');
            $this->line("Validitas     : {$report['kejiwaan']['validitas']}");
            $this->line("Tingkat Stres : {$report['kejiwaan']['tingkat_stres']}");
            $this->line("Kesimpulan    : {$report['kejiwaan']['kesimpulan_mmpi']}");

            $this->newLine();
            $this->info('✅ PROSES INTEGRASI LAPORAN INDIVIDU LSP BERHASIL!');

            return 0;

        } catch (Exception $e) {
            $this->error('ERR: '.$e->getMessage());

            return 1;
        }
    }
}
