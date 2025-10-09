<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Aspect;
use App\Models\AssessmentEvent;
use App\Models\AssessmentTemplate;
use App\Models\Batch;
use App\Models\CategoryType;
use App\Models\Institution;
use App\Models\Interpretation;
use App\Models\Participant;
use App\Models\PositionFormation;
use App\Models\PsychologicalTest;
use App\Models\SubAspect;
use App\Services\Assessment\AssessmentCalculationService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DynamicAssessmentSeeder extends Seeder
{
    public function __construct(
        private readonly AssessmentCalculationService $assessmentService
    ) {}

    /**
     * KONFIGURASI SEEDER DINAMIS
     *
     * Edit konfigurasi di sini untuk generate data berbeda
     */
    private function getSeederConfigurations(): array
    {
        return [
            // Configuration 1: Kejaksaan - 100 participants
            [
                'institution_code' => 'kejaksaan',
                'template_code' => 'p3k_standard_2025',
                'event' => [
                    'code' => 'P3K-KEJAKSAAN-2025',
                    'name' => 'Seleksi P3K Kejaksaan 2025',
                    'description' => 'Assessment P3K untuk Kejaksaan RI tahun 2025',
                    'year' => 2025,
                    'start_date' => '2025-09-01',
                    'end_date' => '2025-12-31',
                    'status' => 'completed',
                ],
                'batches' => [
                    ['code' => 'BATCH-1-MOJOKERTO', 'name' => 'Gelombang 1 - Mojokerto', 'location' => 'Mojokerto', 'batch_number' => 1, 'start_date' => '2025-09-27', 'end_date' => '2025-09-28'],
                    ['code' => 'BATCH-2-SURABAYA', 'name' => 'Gelombang 2 - Surabaya', 'location' => 'Surabaya', 'batch_number' => 2, 'start_date' => '2025-10-15', 'end_date' => '2025-10-16'],
                    ['code' => 'BATCH-3-JAKARTA', 'name' => 'Gelombang 3 - Jakarta', 'location' => 'Jakarta', 'batch_number' => 3, 'start_date' => '2025-11-05', 'end_date' => '2025-11-06'],
                ],
                'positions' => [
                    ['code' => 'fisikawan_medis', 'name' => 'Fisikawan Medis', 'quota' => 20],
                    ['code' => 'analis_kebijakan', 'name' => 'Analis Kebijakan', 'quota' => 30],
                    ['code' => 'auditor', 'name' => 'Auditor', 'quota' => 25],
                    ['code' => 'pranata_komputer', 'name' => 'Pranata Komputer', 'quota' => 25],
                ],
                'participants_count' => 100, // JUMLAH PESERTA
                'performance_distribution' => [
                    'high' => 30,   // 30% high performers (>= 100%)
                    'medium' => 50, // 50% medium performers (85-100%)
                    'low' => 20,    // 20% low performers (< 85%)
                ],
            ],

            // Configuration 2: Uncomment untuk tambah event kedua
            // [
            //     'institution_code' => 'kementerian_kesehatan',
            //     'template_code' => 'p3k_standard_2025',
            //     'event' => [
            //         'code' => 'P3K-KEMENKES-2025',
            //         'name' => 'Seleksi P3K Kementerian Kesehatan 2025',
            //         'description' => 'Assessment P3K untuk Kemenkes tahun 2025',
            //         'year' => 2025,
            //         'start_date' => '2025-10-01',
            //         'end_date' => '2025-12-31',
            //         'status' => 'ongoing',
            //     ],
            //     'batches' => [
            //         ['code' => 'BATCH-1-BANDUNG', 'name' => 'Gelombang 1 - Bandung', 'location' => 'Bandung', 'batch_number' => 1, 'start_date' => '2025-10-10', 'end_date' => '2025-10-11'],
            //         ['code' => 'BATCH-2-YOGYAKARTA', 'name' => 'Gelombang 2 - Yogyakarta', 'location' => 'Yogyakarta', 'batch_number' => 2, 'start_date' => '2025-11-10', 'end_date' => '2025-11-11'],
            //     ],
            //     'positions' => [
            //         ['code' => 'dokter_umum', 'name' => 'Dokter Umum', 'quota' => 50],
            //         ['code' => 'perawat', 'name' => 'Perawat', 'quota' => 100],
            //         ['code' => 'apoteker', 'name' => 'Apoteker', 'quota' => 50],
            //     ],
            //     'participants_count' => 200,
            //     'performance_distribution' => [
            //         'high' => 25,
            //         'medium' => 60,
            //         'low' => 15,
            //     ],
            // ],
        ];
    }

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $configurations = $this->getSeederConfigurations();

        foreach ($configurations as $config) {
            $this->info("\n🎯 Creating event: {$config['event']['name']}");
            $this->seedEvent($config);
        }

        $this->info("\n✅ All events seeded successfully!");
    }

    /**
     * Seed single event with all participants
     */
    private function seedEvent(array $config): void
    {
        DB::transaction(function () use ($config) {
            // 1. Get institution & template
            $institution = Institution::where('code', $config['institution_code'])->firstOrFail();
            $template = AssessmentTemplate::where('code', $config['template_code'])->firstOrFail();

            $this->info("  📋 Institution: {$institution->name}");
            $this->info("  📝 Template: {$template->name}");

            // 2. Create event
            $event = AssessmentEvent::create([
                'institution_id' => $institution->id,
                'template_id' => $template->id,
                ...$config['event'],
            ]);

            $this->info("  📅 Event created: {$event->name}");

            // 3. Create batches
            $batches = [];
            foreach ($config['batches'] as $batchData) {
                $batches[] = Batch::create([
                    'event_id' => $event->id,
                    ...$batchData,
                ]);
            }
            $this->info("  📦 Batches created: ".count($batches));

            // 4. Create positions
            $positions = [];
            foreach ($config['positions'] as $positionData) {
                $positions[] = PositionFormation::create([
                    'event_id' => $event->id,
                    ...$positionData,
                ]);
            }
            $this->info("  💼 Positions created: ".count($positions));

            // 5. Get category types
            $potensiCategory = CategoryType::where('template_id', $template->id)
                ->where('code', 'potensi')
                ->firstOrFail();

            $kompetensiCategory = CategoryType::where('template_id', $template->id)
                ->where('code', 'kompetensi')
                ->firstOrFail();

            // 6. Generate participants with calculated assessments
            $this->info("  👥 Creating {$config['participants_count']} participants...");

            $progressBar = $this->command->getOutput()->createProgressBar($config['participants_count']);
            $progressBar->start();

            for ($i = 0; $i < $config['participants_count']; $i++) {
                // Determine performance level based on distribution
                $performanceLevel = $this->determinePerformanceLevel($config['performance_distribution']);

                // Random batch & position
                $batch = fake()->randomElement($batches);
                $position = fake()->randomElement($positions);

                // Create participant
                $participant = Participant::factory()
                    ->forEvent($event)
                    ->forBatch($batch)
                    ->forPosition($position)
                    ->create();

                // Generate assessment data (RAW DATA like API)
                $assessmentsData = $this->generateAssessmentsData(
                    $template,
                    $potensiCategory,
                    $kompetensiCategory,
                    $performanceLevel
                );

                // Calculate assessments using SERVICE
                $this->assessmentService->calculateParticipant($participant, $assessmentsData);

                // Create psychological test
                $psychTestFactory = PsychologicalTest::factory()
                    ->forParticipant($participant);

                match ($performanceLevel) {
                    'high' => $psychTestFactory->highPerformance()->create(),
                    'medium' => $psychTestFactory->mediumPerformance()->create(),
                    'low' => $psychTestFactory->lowPerformance()->create(),
                };

                // Create interpretations
                Interpretation::factory()
                    ->forParticipant($participant)
                    ->forCategoryType($potensiCategory)
                    ->potensi($performanceLevel)
                    ->create();

                Interpretation::factory()
                    ->forParticipant($participant)
                    ->forCategoryType($kompetensiCategory)
                    ->kompetensi($performanceLevel)
                    ->create();

                $progressBar->advance();
            }

            $progressBar->finish();
            $this->info("\n  ✅ {$config['participants_count']} participants created successfully!");
        });
    }

    /**
     * Generate assessments data (RAW DATA like from API)
     */
    private function generateAssessmentsData(
        AssessmentTemplate $template,
        CategoryType $potensiCategory,
        CategoryType $kompetensiCategory,
        string $performanceLevel
    ): array {
        $performanceMultiplier = match ($performanceLevel) {
            'high' => fake()->randomFloat(2, 1.05, 1.15),   // 105% - 115%
            'medium' => fake()->randomFloat(2, 0.90, 1.05), // 90% - 105%
            'low' => fake()->randomFloat(2, 0.75, 0.90),    // 75% - 90%
        };

        $assessmentsData = [
            'potensi' => [],
            'kompetensi' => [],
        ];

        // POTENSI: Generate sub-aspect ratings (API sends INTEGER 1-5)
        $potensiAspects = Aspect::where('category_type_id', $potensiCategory->id)
            ->with('subAspects')
            ->orderBy('order')
            ->get();

        foreach ($potensiAspects as $aspect) {
            $subAspectsData = [];

            foreach ($aspect->subAspects as $subAspect) {
                $baseRating = $subAspect->standard_rating * $performanceMultiplier;
                $individualRating = (int) max(1, min(5, round($baseRating)));

                $subAspectsData[] = [
                    'sub_aspect_code' => $subAspect->code,
                    'individual_rating' => $individualRating, // INTEGER 1-5
                ];
            }

            $assessmentsData['potensi'][] = [
                'aspect_code' => $aspect->code,
                'sub_aspects' => $subAspectsData,
            ];
        }

        // KOMPETENSI: Generate aspect ratings (API sends INTEGER 1-5)
        $kompetensiAspects = Aspect::where('category_type_id', $kompetensiCategory->id)
            ->orderBy('order')
            ->get();

        foreach ($kompetensiAspects as $aspect) {
            $baseRating = $aspect->standard_rating * $performanceMultiplier;
            $individualRating = (int) max(1, min(5, round($baseRating)));

            $assessmentsData['kompetensi'][] = [
                'aspect_code' => $aspect->code,
                'individual_rating' => $individualRating, // INTEGER 1-5
            ];
        }

        return $assessmentsData;
    }

    /**
     * Determine performance level based on distribution
     */
    private function determinePerformanceLevel(array $distribution): string
    {
        $random = fake()->numberBetween(1, 100);

        $highThreshold = $distribution['high'];
        $mediumThreshold = $highThreshold + $distribution['medium'];

        if ($random <= $highThreshold) {
            return 'high';
        } elseif ($random <= $mediumThreshold) {
            return 'medium';
        } else {
            return 'low';
        }
    }

    /**
     * Output info message
     */
    private function info(string $message): void
    {
        $this->command->info($message);
    }
}
