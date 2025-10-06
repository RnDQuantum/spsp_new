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
                'code' => 'kejaksaan',
                'name' => 'Kejaksaan Agung RI',
                'logo_path' => null,
                'api_key' => Str::random(32),
            ],
            [
                'code' => 'bkn',
                'name' => 'Badan Kepegawaian Negara (BKN)',
                'logo_path' => null,
                'api_key' => Str::random(32),
            ],
            [
                'code' => 'kemendikbud',
                'name' => 'Kementerian Pendidikan dan Kebudayaan',
                'logo_path' => null,
                'api_key' => Str::random(32),
            ],
            [
                'code' => 'kemenkes',
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
