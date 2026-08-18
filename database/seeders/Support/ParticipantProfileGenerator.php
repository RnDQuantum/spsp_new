<?php

declare(strict_types=1);

namespace Database\Seeders\Support;

use App\Models\AssessmentEvent;
use App\Models\Batch;
use App\Models\PositionFormation;

/**
 * ParticipantProfileGenerator - Generator Profil & Identitas Peserta Asesmen
 *
 * Bertanggung jawab menghasilkan 18 atribut profil peserta terstandar SPSP
 * untuk keperluan bulk insert seeder.
 */
class ParticipantProfileGenerator
{
    private static int $participantCounter = 0;

    /**
     * Reset counter peserta.
     */
    public static function resetCounter(): void
    {
        self::$participantCounter = 0;
    }

    /**
     * Generate 1 record array data peserta untuk bulk insert.
     *
     * @return array<string, mixed>
     */
    public static function generate(
        AssessmentEvent $event,
        Batch $batch,
        PositionFormation $position
    ): array {
        self::$participantCounter++;

        $gender = fake()->randomElement(['L', 'P']);
        $firstName = $gender === 'L' ? fake()->firstNameMale() : fake()->firstNameFemale();
        $lastName = fake()->lastName();
        $gelarDepan = fake()->optional(0.2)->randomElement(['Drs.', 'Dr.', 'Ir.', 'H.']);
        $gelarBelakang = fake()->randomElement(['S.Si', 'S.T', 'S.Kom', 'S.E', 'S.H', 'S.Ak', 'S.Psi', 'S.Pd', 'S.Sos', 'M.Si', 'M.T']);
        $pendidikan = fake()->randomElement(['S1', 'S2', 'D3']);
        $agama = fake()->randomElement(['Islam', 'Kristen', 'Katolik', 'Hindu', 'Buddha']);
        $statusPerkawinan = fake()->randomElement(['Menikah', 'Belum Menikah']);
        $pangkat = fake()->randomElement(['Penata Muda', 'Penata', 'Penata Tingkat I', 'Pembina']);
        $golongan = fake()->randomElement(['III/a', 'III/b', 'III/c', 'III/d', 'IV/a']);
        $statusKepegawaian = fake()->randomElement(['PNS', 'CPNS', 'PPPK', 'BUMN', 'Swasta']);

        $username = self::generateUniqueUsername();
        $testNumber = self::generateUniqueTestNumber();
        $skbNumber = self::generateUniqueSkbNumber();
        $email = self::generateUniqueEmail();

        return [
            'event_id' => $event->id,
            'institution_id' => $event->institution_id,
            'batch_id' => $batch->id,
            'position_formation_id' => $position->id,
            'username' => $username,
            'test_number' => $testNumber,
            'skb_number' => $skbNumber,
            'name' => strtoupper($firstName.' '.$lastName).', '.$gelarBelakang,
            'tempat_lahir' => fake()->city(),
            'tanggal_lahir' => fake()->dateTimeBetween('-40 years', '-22 years')->format('Y-m-d'),
            'gelar_depan' => $gelarDepan,
            'gelar_belakang' => $gelarBelakang,
            'pendidikan' => $pendidikan,
            'agama' => $agama,
            'status_perkawinan' => $statusPerkawinan,
            'email' => $email,
            'phone' => fake()->numerify('08##########'),
            'gender' => $gender,
            'photo_path' => null,
            'assessment_date' => fake()->dateTimeBetween('-30 days', 'now')->format('Y-m-d'),
            'nik' => fake()->numerify('35#############'),
            'no_kjg' => fake()->numerify('24400240120######'),
            'jabatan_pelaksana' => strtoupper($position->name),
            'jbt_fungsional' => $position->name,
            'jbt_struktural' => '-',
            'pangkat' => $pangkat,
            'golongan' => $golongan,
            'status_kepegawaian' => $statusKepegawaian,
            'unit_kerja' => 'Instansi '.fake()->city(),
            'minat_penempatan' => $position->name,
            'pengalaman_kerja' => fake()->numberBetween(2, 12).' Tahun',
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    private static function generateUniqueUsername(): string
    {
        $letters = fake()->bothify('???');
        $numbers = str_pad((string) (self::$participantCounter % 100), 2, '0', STR_PAD_LEFT);
        $suffix = str_pad((string) ((int) (self::$participantCounter / 100)), 3, '0', STR_PAD_LEFT);

        return strtoupper($letters.$numbers.'-'.$suffix);
    }

    private static function generateUniqueTestNumber(): string
    {
        $prefix = fake()->numerify('##-#-#-##');
        $sequence = str_pad((string) self::$participantCounter, 5, '0', STR_PAD_LEFT);

        return $prefix.'-'.$sequence;
    }

    private static function generateUniqueSkbNumber(): string
    {
        $baseNumber = str_pad((string) self::$participantCounter, 5, '0', STR_PAD_LEFT);

        return '244002401200'.$baseNumber;
    }

    private static function generateUniqueEmail(): string
    {
        $providers = ['gmail.com', 'yahoo.com', 'hotmail.com', 'outlook.com'];
        $provider = fake()->randomElement($providers);

        return 'participant'.self::$participantCounter.'@'.$provider;
    }
}
