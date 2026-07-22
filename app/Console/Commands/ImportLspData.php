<?php

namespace App\Console\Commands;

use App\Services\Lsp\LspDataImporterService;
use Exception;
use Illuminate\Console\Command;

class ImportLspData extends Command
{
    protected $signature = 'lsp:import {kode_proyek : Kode proyek pelaksanaan LSP} {--username= : Import spesifik satu username peserta} {--institution= : ID Instansi SPSP (opsional)}';

    protected $description = 'Import dan sinkronkan data hasil kalkulasi proyek LSP ke database SPSP';

    public function handle(LspDataImporterService $importer): int
    {
        $kodeProyek = $this->argument('kode_proyek');
        $username = $this->option('username');
        $instId = $this->option('institution') ? (int) $this->option('institution') : null;

        $this->info('=== MEMULAI SINKRONISASI DATA LSP KE SPSP ===');
        $this->line("Kode Proyek : {$kodeProyek}");
        if ($username) {
            $this->line("Target User : {$username}");
        }

        try {
            $res = $importer->importProject($kodeProyek, $username, $instId);

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
