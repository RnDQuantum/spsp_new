<?php

namespace Database\Seeders;

use App\Models\Institution;
use App\Models\InstitutionCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class InstitutionWithCategoriesSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('🏢 Seeding institutions with categories...');

        $categories = InstitutionCategory::all()->keyBy('code');

        // Create base institutions first
        $this->createBaseInstitutions($categories);

        $institutions = [
            [
                'institution' => [
                    'code' => 'telkom',
                    'name' => 'PT. Telkom Indonesia',
                    'api_key' => Str::random(32),
                ],
                'categories' => [
                    ['code' => 'bumn', 'is_primary' => true],
                    ['code' => 'teknologi', 'is_primary' => false],
                    ['code' => 'keuangan', 'is_primary' => false],
                ],
                'events' => [
                    ['year' => 2025, 'status' => 'ongoing'],
                    ['year' => 2024, 'status' => 'completed'],
                ],
            ],
            [
                'institution' => [
                    'code' => 'mandiri',
                    'name' => 'PT. Bank Mandiri',
                    'api_key' => Str::random(32),
                ],
                'categories' => [
                    ['code' => 'bumn', 'is_primary' => true],
                    ['code' => 'keuangan', 'is_primary' => false],
                ],
                'events' => [
                    ['year' => 2025, 'status' => 'ongoing'],
                ],
            ],
            [
                'institution' => [
                    'code' => 'pertamina',
                    'name' => 'PT. Pertamina',
                    'api_key' => Str::random(32),
                ],
                'categories' => [
                    ['code' => 'bumn', 'is_primary' => true],
                ],
                'events' => [
                    ['year' => 2025, 'status' => 'ongoing'],
                    ['year' => 2024, 'status' => 'completed'],
                ],
            ],
            [
                'institution' => [
                    'code' => 'ui',
                    'name' => 'Universitas Indonesia',
                    'api_key' => Str::random(32),
                ],
                'categories' => [
                    ['code' => 'pendidikan', 'is_primary' => true],
                ],
                'events' => [
                    ['year' => 2025, 'status' => 'draft'],
                ],
            ],
            [
                'institution' => [
                    'code' => 'ugm',
                    'name' => 'Universitas Gadjah Mada',
                    'api_key' => Str::random(32),
                ],
                'categories' => [
                    ['code' => 'pendidikan', 'is_primary' => true],
                ],
                'events' => [
                    ['year' => 2025, 'status' => 'draft'],
                    ['year' => 2024, 'status' => 'completed'],
                ],
            ],
            [
                'institution' => [
                    'code' => 'itb',
                    'name' => 'Institut Teknologi Bandung',
                    'api_key' => Str::random(32),
                ],
                'categories' => [
                    ['code' => 'pendidikan', 'is_primary' => true],
                    ['code' => 'teknologi', 'is_primary' => false],
                ],
                'events' => [
                    ['year' => 2024, 'status' => 'completed'],
                ],
            ],
            [
                'institution' => [
                    'code' => 'unilever',
                    'name' => 'PT. Unilever Indonesia',
                    'api_key' => Str::random(32),
                ],
                'categories' => [
                    ['code' => 'swasta', 'is_primary' => true],
                ],
                'events' => [
                    ['year' => 2025, 'status' => 'ongoing'],
                ],
            ],
            [
                'institution' => [
                    'code' => 'gojek',
                    'name' => 'PT. Gojek Indonesia',
                    'api_key' => Str::random(32),
                ],
                'categories' => [
                    ['code' => 'swasta', 'is_primary' => true],
                    ['code' => 'teknologi', 'is_primary' => false],
                ],
                'events' => [
                    ['year' => 2025, 'status' => 'ongoing'],
                    ['year' => 2024, 'status' => 'completed'],
                ],
            ],
            [
                'institution' => [
                    'code' => 'tokopedia',
                    'name' => 'PT. Tokopedia',
                    'api_key' => Str::random(32),
                ],
                'categories' => [
                    ['code' => 'swasta', 'is_primary' => true],
                    ['code' => 'teknologi', 'is_primary' => false],
                ],
                'events' => [
                    ['year' => 2025, 'status' => 'draft'],
                ],
            ],
            [
                'institution' => [
                    'code' => 'rscm',
                    'name' => 'RSUP Dr. Cipto Mangunkusumo',
                    'api_key' => Str::random(32),
                ],
                'categories' => [
                    ['code' => 'kesehatan', 'is_primary' => true],
                ],
                'events' => [
                    ['year' => 2025, 'status' => 'ongoing'],
                ],
            ],
        ];

        // Create new institutions with categories
        foreach ($institutions as $data) {
            $institution = Institution::firstOrCreate(
                ['code' => $data['institution']['code']],
                $data['institution']
            );

            // Attach categories
            foreach ($data['categories'] as $catData) {
                $category = $categories->get($catData['code']);
                if ($category) {
                    $institution->categories()->syncWithoutDetaching([
                        $category->id => ['is_primary' => $catData['is_primary']],
                    ]);
                }
            }

            // Note: Events are now created by DynamicAssessmentSeeder with participants
            // The 'events' key in data array above is kept for reference only

            $this->command->info("  ✓ {$institution->name}");
        }

        $this->command->info('✅ Institutions with categories seeded successfully!');
    }

    private function createBaseInstitutions($categories): void
    {
        $this->command->info('  Creating base institutions...');

        $baseInstitutions = [
            [
                'code' => 'kejaksaan',
                'name' => 'Kejaksaan Agung RI',
                'address' => 'Jl. Sultan Hasanuddin No. 1, Kebayoran Baru, Jakarta Selatan',
                'phone' => '021-7221337',
                'pic_name' => 'Bambang Sugeng, S.H., M.H.',
                'pic_phone' => '081299887766',
                'api_key' => Str::random(32),
                'categories' => [
                    ['code' => 'kementerian', 'is_primary' => true],
                ],
            ],
            [
                'code' => 'bkn',
                'name' => 'Badan Kepegawaian Negara (BKN)',
                'address' => 'Jl. Mayor Jendral Sutoyo No. 12, Cililitan, Jakarta Timur',
                'phone' => '021-8093008',
                'pic_name' => 'Dr. Ir. H. Suharmen, S.Kom., M.Si.',
                'pic_phone' => '081122334455',
                'api_key' => Str::random(32),
                'categories' => [
                    ['code' => 'kementerian', 'is_primary' => true],
                ],
            ],
            [
                'code' => 'kemendikbud',
                'name' => 'Kementerian Pendidikan dan Kebudayaan',
                'address' => 'Jl. Jenderal Sudirman, Senayan, Jakarta Pusat',
                'phone' => '021-5703303',
                'pic_name' => 'Dra. Sri Wahyuni, M.Ed.',
                'pic_phone' => '081344556677',
                'api_key' => Str::random(32),
                'categories' => [
                    ['code' => 'kementerian', 'is_primary' => true],
                    ['code' => 'pendidikan', 'is_primary' => false],
                ],
            ],
            [
                'code' => 'kemenkes',
                'name' => 'Kementerian Kesehatan',
                'address' => 'Jl. H.R. Rasuna Said Blok X-5 Kav. 4-9, Kuningan, Jakarta Selatan',
                'phone' => '021-5201590',
                'pic_name' => 'drg. Arianti Anaya, MKM',
                'pic_phone' => '081566778899',
                'api_key' => Str::random(32),
                'categories' => [
                    ['code' => 'kementerian', 'is_primary' => true],
                    ['code' => 'kesehatan', 'is_primary' => false],
                ],
            ],
        ];

        foreach ($baseInstitutions as $data) {
            $institution = Institution::firstOrCreate(
                ['code' => $data['code']],
                [
                    'name' => $data['name'],
                    'address' => $data['address'],
                    'phone' => $data['phone'],
                    'pic_name' => $data['pic_name'],
                    'pic_phone' => $data['pic_phone'],
                    'api_key' => $data['api_key'],
                ]
            );

            // Attach categories
            foreach ($data['categories'] as $catData) {
                $category = $categories->get($catData['code']);
                if ($category) {
                    $institution->categories()->syncWithoutDetaching([
                        $category->id => ['is_primary' => $catData['is_primary']],
                    ]);
                }
            }

            $this->command->info("  ✓ {$institution->name}");
        }
    }
}
