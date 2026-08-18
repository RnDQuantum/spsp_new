<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\AssessmentEvent;
use App\Models\Participant;
use App\Models\PositionFormation;
use Database\Seeders\Support\CareerHistoryGenerator;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * ParticipantCareerHistorySeeder - Seeder Riwayat Karier untuk Seluruh Peserta
 *
 * Mengisi tabel participant_career_histories dengan riwayat jabatan kronologis
 * realistis untuk seluruh peserta yang belum memiliki data riwayat karier.
 */
class ParticipantCareerHistorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🚀 Memulai seeding riwayat karier peserta...');

        $existingCount = DB::table('participant_career_histories')->count();
        if ($existingCount > 0) {
            $this->command->info("ℹ️  Terdapat {$existingCount} data riwayat karier yang sudah tersimpan.");
        }

        $events = AssessmentEvent::with('institution')->get()->keyBy('id');
        $positions = PositionFormation::all()->keyBy('id');

        $query = Participant::withoutGlobalScopes()
            ->whereNotExists(function ($sub) {
                $sub->select(DB::raw(1))
                    ->from('participant_career_histories')
                    ->whereColumn('participant_career_histories.participant_id', 'participants.id');
            });

        $totalToSeed = $query->count();

        if ($totalToSeed === 0) {
            $this->command->info('✅ Seluruh peserta sudah memiliki data riwayat karier.');

            return;
        }

        $this->command->info("🎯 Menemukan {$totalToSeed} peserta tanpa riwayat karier. Memproses dalam batch...");

        $progressBar = $this->command->getOutput()->createProgressBar($totalToSeed);
        $progressBar->start();

        $chunkSize = 500;
        $insertBatch = [];

        $query->chunk($chunkSize, function ($participants) use ($events, $positions, &$insertBatch, $progressBar) {
            foreach ($participants as $participant) {
                $event = $events->get($participant->event_id) ?? new AssessmentEvent;
                $position = $positions->get($participant->position_formation_id);

                $careers = CareerHistoryGenerator::generateForParticipant($participant, $event, $position);
                foreach ($careers as $career) {
                    $insertBatch[] = $career;
                }

                $progressBar->advance();
            }

            if (count($insertBatch) >= 1000) {
                DB::table('participant_career_histories')->insert($insertBatch);
                $insertBatch = [];
            }
        });

        if (! empty($insertBatch)) {
            DB::table('participant_career_histories')->insert($insertBatch);
        }

        $progressBar->finish();
        $this->command->info("\n✅ Seeding riwayat karier selesai!");
    }
}
