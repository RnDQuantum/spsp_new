<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Participant;
use Database\Seeders\Support\PerformanceRecordGenerator;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * ParticipantPerformanceRecordSeeder - Seeder Rekam Kinerja untuk Seluruh Peserta
 *
 * Mengisi tabel participant_performance_records dengan data tren KPI tahunan
 * dan breakdown metrik realistis untuk seluruh peserta yang belum memiliki data kinerja.
 */
class ParticipantPerformanceRecordSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🚀 Memulai seeding rekam jejak kinerja tahunan peserta...');

        $existingCount = DB::table('participant_performance_records')->count();
        if ($existingCount > 0) {
            $this->command->info("ℹ️  Terdapat {$existingCount} data rekam kinerja yang sudah tersimpan.");
        }

        $query = Participant::withoutGlobalScopes()
            ->with('finalAssessment')
            ->whereNotExists(function ($sub) {
                $sub->select(DB::raw(1))
                    ->from('participant_performance_records')
                    ->whereColumn('participant_performance_records.participant_id', 'participants.id');
            });

        $totalToSeed = $query->count();

        if ($totalToSeed === 0) {
            $this->command->info('✅ Seluruh peserta sudah memiliki data rekam kinerja.');

            return;
        }

        $this->command->info("🎯 Menemukan {$totalToSeed} peserta tanpa rekam kinerja. Memproses dalam batch...");

        $progressBar = $this->command->getOutput()->createProgressBar($totalToSeed);
        $progressBar->start();

        $chunkSize = 500;
        $insertBatch = [];

        $query->chunk($chunkSize, function ($participants) use (&$insertBatch, $progressBar) {
            foreach ($participants as $participant) {
                $records = PerformanceRecordGenerator::generateForParticipant($participant);
                foreach ($records as $record) {
                    $insertBatch[] = $record;
                }

                $progressBar->advance();
            }

            if (count($insertBatch) >= 1000) {
                DB::table('participant_performance_records')->insert($insertBatch);
                $insertBatch = [];
            }
        });

        if (! empty($insertBatch)) {
            DB::table('participant_performance_records')->insert($insertBatch);
        }

        $progressBar->finish();
        $this->command->info("\n✅ Seeding rekam jejak kinerja selesai!");
    }
}
