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
                    'high' => 20,
                    'medium' => 10,
                    'low' => 70,
                ],
            ],

            // Configuration 3: PT. Telkom Indonesia - BUMN & Teknologi
            [
                'institution_code' => 'telkom',
                'event' => [
                    'code' => 'TALENT-TELKOM-2025',
                    'name' => 'Talent Assessment Telkom 2025',
                    'description' => 'Assessment program pengembangan talent untuk PT. Telkom Indonesia',
                    'year' => 2025,
                    'start_date' => '2025-01-15',
                    'end_date' => '2025-03-30',
                    'status' => 'ongoing',
                ],
                'batches' => [['code' => 'BATCH-1-JAKARTA', 'name' => 'Gelombang 1 - Jakarta', 'location' => 'Jakarta', 'batch_number' => 1, 'start_date' => '2025-01-15', 'end_date' => '2025-01-16'], ['code' => 'BATCH-2-BANDUNG', 'name' => 'Gelombang 2 - Bandung', 'location' => 'Bandung', 'batch_number' => 2, 'start_date' => '2025-02-15', 'end_date' => '2025-02-16']],
                'positions' => [['code' => 'it_manager', 'name' => 'IT Manager', 'quota' => 30, 'template_code' => 'supervisor_standard_v1'], ['code' => 'network_engineer', 'name' => 'Network Engineer', 'quota' => 50, 'template_code' => 'staff_standard_v1'], ['code' => 'data_analyst', 'name' => 'Data Analyst', 'quota' => 40, 'template_code' => 'staff_standard_v1']],
                'participants_count' => 120,
                'performance_distribution' => [
                    'high' => 30,
                    'medium' => 55,
                    'low' => 15,
                ],
            ],

            // Configuration 4: PT. Bank Mandiri - BUMN & Keuangan
            [
                'institution_code' => 'mandiri',
                'event' => [
                    'code' => 'LEADERSHIP-MANDIRI-2025',
                    'name' => 'Leadership Development Mandiri 2025',
                    'description' => 'Program assessment pengembangan kepemimpinan Bank Mandiri',
                    'year' => 2025,
                    'start_date' => '2025-02-01',
                    'end_date' => '2025-04-30',
                    'status' => 'ongoing',
                ],
                'batches' => [['code' => 'BATCH-1-JAKARTA', 'name' => 'Gelombang 1 - Jakarta', 'location' => 'Jakarta', 'batch_number' => 1, 'start_date' => '2025-02-10', 'end_date' => '2025-02-11']],
                'positions' => [['code' => 'branch_manager', 'name' => 'Branch Manager', 'quota' => 40, 'template_code' => 'supervisor_standard_v1'], ['code' => 'risk_analyst', 'name' => 'Risk Analyst', 'quota' => 30, 'template_code' => 'professional_standard_v1'], ['code' => 'relationship_officer', 'name' => 'Relationship Officer', 'quota' => 30, 'template_code' => 'staff_standard_v1']],
                'participants_count' => 100,
                'performance_distribution' => [
                    'high' => 35,
                    'medium' => 50,
                    'low' => 15,
                ],
            ],

            // Configuration 5: Universitas Indonesia - Pendidikan
            [
                'institution_code' => 'ui',
                'event' => [
                    'code' => 'DOSEN-UI-2025',
                    'name' => 'Seleksi Dosen Universitas Indonesia 2025',
                    'description' => 'Assessment calon dosen Universitas Indonesia',
                    'year' => 2025,
                    'start_date' => '2025-03-01',
                    'end_date' => '2025-05-31',
                    'status' => 'draft',
                ],
                'batches' => [['code' => 'BATCH-1-DEPOK', 'name' => 'Gelombang 1 - Depok', 'location' => 'Depok', 'batch_number' => 1, 'start_date' => '2025-03-15', 'end_date' => '2025-03-16']],
                'positions' => [['code' => 'dosen_teknik', 'name' => 'Dosen Teknik', 'quota' => 20, 'template_code' => 'professional_standard_v1'], ['code' => 'dosen_ekonomi', 'name' => 'Dosen Ekonomi', 'quota' => 15, 'template_code' => 'professional_standard_v1'], ['code' => 'dosen_kedokteran', 'name' => 'Dosen Kedokteran', 'quota' => 15, 'template_code' => 'professional_standard_v1']],
                'participants_count' => 50,
                'performance_distribution' => [
                    'high' => 40,
                    'medium' => 45,
                    'low' => 15,
                ],
            ],

            // Configuration 6: PT. Gojek Indonesia - Swasta & Teknologi
            [
                'institution_code' => 'gojek',
                'event' => [
                    'code' => 'TECH-GOJEK-2025',
                    'name' => 'Tech Talent Assessment Gojek 2025',
                    'description' => 'Assessment program tech talent Gojek Indonesia',
                    'year' => 2025,
                    'start_date' => '2025-01-10',
                    'end_date' => '2025-12-31',
                    'status' => 'ongoing',
                ],
                'batches' => [['code' => 'BATCH-1-JAKARTA', 'name' => 'Gelombang 1 - Jakarta', 'location' => 'Jakarta', 'batch_number' => 1, 'start_date' => '2025-02-01', 'end_date' => '2025-02-02'], ['code' => 'BATCH-2-JAKARTA', 'name' => 'Gelombang 2 - Jakarta', 'location' => 'Jakarta', 'batch_number' => 2, 'start_date' => '2025-06-01', 'end_date' => '2025-06-02']],
                'positions' => [['code' => 'software_engineer', 'name' => 'Software Engineer', 'quota' => 80, 'template_code' => 'staff_standard_v1'], ['code' => 'product_manager', 'name' => 'Product Manager', 'quota' => 30, 'template_code' => 'supervisor_standard_v1'], ['code' => 'data_scientist', 'name' => 'Data Scientist', 'quota' => 40, 'template_code' => 'professional_standard_v1']],
                'participants_count' => 150,
                'performance_distribution' => [
                    'high' => 35,
                    'medium' => 50,
                    'low' => 15,
                ],
            ],

            // Configuration 7: Badan Kepegawaian Negara (BKN) - Kementerian
            [
                'institution_code' => 'bkn',
                'event' => [
                    'code' => 'CPNS-BKN-2025',
                    'name' => 'Seleksi CPNS BKN 2025',
                    'description' => 'Assessment CPNS untuk Badan Kepegawaian Negara tahun 2025',
                    'year' => 2025,
                    'start_date' => '2025-08-01',
                    'end_date' => '2025-12-31',
                    'status' => 'ongoing',
                ],
                'batches' => [['code' => 'BATCH-1-JAKARTA', 'name' => 'Gelombang 1 - Jakarta', 'location' => 'Jakarta', 'batch_number' => 1, 'start_date' => '2025-09-01', 'end_date' => '2025-09-02']],
                'positions' => [['code' => 'analis_sdm', 'name' => 'Analis SDM', 'quota' => 40, 'template_code' => 'professional_standard_v1'], ['code' => 'admin_kepegawaian', 'name' => 'Admin Kepegawaian', 'quota' => 60, 'template_code' => 'staff_standard_v1']],
                'participants_count' => 100,
                'performance_distribution' => [
                    'high' => 25,
                    'medium' => 60,
                    'low' => 15,
                ],
            ],

            // Configuration 8: Kementerian Pendidikan dan Kebudayaan - Kementerian & Pendidikan
            [
                'institution_code' => 'kemendikbud',
                'event' => [
                    'code' => 'GURU-KEMENDIKBUD-2025',
                    'name' => 'Seleksi Guru Kemendikbud 2025',
                    'description' => 'Assessment calon guru Kementerian Pendidikan dan Kebudayaan',
                    'year' => 2025,
                    'start_date' => '2025-06-01',
                    'end_date' => '2025-09-30',
                    'status' => 'ongoing',
                ],
                'batches' => [['code' => 'BATCH-1-JAKARTA', 'name' => 'Gelombang 1 - Jakarta', 'location' => 'Jakarta', 'batch_number' => 1, 'start_date' => '2025-07-01', 'end_date' => '2025-07-02'], ['code' => 'BATCH-2-SURABAYA', 'name' => 'Gelombang 2 - Surabaya', 'location' => 'Surabaya', 'batch_number' => 2, 'start_date' => '2025-08-01', 'end_date' => '2025-08-02']],
                'positions' => [['code' => 'guru_sd', 'name' => 'Guru SD', 'quota' => 100, 'template_code' => 'professional_standard_v1'], ['code' => 'guru_smp', 'name' => 'Guru SMP', 'quota' => 80, 'template_code' => 'professional_standard_v1']],
                'participants_count' => 180,
                'performance_distribution' => [
                    'high' => 30,
                    'medium' => 50,
                    'low' => 20,
                ],
            ],

            // Configuration 9: PT. Pertamina - BUMN
            [
                'institution_code' => 'pertamina',
                'event' => [
                    'code' => 'RECRUITMENT-PERTAMINA-2025',
                    'name' => 'Recruitment Program Pertamina 2025',
                    'description' => 'Assessment program rekrutmen PT. Pertamina',
                    'year' => 2025,
                    'start_date' => '2025-03-01',
                    'end_date' => '2025-06-30',
                    'status' => 'ongoing',
                ],
                'batches' => [['code' => 'BATCH-1-JAKARTA', 'name' => 'Gelombang 1 - Jakarta', 'location' => 'Jakarta', 'batch_number' => 1, 'start_date' => '2025-04-01', 'end_date' => '2025-04-02']],
                'positions' => [['code' => 'engineer', 'name' => 'Engineer', 'quota' => 50, 'template_code' => 'professional_standard_v1'], ['code' => 'analyst', 'name' => 'Analyst', 'quota' => 30, 'template_code' => 'staff_standard_v1'], ['code' => 'supervisor', 'name' => 'Supervisor', 'quota' => 20, 'template_code' => 'supervisor_standard_v1']],
                'participants_count' => 100,
                'performance_distribution' => [
                    'high' => 35,
                    'medium' => 50,
                    'low' => 15,
                ],
            ],

            // Configuration 10: Universitas Gadjah Mada - Pendidikan
            [
                'institution_code' => 'ugm',
                'event' => [
                    'code' => 'DOSEN-UGM-2025',
                    'name' => 'Seleksi Dosen UGM 2025',
                    'description' => 'Assessment calon dosen Universitas Gadjah Mada',
                    'year' => 2025,
                    'start_date' => '2025-04-01',
                    'end_date' => '2025-07-31',
                    'status' => 'ongoing',
                ],
                'batches' => [['code' => 'BATCH-1-YOGYAKARTA', 'name' => 'Gelombang 1 - Yogyakarta', 'location' => 'Yogyakarta', 'batch_number' => 1, 'start_date' => '2025-05-01', 'end_date' => '2025-05-02']],
                'positions' => [['code' => 'dosen_soshum', 'name' => 'Dosen Sosial Humaniora', 'quota' => 25, 'template_code' => 'professional_standard_v1'], ['code' => 'dosen_saintek', 'name' => 'Dosen Saintek', 'quota' => 25, 'template_code' => 'professional_standard_v1']],
                'participants_count' => 50,
                'performance_distribution' => [
                    'high' => 40,
                    'medium' => 45,
                    'low' => 15,
                ],
            ],

            // Configuration 11: Institut Teknologi Bandung - Pendidikan & Teknologi
            [
                'institution_code' => 'itb',
                'event' => [
                    'code' => 'DOSEN-ITB-2025',
                    'name' => 'Seleksi Dosen ITB 2025',
                    'description' => 'Assessment calon dosen Institut Teknologi Bandung',
                    'year' => 2025,
                    'start_date' => '2025-05-01',
                    'end_date' => '2025-08-31',
                    'status' => 'ongoing',
                ],
                'batches' => [['code' => 'BATCH-1-BANDUNG', 'name' => 'Gelombang 1 - Bandung', 'location' => 'Bandung', 'batch_number' => 1, 'start_date' => '2025-06-01', 'end_date' => '2025-06-02']],
                'positions' => [['code' => 'dosen_teknik', 'name' => 'Dosen Teknik', 'quota' => 30, 'template_code' => 'professional_standard_v1'], ['code' => 'dosen_informatika', 'name' => 'Dosen Informatika', 'quota' => 20, 'template_code' => 'professional_standard_v1']],
                'participants_count' => 50,
                'performance_distribution' => [
                    'high' => 45,
                    'medium' => 40,
                    'low' => 15,
                ],
            ],

            // Configuration 12: PT. Unilever Indonesia - Swasta
            [
                'institution_code' => 'unilever',
                'event' => [
                    'code' => 'MT-UNILEVER-2025',
                    'name' => 'Management Trainee Unilever 2025',
                    'description' => 'Assessment program Management Trainee PT. Unilever Indonesia',
                    'year' => 2025,
                    'start_date' => '2025-01-15',
                    'end_date' => '2025-04-30',
                    'status' => 'ongoing',
                ],
                'batches' => [['code' => 'BATCH-1-JAKARTA', 'name' => 'Gelombang 1 - Jakarta', 'location' => 'Jakarta', 'batch_number' => 1, 'start_date' => '2025-02-15', 'end_date' => '2025-02-16']],
                'positions' => [['code' => 'mt_marketing', 'name' => 'MT Marketing', 'quota' => 30, 'template_code' => 'staff_standard_v1'], ['code' => 'mt_supply_chain', 'name' => 'MT Supply Chain', 'quota' => 20, 'template_code' => 'staff_standard_v1']],
                'participants_count' => 50,
                'performance_distribution' => [
                    'high' => 40,
                    'medium' => 45,
                    'low' => 15,
                ],
            ],

            // Configuration 13: PT. Tokopedia - Swasta & Teknologi
            [
                'institution_code' => 'tokopedia',
                'event' => [
                    'code' => 'TECH-TOKOPEDIA-2025',
                    'name' => 'Tech Hiring Tokopedia 2025',
                    'description' => 'Assessment program tech hiring PT. Tokopedia',
                    'year' => 2025,
                    'start_date' => '2025-02-01',
                    'end_date' => '2025-05-31',
                    'status' => 'ongoing',
                ],
                'batches' => [['code' => 'BATCH-1-JAKARTA', 'name' => 'Gelombang 1 - Jakarta', 'location' => 'Jakarta', 'batch_number' => 1, 'start_date' => '2025-03-01', 'end_date' => '2025-03-02']],
                'positions' => [['code' => 'backend_engineer', 'name' => 'Backend Engineer', 'quota' => 40, 'template_code' => 'staff_standard_v1'], ['code' => 'frontend_engineer', 'name' => 'Frontend Engineer', 'quota' => 30, 'template_code' => 'staff_standard_v1'], ['code' => 'tech_lead', 'name' => 'Tech Lead', 'quota' => 10, 'template_code' => 'supervisor_standard_v1']],
                'participants_count' => 80,
                'performance_distribution' => [
                    'high' => 30,
                    'medium' => 55,
                    'low' => 15,
                ],
            ],

            // Configuration 14: RSUP Dr. Cipto Mangunkusumo - Kesehatan
            [
                'institution_code' => 'rscm',
                'event' => [
                    'code' => 'TENAGA-MEDIS-RSCM-2025',
                    'name' => 'Seleksi Tenaga Medis RSCM 2025',
                    'description' => 'Assessment tenaga medis RSUP Dr. Cipto Mangunkusumo',
                    'year' => 2025,
                    'start_date' => '2025-03-01',
                    'end_date' => '2025-06-30',
                    'status' => 'ongoing',
                ],
                'batches' => [['code' => 'BATCH-1-JAKARTA', 'name' => 'Gelombang 1 - Jakarta', 'location' => 'Jakarta', 'batch_number' => 1, 'start_date' => '2025-04-01', 'end_date' => '2025-04-02']],
                'positions' => [['code' => 'dokter_spesialis', 'name' => 'Dokter Spesialis', 'quota' => 20, 'template_code' => 'professional_standard_v1'], ['code' => 'perawat_icu', 'name' => 'Perawat ICU', 'quota' => 40, 'template_code' => 'staff_standard_v1'], ['code' => 'tenaga_laboratorium', 'name' => 'Tenaga Laboratorium', 'quota' => 20, 'template_code' => 'staff_standard_v1']],
                'participants_count' => 80,
                'performance_distribution' => [
                    'high' => 35,
                    'medium' => 50,
                    'low' => 15,
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
