<?php

namespace App\Console\Commands;

use App\Services\Lsp\LspDataImporterService;
use Exception;
use Illuminate\Console\Command;

class ImportLspData extends Command
{
    protected $signature = 'lsp:import {kode_proyek : Kode proyek pelaksanaan LSP} {--username= : Import spesifik satu username peserta} {--institution= : ID Instansi SPSP (opsional)}';

    protected $description = 'Import dan sinkronkan data hasil kalkulasi proyek LSP ke database SPSP dengan performa tinggi';

    public function handle(LspDataImporterService $importer): int
    {
        $kodeProyek = $this->argument('kode_proyek');
        $username = $this->option('username');
        $instId = $this->option('institution') ? (int) $this->option('institution') : null;

        $isLegacy = $importer->isLegacyProject($kodeProyek);
        $pathLabel = $isLegacy ? 'Jalur A — Legacy Database LSP (< PR-A-338)' : 'Jalur B — REST API psikotes.qhrmi.id (>= PR-A-338)';

        $this->info('=== MEMULAI SINKRONISASI DATA LSP KE SPSP (DUAL-PATH ENGINE) ===');
        $this->line("Kode Proyek : {$kodeProyek}");
        $this->line("Alur Ingest : {$pathLabel}");
        if ($username) {
            $this->line("Target User : {$username}");
        }

        try {
            $progressBar = null;

            $res = $importer->importProject($kodeProyek, $username, $instId, function (int $stepCount, int $totalFound) use (&$progressBar) {
                if (! $progressBar) {
                    $this->newLine();
                    $this->info("Menemukan {$totalFound} peserta. Memproses sinkronisasi data...");
                    $progressBar = $this->output->createProgressBar($totalFound);
                    $progressBar->setFormat(' %current%/%max% [%bar%] %percent:3s%% | Waktu: %elapsed:6s%/%estimated:-6s%');
                    $progressBar->start();
                }
                $progressBar->advance($stepCount);
            });

            if ($progressBar) {
                $progressBar->finish();
                $this->newLine(2);
            }

            $this->newLine();
            $this->info('--- RINGKASAN HASIL IMPOR ---');
            $this->table(
                ['Event ID', 'Kode Event', 'Nama Event', 'Total Ditemukan', 'Berhasil Diimpor', 'Gagal'],
                [[
                    $res['event_id'],
                    $res['event_code'],
                    $res['event_name'],
                    $res['total_found'],
                    $res['imported_count'],
                    $res['failed_count'],
                ]]
            );

            if (! empty($res['errors'])) {
                $this->newLine();
                $this->warn('--- DAFTAR KESALAHAN/CATATAN ---');
                foreach ($res['errors'] as $err) {
                    $this->error("- {$err}");
                }
            }

            $this->newLine();
            $this->info('✅ IMPOR DATA PROYEK LSP BERHASIL DISINKRONKAN KE SPSP!');

            return 0;

        } catch (Exception $e) {
            $this->error('ERR: '.$e->getMessage());

            return 1;
        }
    }
}
