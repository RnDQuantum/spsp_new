<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Aspect;
use App\Models\AssessmentEvent;
use App\Models\AssessmentTemplate;
use App\Models\Batch;
use App\Models\CategoryType;
use App\Models\Institution;
use App\Models\Participant;
use App\Models\PositionFormation;
use App\Services\Assessment\AssessmentCalculationService;
use App\Services\Cache\AspectCacheService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DynamicAssessmentSeeder extends Seeder
{
    public function __construct(private readonly AssessmentCalculationService $assessmentService) {}

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
                'participants_count' => 20000, // JUMLAH PESERTA
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
                'participants_count' => 15000, // JUMLAH PESERTA
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
                'participants_count' => 1200,
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
                'participants_count' => 1000,
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
                'participants_count' => 500,
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
                'participants_count' => 10000,
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
                'participants_count' => 1800,
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
                'participants_count' => 1000,
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
                'participants_count' => 500,
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
                'participants_count' => 500,
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
                'participants_count' => 500,
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
                'participants_count' => 800,
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
                'participants_count' => 800,
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
     * Seed single event with all participants (OPTIMIZED)
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

            // 5. Generate participants with calculated assessments (OPTIMIZED)
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
        $this->info("  👥 Creating {$totalParticipants} participants...");

        $progressBar = $this->command->getOutput()->createProgressBar($totalParticipants);
        $progressBar->start();

        // ⚡ ADAPTIVE CHUNK SIZE: Larger chunks for better performance
        $chunkSize = match (true) {
            $totalParticipants < 500 => 50,
            $totalParticipants < 2000 => 100,
            $totalParticipants < 5000 => 150,
            default => 200
        };

        $totalChunks = (int) ceil($totalParticipants / $chunkSize);
        $this->info("\n  📊 Processing in {$totalChunks} chunks of ~{$chunkSize} participants");

        for ($chunkIndex = 0; $chunkIndex < $totalChunks; $chunkIndex++) {
            $currentChunkSize = min($chunkSize, $totalParticipants - ($chunkIndex * $chunkSize));

            // ⚡ Process chunk in separate transaction
            DB::beginTransaction();
            try {
                $this->processParticipantChunk(
                    $currentChunkSize,
                    $event,
                    $batches,
                    $positions,
                    $config,
                    $categoriesCache,
                    $aspectsCache,
                    $progressBar
                );

                DB::commit();

                // ⚡ Memory management: GC every 5 chunks
                if ($chunkIndex % 5 === 0 && $chunkIndex > 0) {
                    gc_collect_cycles();
                }
            } catch (\Exception $e) {
                DB::rollBack();
                $progressBar->finish();
                throw $e;
            }
        }

        $progressBar->finish();
        $this->info("\n  ✅ {$totalParticipants} participants created successfully!");
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
        // Prepare batch data arrays
        $participantsData = [];
        $psychTestsData = [];
        $interpretationsData = [];

        // Generate all participants data first
        for ($i = 0; $i < $chunkSize; $i++) {
            $performanceLevel = $this->determinePerformanceLevel($config['performance_distribution']);
            $batch = fake()->randomElement($batches);
            $position = fake()->randomElement($positions);

            // Generate participant data
            $participantData = $this->generateParticipantData($event, $batch, $position);
            $participantsData[] = $participantData;
        }

        // ⚡ BULK INSERT: Insert all participants at once
        DB::table('participants')->insert($participantsData);

        // ⚡ Get inserted participants (using username as identifier)
        $insertedParticipants = Participant::where('event_id', $event->id)
            ->whereIn('username', collect($participantsData)->pluck('username'))
            ->get()
            ->keyBy('username');

        // Process each participant's assessments
        foreach ($participantsData as $pData) {
            $participant = $insertedParticipants->get($pData['username']);
            if (! $participant) {
                continue;
            }

            // Get performance level (regenerate based on distribution)
            $performanceLevel = $this->determinePerformanceLevel($config['performance_distribution']);

            // Get position and template
            $position = collect($positions)->firstWhere('id', $participant->position_formation_id);
            $template = $position->template;

            // Get categories from cache
            $categories = $categoriesCache->get($template->id);
            $potensiCategory = $categories->firstWhere('code', 'potensi');
            $kompetensiCategory = $categories->firstWhere('code', 'kompetensi');

            // Generate assessment data
            $assessmentsData = $this->generateAssessmentsData(
                $template,
                $potensiCategory,
                $kompetensiCategory,
                $performanceLevel,
                $aspectsCache
            );

            // ⚡ Calculate assessments (already inside transaction)
            $this->assessmentService->calculateParticipantWithoutTransaction($participant, $assessmentsData);

            // Prepare psychological test data
            $psychTestsData[] = $this->generatePsychTestData($participant, $performanceLevel);

            // Prepare interpretations data
            $interpretationsData[] = $this->generateInterpretationData($participant, $potensiCategory, $performanceLevel, 'potensi');
            $interpretationsData[] = $this->generateInterpretationData($participant, $kompetensiCategory, $performanceLevel, 'kompetensi');

            $progressBar->advance();
        }

        // ⚡ BULK INSERT: Insert psychological tests
        if (! empty($psychTestsData)) {
            DB::table('psychological_tests')->insert($psychTestsData);
        }

        // ⚡ BULK INSERT: Insert interpretations
        if (! empty($interpretationsData)) {
            DB::table('interpretations')->insert($interpretationsData);
        }
    }

    /**
     * Generate participant data array for bulk insert
     */
    private function generateParticipantData(AssessmentEvent $event, Batch $batch, PositionFormation $position): array
    {
        $gender = fake()->randomElement(['L', 'P']);
        $firstName = $gender === 'L' ? fake()->firstNameMale() : fake()->firstNameFemale();
        $lastName = fake()->lastName();
        $degrees = ['S.Si', 'S.T', 'S.Kom', 'S.E', 'S.H', 'S.Ak', 'S.Psi', 'S.Pd', 'S.Sos'];
        $degree = fake()->randomElement($degrees);

        return [
            'event_id' => $event->id,
            'batch_id' => $batch->id,
            'position_formation_id' => $position->id,
            'username' => $this->generateUniqueUsername(),
            'test_number' => $this->generateUniqueTestNumber(),
            'skb_number' => $this->generateUniqueSkbNumber(),
            'name' => strtoupper($firstName . ' ' . $lastName) . ', ' . $degree,
            'email' => $this->generateUniqueEmail(),
            'phone' => fake()->numerify('08##########'),
            'gender' => $gender,
            'photo_path' => null,
            'assessment_date' => fake()->dateTimeBetween('-30 days', 'now')->format('Y-m-d'),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    /**
     * Generate psychological test data for bulk insert
     */
    private function generatePsychTestData(Participant $participant, string $performanceLevel): array
    {
        $baseData = [
            'participant_id' => $participant->id,
            'event_id' => $participant->event_id,
            'no_test' => $participant->test_number,
            'username' => $participant->username,
            'created_at' => now(),
            'updated_at' => now(),
        ];

        return match ($performanceLevel) {
            'high' => array_merge($baseData, [
                'validitas' => 'Valid - Hasil tes dapat dipercaya dan akurat',
                'internal' => 'Memiliki kemampuan internal yang sangat baik dengan potensi tinggi dalam penalaran dan analisis',
                'interpersonal' => 'Keterampilan interpersonal yang sangat baik, mampu memimpin dan berkolaborasi efektif',
                'kap_kerja' => 'Kapasitas kerja tinggi dengan kemampuan menyelesaikan tugas kompleks secara efisien',
                'klinik' => 'Tidak ada indikasi klinis yang signifikan, kondisi psikologis stabil',
                'kesimpulan' => 'Kandidat dengan performa tinggi, memiliki potensi untuk posisi leadership',
                'psikogram' => json_encode(['Leadership' => 'Sangat Baik', 'Problem Solving' => 'Sangat Baik', 'Adaptability' => 'Baik']),
                'nilai_pq' => fake()->randomFloat(2, 85, 95),
                'tingkat_stres' => fake()->randomElement(['Rendah', 'Normal']),
            ]),
            'medium' => array_merge($baseData, [
                'validitas' => 'Valid - Hasil tes dapat dipercaya',
                'internal' => 'Kemampuan internal yang memadai dengan potensi untuk berkembang',
                'interpersonal' => 'Keterampilan interpersonal yang baik, mampu bekerja dalam tim',
                'kap_kerja' => 'Kapasitas kerja yang memadai sesuai dengan standar posisi',
                'klinik' => 'Tidak ada indikasi klinis yang signifikan',
                'kesimpulan' => 'Kandidat dengan performa memadai, cocok untuk posisi yang dilamar',
                'psikogram' => json_encode(['Reliability' => 'Baik', 'Organization' => 'Baik', 'Detail Orientation' => 'Baik']),
                'nilai_pq' => fake()->randomFloat(2, 70, 84),
                'tingkat_stres' => fake()->randomElement(['Normal', 'Sedang']),
            ]),
            'low' => array_merge($baseData, [
                'validitas' => 'Valid dengan catatan - Perlu observasi lebih lanjut',
                'internal' => 'Kemampuan internal perlu pengembangan lebih lanjut',
                'interpersonal' => 'Keterampilan interpersonal memerlukan pengembangan',
                'kap_kerja' => 'Kapasitas kerja di bawah standar, memerlukan pelatihan intensif',
                'klinik' => 'Terdapat beberapa area yang memerlukan perhatian khusus',
                'kesimpulan' => 'Kandidat memerlukan program pengembangan intensif sebelum dapat optimal',
                'psikogram' => json_encode(['Adaptability' => 'Cukup', 'Motivation' => 'Perlu Ditingkatkan', 'Planning' => 'Perlu Ditingkatkan']),
                'nilai_pq' => fake()->randomFloat(2, 50, 69),
                'tingkat_stres' => fake()->randomElement(['Sedang', 'Tinggi']),
            ]),
        };
    }

    /**
     * Generate interpretation data for bulk insert
     */
    private function generateInterpretationData(
        Participant $participant,
        CategoryType $category,
        string $performanceLevel,
        string $categoryCode
    ): array {
        $interpretations = [
            'potensi' => [
                'high' => 'Memiliki potensi yang sangat baik dengan kemampuan di atas rata-rata dalam berbagai aspek. Kandidat menunjukkan kecenderungan untuk berkembang pesat dan mampu mengatasi tantangan kompleks.',
                'medium' => 'Menunjukkan potensi yang memadai dengan ruang untuk pengembangan lebih lanjut. Dengan bimbingan dan kesempatan yang tepat, kandidat dapat meningkatkan performa secara signifikan.',
                'low' => 'Memerlukan pengembangan intensif untuk meningkatkan potensi di berbagai aspek. Diperlukan program pelatihan terstruktur dan mentoring berkelanjutan untuk mencapai standar yang diharapkan.',
            ],
            'kompetensi' => [
                'high' => 'Menguasai kompetensi dengan sangat baik dan konsisten di atas standar yang ditetapkan. Kandidat mampu menerapkan kompetensi secara efektif dalam berbagai situasi kerja.',
                'medium' => 'Memiliki kompetensi yang cukup memadai sesuai dengan standar posisi. Beberapa area masih memerlukan pengembangan untuk mencapai tingkat optimal.',
                'low' => 'Perlu peningkatan signifikan dalam beberapa area kompetensi kunci. Diperlukan upaya intensif untuk mengembangkan kompetensi yang dipersyaratkan oleh posisi ini.',
            ],
        ];

        return [
            'participant_id' => $participant->id,
            'category_type_id' => $category->id,
            'event_id' => $participant->event_id,
            'interpretation_text' => $interpretations[$categoryCode][$performanceLevel] ?? 'Data interpretasi sedang diproses.',
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    // Unique identifier generators using static counter
    private static int $participantCounter = 0;

    private function generateUniqueUsername(): string
    {
        self::$participantCounter++;
        $letters = fake()->bothify('???');
        $numbers = str_pad((string) (self::$participantCounter % 100), 2, '0', STR_PAD_LEFT);
        $suffix = str_pad((string) ((int) (self::$participantCounter / 100)), 3, '0', STR_PAD_LEFT);

        return strtoupper($letters . $numbers . '-' . $suffix);
    }

    private function generateUniqueTestNumber(): string
    {
        $prefix = fake()->numerify('##-#-#-##');
        $sequence = str_pad((string) self::$participantCounter, 5, '0', STR_PAD_LEFT);

        return $prefix . '-' . $sequence;
    }

    private function generateUniqueSkbNumber(): string
    {
        $baseNumber = str_pad((string) self::$participantCounter, 5, '0', STR_PAD_LEFT);

        return '244002401200' . $baseNumber;
    }

    private function generateUniqueEmail(): string
    {
        $providers = ['gmail.com', 'yahoo.com', 'hotmail.com', 'outlook.com'];
        $provider = fake()->randomElement($providers);

        return 'participant' . self::$participantCounter . '@' . $provider;
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
