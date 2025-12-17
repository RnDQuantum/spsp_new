<?php

namespace Database\Seeders;

use App\Models\AssessmentEvent;
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

            // Create events
            foreach ($data['events'] as $eventData) {
                AssessmentEvent::firstOrCreate(
                    [
                        'institution_id' => $institution->id,
                        'code' => strtoupper($institution->code) . '-ASSESSMENT-' . $eventData['year'],
                    ],
                    [
                        'name' => 'Assessment ' . $institution->name . ' ' . $eventData['year'],
                        'description' => 'Assessment kebutuhan SDM untuk ' . $institution->name,
                        'year' => $eventData['year'],
                        'start_date' => $eventData['year'] . '-01-01',
                        'end_date' => $eventData['year'] . '-12-31',
                        'status' => $eventData['status'],
                    ]
                );
            }

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
                'api_key' => Str::random(32),
                'categories' => [
                    ['code' => 'kementerian', 'is_primary' => true],
                ],
            ],
            [
                'code' => 'bkn',
                'name' => 'Badan Kepegawaian Negara (BKN)',
                'api_key' => Str::random(32),
                'categories' => [
                    ['code' => 'kementerian', 'is_primary' => true],
                ],
            ],
            [
                'code' => 'kemendikbud',
                'name' => 'Kementerian Pendidikan dan Kebudayaan',
                'api_key' => Str::random(32),
                'categories' => [
                    ['code' => 'kementerian', 'is_primary' => true],
                    ['code' => 'pendidikan', 'is_primary' => false],
                ],
            ],
            [
                'code' => 'kemenkes',
                'name' => 'Kementerian Kesehatan',
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
