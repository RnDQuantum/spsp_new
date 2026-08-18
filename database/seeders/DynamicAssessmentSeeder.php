<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Aspect;
use App\Models\AspectAssessment;
use App\Models\AssessmentEvent;
use App\Models\AssessmentTemplate;
use App\Models\Batch;
use App\Models\CategoryAssessment;
use App\Models\CategoryType;
use App\Models\FinalAssessment;
use App\Models\Institution;
use App\Models\Interpretation;
use App\Models\Mmpi;
use App\Models\Participant;
use App\Models\PositionFormation;
use App\Models\Project;
use App\Models\SubAspectAssessment;
use App\Models\TestResult;
use App\Services\Cache\AspectCacheService;
use Database\Seeders\Data\AssessmentEventConfig;
use Database\Seeders\Support\AssessmentRecordGenerator;
use Database\Seeders\Support\CareerHistoryGenerator;
use Database\Seeders\Support\ParticipantProfileGenerator;
use Database\Seeders\Support\PerformanceRecordGenerator;
use Database\Seeders\Support\PersonalProfileGenerator;
use Database\Seeders\Support\TestResultGenerator;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * DynamicAssessmentSeeder - Orchestrator Seeding Data Asesmen & Riwayat Tes SPSP
 *
 * Mengoordinasikan pembentukan Proyek, Event, Batch, Formasi Jabatan, Peserta,
 * Kalkulasi Rating Potensi & Kompetensi, MMPI, serta Riwayat Tes Lengkap (test_results).
 * Menggunakan arsitektur modular & performa tinggi Bulk-Insert Total (~180 p/s).
 */
class DynamicAssessmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        ParticipantProfileGenerator::resetCounter();

        $configurations = AssessmentEventConfig::getConfigurations();

        foreach ($configurations as $config) {
            $this->info("\n🎯 Creating event: {$config['event']['name']}");
            $this->seedEvent($config);
        }

        $this->info("\n✅ All events seeded successfully!");
    }

    /**
     * Seed single event with all participants (OPTIMIZED)
     */
    private function seedEvent(array $config): void
    {
        DB::transaction(function () use ($config) {
            // 1. Get institution
            $institution = Institution::where('code', $config['institution_code'])->firstOrFail();
            $this->info("  📋 Institution: {$institution->name}");

            // 2. Create or get Master Project (AP-XXX)
            $projectCode = $config['project_code'] ?? 'AP-'.str_pad((string) rand(85, 999), 3, '0', STR_PAD_LEFT);
            $project = Project::firstOrCreate(
                ['code' => $projectCode],
                [
                    'institution_id' => $institution->id,
                    'name' => "Master Proyek Assessment {$institution->name} ({$projectCode})",
                    'year' => $config['event']['year'] ?? date('Y'),
                    'contract_number' => 'QHI'.rand(1000, 9999).'-MR-'.rand(100, 999),
                    'pic_name' => 'Technical Coordinator',
                    'pic_phone' => '081234567890',
                    'project_type' => 'Seleksi & Pemetaan',
                    'status' => 'completed',
                ]
            );

            // 3. Create event with project_id link
            $event = AssessmentEvent::create([
                'institution_id' => $institution->id,
                'project_id' => $project->id,
                'location' => $config['batches'][0]['location'] ?? 'Pusat',
                'target_participants' => $config['participants_count'] ?? 100,
                'assessment_type' => 'Psikotes & Kompetensi',
                ...$config['event'],
            ]);
            $this->info("  📅 Event created: {$event->name}");

            // 4. Create batches
            $batches = [];
            foreach ($config['batches'] as $batchData) {
                $batches[] = Batch::create([
                    'event_id' => $event->id,
                    'institution_id' => $institution->id,
                    ...$batchData,
                ]);
            }
            $this->info('  📦 Batches created: '.count($batches));

            // 5. Create positions with their templates
            $positions = [];
            foreach ($config['positions'] as $positionData) {
                $positionTemplate = AssessmentTemplate::where('code', $positionData['template_code'])->firstOrFail();

                $position = PositionFormation::create([
                    'event_id' => $event->id,
                    'institution_id' => $institution->id,
                    'template_id' => $positionTemplate->id,
                    'code' => $positionData['code'],
                    'name' => $positionData['name'],
                    'quota' => $positionData['quota'],
                ]);

                $position->load('template');
                $positions[] = $position;
            }
            $this->info('  💼 Positions created: '.count($positions));

            // 6. Preload cache for categories & aspects
            $templateIds = collect($positions)->pluck('template_id')->unique()->values();

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

            // 7. Generate participants with calculated assessments & test results
            $this->seedParticipantsOptimized($event, $batches, $positions, $config, $categoriesCache, $aspectsCache);
        });
    }

    /**
     * ⚡ OPTIMIZED: Seed participants in batches with bulk inserts
     */
    private function seedParticipantsOptimized(
        AssessmentEvent $event,
        array $batches,
        array $positions,
        array $config,
        $categoriesCache,
        $aspectsCache
    ): void {
        $totalParticipants = $config['participants_count'];

        $chunkSize = match (true) {
            $totalParticipants < 500 => 250,
            default => 500
        };

        $totalChunks = (int) ceil($totalParticipants / $chunkSize);

        $this->info("  👥 Creating {$totalParticipants} participants in {$totalChunks} batches");
        $this->info("  📊 Each batch processes ~{$chunkSize} participants\n");

        $startTime = microtime(true);
        $processedTotal = 0;

        for ($chunkIndex = 0; $chunkIndex < $totalChunks; $chunkIndex++) {
            $currentChunkSize = min($chunkSize, $totalParticipants - ($chunkIndex * $chunkSize));
            $chunkNumber = $chunkIndex + 1;

            $this->info("     Batch {$chunkNumber}/{$totalChunks}: Processing {$currentChunkSize} participants...");
            $chunkProgressBar = $this->command->getOutput()->createProgressBar($currentChunkSize);
            $chunkProgressBar->start();

            DB::beginTransaction();
            try {
                $chunkStartTime = microtime(true);

                $this->processParticipantChunk(
                    $currentChunkSize,
                    $event,
                    $batches,
                    $positions,
                    $config,
                    $categoriesCache,
                    $aspectsCache,
                    $chunkProgressBar
                );

                DB::commit();

                $this->clearEloquentMemory();

                $chunkProgressBar->finish();
                $chunkDuration = microtime(true) - $chunkStartTime;
                $processedTotal += $currentChunkSize;
                $overallProgress = round(($processedTotal / $totalParticipants) * 100, 1);
                $avgSpeed = $processedTotal / (microtime(true) - $startTime);

                $this->info("\n     ✓ Batch {$chunkNumber} completed in ".number_format($chunkDuration, 2).'s');
                $this->info("     📈 Overall: {$processedTotal}/{$totalParticipants} ({$overallProgress}%) | Speed: ".number_format($avgSpeed, 1)." p/s\n");

                if ($chunkIndex % 5 === 0 && $chunkIndex > 0) {
                    gc_collect_cycles();
                    $memoryMB = round(memory_get_usage(true) / 1024 / 1024, 2);
                    $this->info("     🗑️  Memory cleanup | Current: {$memoryMB} MB\n");
                }
            } catch (\Throwable $e) {
                DB::rollBack();
                $chunkProgressBar->finish();
                throw $e;
            }
        }

        $totalDuration = microtime(true) - $startTime;
        $avgSpeed = $totalParticipants / max(0.001, $totalDuration);

        $this->info("  ✅ {$totalParticipants} participants created successfully!");
        $this->info('  ⏱️  Total time: '.number_format($totalDuration, 2).'s | Average: '.number_format($avgSpeed, 1).' participants/second');
    }

    /**
     * ⚡ Process single chunk of participants with batch inserts
     */
    private function processParticipantChunk(
        int $chunkSize,
        AssessmentEvent $event,
        array $batches,
        array $positions,
        array $config,
        $categoriesCache,
        $aspectsCache,
        $progressBar
    ): void {
        $participantsData = [];
        $psychTestsData = [];
        $interpretationsData = [];
        $categoryAssessmentsData = [];
        $aspectAssessmentsData = [];
        $subAspectAssessmentsData = [];
        $finalAssessmentsData = [];
        $testResultsData = [];
        $careerHistoriesData = [];
        $performanceRecordsData = [];
        $personalProfilesData = [];

        // 1. Generate participants data
        for ($i = 0; $i < $chunkSize; $i++) {
            $batch = fake()->randomElement($batches);
            $position = fake()->randomElement($positions);

            $participantsData[] = ParticipantProfileGenerator::generate($event, $batch, $position);
        }

        // 2. Bulk insert participants (max 200 rows per query to avoid placeholder limit)
        foreach (array_chunk($participantsData, 200) as $pChunk) {
            DB::table('participants')->insert($pChunk);
        }

        // 3. Get inserted participants
        $insertedParticipants = Participant::where('event_id', $event->id)
            ->whereIn('username', collect($participantsData)->pluck('username'))
            ->get()
            ->keyBy('username');

        // 4. Generate all assessment records & test results
        $categoryIdCounter = DB::table('category_assessments')->max('id') ?? 0;
        $aspectIdCounter = DB::table('aspect_assessments')->max('id') ?? 0;
        $subAspectIdCounter = DB::table('sub_aspect_assessments')->max('id') ?? 0;

        foreach ($participantsData as $pData) {
            $participant = $insertedParticipants->get($pData['username']);
            if (! $participant) {
                continue;
            }

            $boxCategory = AssessmentRecordGenerator::determineNineBoxCategory($config['nine_box_distribution']);

            $position = collect($positions)->firstWhere('id', $participant->position_formation_id);
            $template = $position->template;

            $categories = $categoriesCache->get($template->id);
            $potensiCategory = $categories->firstWhere('code', 'potensi');
            $kompetensiCategory = $categories->firstWhere('code', 'kompetensi');

            $assessmentsData = AssessmentRecordGenerator::generateAssessmentsDataForNineBox(
                $template,
                $potensiCategory,
                $kompetensiCategory,
                $boxCategory,
                $aspectsCache
            );

            AssessmentRecordGenerator::generateAssessmentRecords(
                $participant,
                $template,
                $assessmentsData,
                $categoriesCache,
                $aspectsCache,
                $categoryIdCounter,
                $aspectIdCounter,
                $subAspectIdCounter,
                $categoryAssessmentsData,
                $aspectAssessmentsData,
                $subAspectAssessmentsData,
                $finalAssessmentsData
            );

            // Psychological MMPI data
            $psychTestsData[] = AssessmentRecordGenerator::generateMmpiData($participant, $boxCategory);

            // Interpretations data
            $interpretationsData[] = AssessmentRecordGenerator::generateInterpretationData($participant, $potensiCategory, $boxCategory, 'potensi');
            $interpretationsData[] = AssessmentRecordGenerator::generateInterpretationData($participant, $kompetensiCategory, $boxCategory, 'kompetensi');

            // 🚀 NEW: Generate authentic test results (test_results)
            $participantTestResults = TestResultGenerator::generateForParticipant($participant, $event, $boxCategory);
            foreach ($participantTestResults as $trRecord) {
                $testResultsData[] = $trRecord;
            }

            // 🚀 NEW: Generate authentic career histories (participant_career_histories)
            $participantCareers = CareerHistoryGenerator::generateForParticipant($participant, $event, $position);
            foreach ($participantCareers as $careerRecord) {
                $careerHistoriesData[] = $careerRecord;
            }

            // 🚀 NEW: Generate authentic performance records (participant_performance_records)
            $participantPerf = PerformanceRecordGenerator::generateForParticipant($participant);
            foreach ($participantPerf as $perfRecord) {
                $performanceRecordsData[] = $perfRecord;
            }

            // 🚀 NEW: Generate authentic personal profiles (participant_personal_profiles)
            $personalProfilesData[] = PersonalProfileGenerator::generateForParticipant($participant);

            $progressBar->advance();
        }

        // 5. BULK INSERT all assessment records in chunks of 1000
        $insertChunkSize = 1000;

        if (! empty($categoryAssessmentsData)) {
            foreach (array_chunk($categoryAssessmentsData, $insertChunkSize) as $chunk) {
                DB::table('category_assessments')->insert($chunk);
            }
        }
        if (! empty($aspectAssessmentsData)) {
            foreach (array_chunk($aspectAssessmentsData, $insertChunkSize) as $chunk) {
                DB::table('aspect_assessments')->insert($chunk);
            }
        }
        if (! empty($subAspectAssessmentsData)) {
            foreach (array_chunk($subAspectAssessmentsData, $insertChunkSize) as $chunk) {
                DB::table('sub_aspect_assessments')->insert($chunk);
            }
        }
        if (! empty($finalAssessmentsData)) {
            foreach (array_chunk($finalAssessmentsData, $insertChunkSize) as $chunk) {
                DB::table('final_assessments')->insert($chunk);
            }
        }
        if (! empty($psychTestsData)) {
            foreach (array_chunk($psychTestsData, $insertChunkSize) as $chunk) {
                DB::table('mmpi')->insert($chunk);
            }
        }
        if (! empty($interpretationsData)) {
            foreach (array_chunk($interpretationsData, $insertChunkSize) as $chunk) {
                DB::table('interpretations')->insert($chunk);
            }
        }
        // 🚀 BULK INSERT test_results
        if (! empty($testResultsData)) {
            foreach (array_chunk($testResultsData, $insertChunkSize) as $chunk) {
                DB::table('test_results')->insert($chunk);
            }
        }
        // 🚀 BULK INSERT participant_career_histories
        if (! empty($careerHistoriesData)) {
            foreach (array_chunk($careerHistoriesData, $insertChunkSize) as $chunk) {
                DB::table('participant_career_histories')->insert($chunk);
            }
        }
        // 🚀 BULK INSERT participant_performance_records
        if (! empty($performanceRecordsData)) {
            foreach (array_chunk($performanceRecordsData, $insertChunkSize) as $chunk) {
                DB::table('participant_performance_records')->insert($chunk);
            }
        }
        // 🚀 BULK INSERT participant_personal_profiles
        if (! empty($personalProfilesData)) {
            foreach (array_chunk($personalProfilesData, $insertChunkSize) as $chunk) {
                DB::table('participant_personal_profiles')->insert($chunk);
            }
        }
    }

    /**
     * ⚡ Clear Eloquent memory to prevent memory leak
     */
    private function clearEloquentMemory(): void
    {
        DB::connection()->flushQueryLog();

        Participant::clearBootedModels();

        foreach ([
            Participant::class,
            CategoryAssessment::class,
            AspectAssessment::class,
            SubAspectAssessment::class,
            FinalAssessment::class,
            TestResult::class,
            Mmpi::class,
            Interpretation::class,
        ] as $model) {
            if (method_exists($model, 'flushEventListeners')) {
                $model::flushEventListeners();
            }
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
