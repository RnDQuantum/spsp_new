<?php

namespace Database\Seeders;

use App\Models\Institution;
use App\Models\InstitutionCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class InstitutionSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('🏢 Seeding base institutions...');

        $categories = InstitutionCategory::all()->keyBy('code');

        $institutions = [
            [
                'code' => 'kejaksaan',
                'name' => 'Kejaksaan Agung RI',
                'logo_path' => null,
                'api_key' => Str::random(32),
                'categories' => [
                    ['code' => 'kementerian', 'is_primary' => true],
                ],
            ],
            [
                'code' => 'bkn',
                'name' => 'Badan Kepegawaian Negara (BKN)',
                'logo_path' => null,
                'api_key' => Str::random(32),
                'categories' => [
                    ['code' => 'kementerian', 'is_primary' => true],
                ],
            ],
            [
                'code' => 'kemendikbud',
                'name' => 'Kementerian Pendidikan dan Kebudayaan',
                'logo_path' => null,
                'api_key' => Str::random(32),
                'categories' => [
                    ['code' => 'kementerian', 'is_primary' => true],
                    ['code' => 'pendidikan', 'is_primary' => false],
                ],
            ],
            [
                'code' => 'kemenkes',
                'name' => 'Kementerian Kesehatan',
                'logo_path' => null,
                'api_key' => Str::random(32),
                'categories' => [
                    ['code' => 'kementerian', 'is_primary' => true],
                    ['code' => 'kesehatan', 'is_primary' => false],
                ],
            ],
        ];

        foreach ($institutions as $data) {
            $institution = Institution::firstOrCreate(
                ['code' => $data['code']],
                [
                    'name' => $data['name'],
                    'logo_path' => $data['logo_path'],
                    'api_key' => $data['api_key'],
                ]
            );

            // Attach categories if they exist
            if (isset($data['categories'])) {
                foreach ($data['categories'] as $catData) {
                    $category = $categories->get($catData['code']);
                    if ($category) {
                        $institution->categories()->syncWithoutDetaching([
                            $category->id => ['is_primary' => $catData['is_primary']],
                        ]);
                    }
                }
            }

            $this->command->info("  ✓ {$institution->name}");
        }

        $this->command->info('✅ Base institutions seeded successfully!');
    }
}
