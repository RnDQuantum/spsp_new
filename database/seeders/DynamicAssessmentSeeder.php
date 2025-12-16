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
use App\Services\Assessment\AssessmentCalculationService;
use App\Services\Cache\AspectCacheService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DynamicAssessmentSeeder extends Seeder
{
    public function __construct(private readonly AssessmentCalculationService $assessmentService)
    {
    }

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
                'event' => [
                    'code' => 'P3K-KEJAKSAAN-2025',
                    'name' => 'Seleksi P3K Kejaksaan 2025',
                    'description' => 'Assessment P3K untuk Kejaksaan RI tahun 2025',
                    'year' => 2025,
                    'start_date' => '2025-09-01',
                    'end_date' => '2025-12-31',
                    'status' => 'completed',
                ],
                'batches' => [['code' => 'BATCH-1-MOJOKERTO', 'name' => 'Gelombang 1 - Mojokerto', 'location' => 'Mojokerto', 'batch_number' => 1, 'start_date' => '2025-09-27', 'end_date' => '2025-09-28'], ['code' => 'BATCH-2-SURABAYA', 'name' => 'Gelombang 2 - Surabaya', 'location' => 'Surabaya', 'batch_number' => 2, 'start_date' => '2025-10-15', 'end_date' => '2025-10-16'], ['code' => 'BATCH-3-JAKARTA', 'name' => 'Gelombang 3 - Jakarta', 'location' => 'Jakarta', 'batch_number' => 3, 'start_date' => '2025-11-05', 'end_date' => '2025-11-06']],
                'positions' => [['code' => 'fisikawan_medis', 'name' => 'Fisikawan Medis', 'quota' => 20, 'template_code' => 'professional_standard_v1'], ['code' => 'analis_kebijakan', 'name' => 'Analis Kebijakan', 'quota' => 30, 'template_code' => 'staff_standard_v1'], ['code' => 'auditor', 'name' => 'Auditor', 'quota' => 25, 'template_code' => 'supervisor_standard_v1'], ['code' => 'pranata_komputer', 'name' => 'Pranata Komputer', 'quota' => 25, 'template_code' => 'staff_standard_v1']],
                'participants_count' => 200, // JUMLAH PESERTA
                'performance_distribution' => [
                    'high' => 25, // 25% high performers (exceed standard)
                    'medium' => 60, // 60% medium performers (around standard)
                    'low' => 15, // 15% low performers (below standard)
                ],
            ],

            // Configuration 2: Kementerian Kesehatan - 200 participants
            [
                'institution_code' => 'kemenkes',
                'event' => [
                    'code' => 'P3K-KEMENKES-2025',
                    'name' => 'Seleksi P3K Kementerian Kesehatan 2025',
                    'description' => 'Assessment P3K untuk Kemenkes tahun 2025',
                    'year' => 2025,
                    'start_date' => '2025-10-01',
                    'end_date' => '2025-12-31',
                    'status' => 'ongoing',
                ],
                'batches' => [['code' => 'BATCH-1-BANDUNG', 'name' => 'Gelombang 1 - Bandung', 'location' => 'Bandung', 'batch_number' => 1, 'start_date' => '2025-10-10', 'end_date' => '2025-10-11'], ['code' => 'BATCH-2-YOGYAKARTA', 'name' => 'Gelombang 2 - Yogyakarta', 'location' => 'Yogyakarta', 'batch_number' => 2, 'start_date' => '2025-11-10', 'end_date' => '2025-11-11']],
                'positions' => [['code' => 'dokter_umum', 'name' => 'Dokter Umum', 'quota' => 50, 'template_code' => 'professional_standard_v1'], ['code' => 'perawat', 'name' => 'Perawat', 'quota' => 100, 'template_code' => 'staff_standard_v1'], ['code' => 'apoteker', 'name' => 'Apoteker', 'quota' => 50, 'template_code' => 'supervisor_standard_v1']],
                'participants_count' => 150, // JUMLAH PESERTA
                'performance_distribution' => [
                    'high' => 20, // 25% high performers (exceed standard)
                    'medium' => 10, // 60% medium performers (around standard)
                    'low' => 70, // 15% low performers (below standard)
                ],
            ],
        ];
    }

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset the participant factory counter at the start
        Participant::factory()::resetCounter();

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
            // 1. Get institution
            $institution = Institution::where('code', $config['institution_code'])->firstOrFail();

            $this->info("  📋 Institution: {$institution->name}");

            // 2. Create event (no template_id - template is per position now)
            $event = AssessmentEvent::create([
                'institution_id' => $institution->id,
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
            $this->info('  📦 Batches created: ' . count($batches));

            // 4. Create positions with their templates
            $positions = [];
            foreach ($config['positions'] as $positionData) {
                $positionTemplate = AssessmentTemplate::where('code', $positionData['template_code'])->firstOrFail();

                $position = PositionFormation::create([
                    'event_id' => $event->id,
                    'template_id' => $positionTemplate->id,
                    'code' => $positionData['code'],
                    'name' => $positionData['name'],
                    'quota' => $positionData['quota'],
                ]);

                // Eager load the template relationship immediately
                $position->load('template');
                $positions[] = $position;
            }
            $this->info('  💼 Positions created: ' . count($positions));

            // ⚡ CACHE: Pre-load all templates' categories & aspects once
            $templateIds = collect($positions)->pluck('template_id')->unique()->values();

            // Preload AspectCacheService for all templates
            foreach ($templateIds as $templateId) {
                AspectCacheService::preloadByTemplate($templateId);
            }

            $categoriesCache = CategoryType::whereIn('template_id', $templateIds)
                ->get()
                ->groupBy('template_id');

            $aspectsCache = Aspect::whereHas('categoryType', function ($query) use ($templateIds) {
                $query->whereIn('template_id', $templateIds);
            })
                ->with('subAspects')
                ->get()
                ->groupBy('category_type_id');

            $this->info('  🚀 Cached categories & aspects for performance');

            // 5. Generate participants with calculated assessments
            $this->info("  👥 Creating {$config['participants_count']} participants...");

            $progressBar = $this->command->getOutput()->createProgressBar($config['participants_count']);
            $progressBar->start();

            // ⚡ OPTIMIZATION: Process in larger chunks with periodic transaction commits
            $chunkSize = 50; // Reduced chunk size for faster commits
            $totalChunks = (int) ceil($config['participants_count'] / $chunkSize);

            for ($chunk = 0; $chunk < $totalChunks; $chunk++) {
                $currentChunkSize = min($chunkSize, $config['participants_count'] - ($chunk * $chunkSize));

                // ⚡ Use separate transaction per chunk to avoid memory buildup
                DB::transaction(function () use ($currentChunkSize, $batches, $positions, $event, $config, $progressBar, $categoriesCache, $aspectsCache) {
                    for ($i = 0; $i < $currentChunkSize; $i++) {
                        // Determine performance level based on distribution
                        $performanceLevel = $this->determinePerformanceLevel($config['performance_distribution']);

                        // Random batch & position
                        $batch = fake()->randomElement($batches);
                        $position = fake()->randomElement($positions);

                        // Create participant
                        $participant = Participant::factory()->forEvent($event)->forBatch($batch)->forPosition($position)->create();

                        // Get template from position (not from event!)
                        $template = $position->template;

                        // ⚡ Get categories from cache instead of query
                        $categories = $categoriesCache->get($template->id);
                        $potensiCategory = $categories->firstWhere('code', 'potensi');
                        $kompetensiCategory = $categories->firstWhere('code', 'kompetensi');

                        // Generate assessment data (RAW DATA like API)
                        $assessmentsData = $this->generateAssessmentsData($template, $potensiCategory, $kompetensiCategory, $performanceLevel, $aspectsCache);

                        // ⚡ Calculate assessments using SERVICE (without nested transaction)
                        $this->assessmentService->calculateParticipantWithoutTransaction($participant, $assessmentsData);

                        // Create psychological test
                        $psychTestFactory = PsychologicalTest::factory()->forParticipant($participant);

                        match ($performanceLevel) {
                            'high' => $psychTestFactory->highPerformance()->create(),
                            'medium' => $psychTestFactory->mediumPerformance()->create(),
                            'low' => $psychTestFactory->lowPerformance()->create(),
                        };

                        // Create interpretations
                        Interpretation::factory()->forParticipant($participant)->forCategoryType($potensiCategory)->potensi($performanceLevel)->create();

                        Interpretation::factory()->forParticipant($participant)->forCategoryType($kompetensiCategory)->kompetensi($performanceLevel)->create();

                        $progressBar->advance();
                    }
                });

                // ⚡ CRITICAL: Clear entity manager cache every chunk to prevent memory leak
                if ($chunk % 10 === 0) {
                    gc_collect_cycles(); // Force garbage collection
                }
            }

            $progressBar->finish();
            $this->info("\n  ✅ {$config['participants_count']} participants created successfully!");
        });
    }

    /**
     * Generate assessments data (RAW DATA like from API)
     */
    private function generateAssessmentsData(AssessmentTemplate $template, CategoryType $potensiCategory, CategoryType $kompetensiCategory, string $performanceLevel, $aspectsCache): array
    {
        // Base performance multiplier range by level
        [$minMultiplier, $maxMultiplier] = match ($performanceLevel) {
            'high' => [1.05, 1.25], // Exceed standard significantly (rating 3.15-5.00)
            'medium' => [0.85, 1.1], // Around standard (rating 2.55-4.40)
            'low' => [0.40, 0.75], // Below standard (rating 1.20-3.00, includes Kelas I & II)
        };

        $assessmentsData = [
            'potensi' => [],
            'kompetensi' => [],
        ];

        // ⚡ POTENSI: Get aspects from cache
        $potensiAspects = $aspectsCache->get($potensiCategory->id)?->sortBy('order') ?? collect();

        foreach ($potensiAspects as $aspect) {
            $subAspectsData = [];

            foreach ($aspect->subAspects as $subAspect) {
                // Add random variation per sub-aspect (±0.3 variance)
                $variance = fake()->randomFloat(2, -0.3, 0.3);
                $performanceMultiplier = fake()->randomFloat(2, $minMultiplier, $maxMultiplier);

                $baseRating = $subAspect->standard_rating * $performanceMultiplier + $variance;
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

        // ⚡ KOMPETENSI: Get aspects from cache
        $kompetensiAspects = $aspectsCache->get($kompetensiCategory->id)?->sortBy('order') ?? collect();

        foreach ($kompetensiAspects as $aspect) {
            // Add random variation per aspect (±0.3 variance)
            $variance = fake()->randomFloat(2, -0.3, 0.3);
            $performanceMultiplier = fake()->randomFloat(2, $minMultiplier, $maxMultiplier);

            $baseRating = $aspect->standard_rating * $performanceMultiplier + $variance;
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
