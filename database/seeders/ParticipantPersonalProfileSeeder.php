<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Participant;
use Database\Seeders\Support\PersonalProfileGenerator;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * ParticipantPersonalProfileSeeder - Seeder Profil Personal untuk Seluruh Peserta
 *
 * Mengisi tabel participant_personal_profiles dengan data profil pelengkap
 * realistis (Zodiak, Shio, Weton, Hobi, Olahraga, Medis) untuk seluruh peserta.
 */
class ParticipantPersonalProfileSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🚀 Memulai seeding profil personal peserta...');

        $existingCount = DB::table('participant_personal_profiles')->count();
        if ($existingCount > 0) {
            $this->command->info("ℹ️  Terdapat {$existingCount} data profil personal yang sudah tersimpan.");
        }

        $query = Participant::withoutGlobalScopes()
            ->whereNotExists(function ($sub) {
                $sub->select(DB::raw(1))
                    ->from('participant_personal_profiles')
                    ->whereColumn('participant_personal_profiles.participant_id', 'participants.id');
            });

        $totalToSeed = $query->count();

        if ($totalToSeed === 0) {
            $this->command->info('✅ Seluruh peserta sudah memiliki data profil personal.');

            return;
        }

        $this->command->info("🎯 Menemukan {$totalToSeed} peserta tanpa profil personal. Memproses dalam batch...");

        $progressBar = $this->command->getOutput()->createProgressBar($totalToSeed);
        $progressBar->start();

        $chunkSize = 500;
        $insertBatch = [];

        $query->chunk($chunkSize, function ($participants) use (&$insertBatch, $progressBar) {
            foreach ($participants as $participant) {
                $profile = PersonalProfileGenerator::generateForParticipant($participant);
                $insertBatch[] = $profile;
                $progressBar->advance();
            }

            if (count($insertBatch) >= 1000) {
                DB::table('participant_personal_profiles')->insert($insertBatch);
                $insertBatch = [];
            }
        });

        if (! empty($insertBatch)) {
            DB::table('participant_personal_profiles')->insert($insertBatch);
        }

        $progressBar->finish();
        $this->command->info("\n✅ Seeding profil personal selesai!");
    }
}
