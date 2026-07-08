<?php

namespace App\Console\Commands;

use App\Models\Participant;
use App\Models\TestResult;
use App\Services\TestResultImportService;
use Illuminate\Console\Command;

class ImportTestResults extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test-results:import
                            {--file= : Path ke file JSON untuk import (format: sample per-tes atau multi-peserta)}
                            {--dir= : Path ke directory berisi file JSON per alat tes}
                            {--event= : Event ID untuk import}
                            {--participant= : Participant ID untuk import}
                            {--dry-run : Tampilkan data yang akan di-import tanpa menyimpan}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import data tes mentah dari file JSON ke tabel test_results (development & testing)';

    /**
     * Execute the console command.
     */
    public function handle(TestResultImportService $importService): int
    {
        $this->info('╔══════════════════════════════════════════════╗');
        $this->info('║  Import Test Results — Data API Tes Online   ║');
        $this->info('╚══════════════════════════════════════════════╝');
        $this->newLine();

        // Validasi: harus ada --file atau --dir
        if (!$this->option('file') && !$this->option('dir')) {
            $this->error('Harus menyertakan --file=<path> atau --dir=<path>');
            $this->line('');
            $this->line('Contoh:');
            $this->line('  php artisan test-results:import --file=sample.json --event=1 --participant=1');
            $this->line('  php artisan test-results:import --dir=output_analisis/sample_per_tes/ --event=1 --participant=1');
            return Command::FAILURE;
        }

        // Validasi event & participant
        $eventId = (int) $this->option('event');
        $participantId = (int) $this->option('participant');

        if (!$eventId || !$participantId) {
            $this->error('Harus menyertakan --event=<id> dan --participant=<id>');
            return Command::FAILURE;
        }

        // Validasi participant ada di database
        $participant = Participant::find($participantId);
        if (!$participant) {
            $this->error("Participant ID {$participantId} tidak ditemukan di database.");
            return Command::FAILURE;
        }

        $this->info("Event ID    : {$eventId}");
        $this->info("Participant : {$participant->name} (ID: {$participantId})");
        $this->newLine();

        // Kumpulkan data tes dari file(s)
        $tesData = $this->collectTestData();

        if (empty($tesData)) {
            $this->warn('Tidak ada data tes yang ditemukan.');
            return Command::FAILURE;
        }

        $this->info("Ditemukan " . count($tesData) . " alat tes");
        $this->newLine();

        // Dry-run mode
        if ($this->option('dry-run')) {
            return $this->dryRun($tesData);
        }

        // Import!
        $this->info('Memulai import...');
        $result = $importService->importParticipantTests($participantId, $eventId, $tesData);

        $this->newLine();
        $this->displayResults($result);

        return ($result['failed'] > 0) ? Command::FAILURE : Command::SUCCESS;
    }

    /**
     * Kumpulkan data tes dari file atau directory.
     */
    protected function collectTestData(): array
    {
        $tesData = [];

        if ($file = $this->option('file')) {
            $tesData = $this->readSingleFile($file);
        } elseif ($dir = $this->option('dir')) {
            $tesData = $this->readDirectory($dir);
        }

        return $tesData;
    }

    /**
     * Baca satu file JSON.
     * Mendukung format sample per-tes (dari collect_analisis_alat_tes.py).
     */
    protected function readSingleFile(string $path): array
    {
        if (!file_exists($path)) {
            $this->error("File tidak ditemukan: {$path}");
            return [];
        }

        $content = json_decode(file_get_contents($path), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->error("File JSON tidak valid: {$path}");
            return [];
        }

        // Format sample per-tes: {"kode_tes": "A.1", "response_utuh": {...}}
        if (isset($content['kode_tes']) && isset($content['response_utuh'])) {
            $testCode = $content['kode_tes'];
            $testName = $content['nama'] ?? 'unknown';
            $this->line("  Membaca: {$testCode} — {$testName}");
            return [$testCode => $content['response_utuh']];
        }

        // Format multi-tes: {"A.1": {...}, "B.2": {...}}
        $this->line("  Membaca multi-tes dari file ({$path})");
        return $content;
    }

    /**
     * Baca semua file JSON dari directory.
     * Skip _index.json dan file non-JSON.
     */
    protected function readDirectory(string $dir): array
    {
        if (!is_dir($dir)) {
            $this->error("Directory tidak ditemukan: {$dir}");
            return [];
        }

        $tesData = [];
        $files = glob(rtrim($dir, '/\\') . '/*.json');

        foreach ($files as $file) {
            $basename = basename($file);

            // Skip index file
            if ($basename === '_index.json') {
                continue;
            }

            $content = json_decode(file_get_contents($file), true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $this->warn("  Skip (invalid JSON): {$basename}");
                continue;
            }

            // Format sample per-tes
            if (isset($content['kode_tes']) && isset($content['response_utuh'])) {
                $testCode = $content['kode_tes'];
                $isKosong = $content['data_kosong'] ?? true;
                $status = $isKosong ? '<fg=yellow>KOSONG</>' : '<fg=green>terisi</>';
                $testName = $content['nama'] ?? 'unknown';
                $this->line("  {$testCode} — {$testName} [{$status}]");
                $tesData[$testCode] = $content['response_utuh'];
            }
        }

        return $tesData;
    }

    /**
     * Tampilkan preview data tanpa menyimpan (dry-run).
     */
    protected function dryRun(array $tesData): int
    {
        $this->info('=== DRY RUN MODE (tidak menyimpan ke database) ===');
        $this->newLine();

        $tableData = [];

        foreach ($tesData as $testCode => $data) {
            $isExcluded = TestResult::isExcluded($testCode);
            $category = TestResult::getCategoryForCode($testCode);
            $dataKeys = count(array_keys($data));
            $testName = $data['nama_alat_tes'] ?? 'N/A';

            $statusLabel = $isExcluded ? '❌ SKIP (excluded)' : '✅ Will import';

            $tableData[] = [
                $testCode,
                mb_substr($testName, 0, 30),
                $category,
                $dataKeys . ' fields',
                $statusLabel,
            ];
        }

        $this->table(
            ['Kode', 'Nama', 'Kategori', 'Data Size', 'Status'],
            $tableData
        );

        return Command::SUCCESS;
    }

    /**
     * Tampilkan hasil import.
     */
    protected function displayResults(array $result): void
    {
        $this->info("┌──────────────────────────────────┐");
        $this->info("│  Hasil Import                    │");
        $this->info("├──────────────────────────────────┤");
        $this->info("│  ✅ Imported : {$result['imported']}");
        $this->info("│  ⏭️  Skipped  : {$result['skipped']}");

        if ($result['failed'] > 0) {
            $this->error("│  ❌ Failed   : {$result['failed']}");
        } else {
            $this->info("│  ❌ Failed   : 0");
        }

        $this->info("└──────────────────────────────────┘");

        if (!empty($result['errors'])) {
            $this->newLine();
            $this->warn('Errors:');
            foreach ($result['errors'] as $error) {
                $this->error("  [{$error['test_code']}] {$error['message']}");
            }
        }
    }
}
