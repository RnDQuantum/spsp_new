<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Institution;
use Illuminate\Support\Str;

class InstitutionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $institutions = [
            [
                'code' => 'INST001',
                'name' => 'Badan Kepegawaian Negara (BKN)',
                'logo_path' => null,
                'api_key' => Str::random(32),
            ],
            [
                'code' => 'INST002',
                'name' => 'Kementerian Pendidikan dan Kebudayaan',
                'logo_path' => null,
                'api_key' => Str::random(32),
            ],
            [
                'code' => 'INST003',
                'name' => 'Kementerian Kesehatan',
                'logo_path' => null,
                'api_key' => Str::random(32),
            ],
        ];

        foreach ($institutions as $institution) {
            Institution::create($institution);
        }
    }
}
