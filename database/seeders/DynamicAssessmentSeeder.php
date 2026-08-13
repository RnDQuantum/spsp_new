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
use App\Models\Participant;
use App\Models\PositionFormation;
use App\Models\Project;
use App\Models\SubAspectAssessment;
use App\Services\Cache\AspectCacheService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DynamicAssessmentSeeder extends Seeder
{
    /**
     * KONFIGURASI SEEDER DINAMIS
     *
     * Edit konfigurasi di sini untuk generate data berbeda
     */
    private function getSeederConfigurations(): array
    {
        return [
            // Configuration 1: Kejaksaan (Jalur A - Legacy)
            [
                'institution_code' => 'kejaksaan',
                'project_code' => 'AP-085',
                'event' => [
                    'code' => 'PR-A-313',
                    'name' => 'Seleksi Kompetensi Teknis Tambahan Kejaksaan 2025',
                    'description' => 'Assessment P3K untuk Kejaksaan RI tahun 2025',
                    'year' => 2025,
                    'start_date' => '2025-09-01',
                    'end_date' => '2025-12-31',
                    'status' => 'completed',
                ],
                'batches' => [['code' => 'BATCH-1-MOJOKERTO', 'name' => 'Gelombang 1 - Mojokerto', 'location' => 'Mojokerto', 'batch_number' => 1, 'start_date' => '2025-09-27', 'end_date' => '2025-09-28'], ['code' => 'BATCH-2-SURABAYA', 'name' => 'Gelombang 2 - Surabaya', 'location' => 'Surabaya', 'batch_number' => 2, 'start_date' => '2025-10-15', 'end_date' => '2025-10-16'], ['code' => 'BATCH-3-JAKARTA', 'name' => 'Gelombang 3 - Jakarta', 'location' => 'Jakarta', 'batch_number' => 3, 'start_date' => '2025-11-05', 'end_date' => '2025-11-06']],
                'positions' => [['code' => 'fisikawan_medis', 'name' => 'Fisikawan Medis', 'quota' => 20, 'template_code' => 'professional_standard_v1'], ['code' => 'analis_kebijakan', 'name' => 'Analis Kebijakan', 'quota' => 30, 'template_code' => 'staff_standard_v1'], ['code' => 'auditor', 'name' => 'Auditor', 'quota' => 25, 'template_code' => 'supervisor_standard_v1'], ['code' => 'pranata_komputer', 'name' => 'Pranata Komputer', 'quota' => 25, 'template_code' => 'staff_standard_v1']],
                'participants_count' => 20000,
                'nine_box_distribution' => [
                    'K-1' => 5,
                    'K-2' => 10,
                    'K-3' => 5,
                    'K-4' => 10,
                    'K-5' => 20,
                    'K-6' => 10,
                    'K-7' => 15,
                    'K-8' => 15,
                    'K-9' => 10,
                ],
            ],

            // Configuration 2: Kejaksaan (Jalur B - API Online)
            [
                'institution_code' => 'kejaksaan',
                'project_code' => 'AP-554',
                'event' => [
                    'code' => 'PR-A-338',
                    'name' => 'Tes Psikologi & Wawancara Kejaksaan Agung (PR-A-338)',
                    'description' => 'Assessment Online & Wawancara Kejaksaan Agung RI',
                    'year' => 2025,
                    'start_date' => '2025-10-01',
                    'end_date' => '2025-12-31',
                    'status' => 'completed',
                ],
                'batches' => [['code' => 'BATCH-1-JAKARTA', 'name' => 'Gelombang 1 - Jakarta', 'location' => 'Jakarta', 'batch_number' => 1, 'start_date' => '2025-10-10', 'end_date' => '2025-10-11']],
                'positions' => [['code' => 'analis_hukum', 'name' => 'Analis Hukum', 'quota' => 50, 'template_code' => 'professional_standard_v1'], ['code' => 'pengelola_perkara', 'name' => 'Pengelola Penanganan Perkara', 'quota' => 100, 'template_code' => 'staff_standard_v1']],
                'participants_count' => 10000,
                'nine_box_distribution' => [
                    'K-1' => 15,
                    'K-2' => 20,
                    'K-3' => 10,
                    'K-4' => 10,
                    'K-5' => 15,
                    'K-6' => 8,
                    'K-7' => 8,
                    'K-8' => 9,
                    'K-9' => 5,
                ],
            ],

            // Configuration 3: Kementerian Kesehatan
            [
                'institution_code' => 'kemenkes',
                'project_code' => 'AP-100',
                'event' => [
                    'code' => 'PR-A-355',
                    'name' => 'Seleksi P3K Kementerian Kesehatan 2025',
                    'description' => 'Assessment P3K untuk Kemenkes tahun 2025',
                    'year' => 2025,
                    'start_date' => '2025-10-01',
                    'end_date' => '2025-12-31',
                    'status' => 'ongoing',
                ],
                'batches' => [['code' => 'BATCH-1-BANDUNG', 'name' => 'Gelombang 1 - Bandung', 'location' => 'Bandung', 'batch_number' => 1, 'start_date' => '2025-10-10', 'end_date' => '2025-10-11'], ['code' => 'BATCH-2-YOGYAKARTA', 'name' => 'Gelombang 2 - Yogyakarta', 'location' => 'Yogyakarta', 'batch_number' => 2, 'start_date' => '2025-11-10', 'end_date' => '2025-11-11']],
                'positions' => [['code' => 'dokter_umum', 'name' => 'Dokter Umum', 'quota' => 50, 'template_code' => 'professional_standard_v1'], ['code' => 'perawat', 'name' => 'Perawat', 'quota' => 100, 'template_code' => 'staff_standard_v1'], ['code' => 'apoteker', 'name' => 'Apoteker', 'quota' => 50, 'template_code' => 'supervisor_standard_v1']],
                'participants_count' => 2000,
                'nine_box_distribution' => [
                    'K-1' => 15,
                    'K-2' => 20,
                    'K-3' => 10,
                    'K-4' => 10,
                    'K-5' => 15,
                    'K-6' => 8,
                    'K-7' => 8,
                    'K-8' => 9,
                    'K-9' => 5,
                ],
            ],

            // Configuration 4: PT. Telkom Indonesia
            [
                'institution_code' => 'telkom',
                'project_code' => 'AP-201',
                'event' => [
                    'code' => 'PR-A-360',
                    'name' => 'Talent Assessment Telkom 2025',
                    'description' => 'Assessment program pengembangan talent untuk PT. Telkom Indonesia',
                    'year' => 2025,
                    'start_date' => '2025-01-15',
                    'end_date' => '2025-03-30',
                    'status' => 'ongoing',
                ],
                'batches' => [['code' => 'BATCH-1-JAKARTA', 'name' => 'Gelombang 1 - Jakarta', 'location' => 'Jakarta', 'batch_number' => 1, 'start_date' => '2025-01-15', 'end_date' => '2025-01-16'], ['code' => 'BATCH-2-BANDUNG', 'name' => 'Gelombang 2 - Bandung', 'location' => 'Bandung', 'batch_number' => 2, 'start_date' => '2025-02-15', 'end_date' => '2025-02-16']],
                'positions' => [['code' => 'it_manager', 'name' => 'IT Manager', 'quota' => 30, 'template_code' => 'supervisor_standard_v1'], ['code' => 'network_engineer', 'name' => 'Network Engineer', 'quota' => 50, 'template_code' => 'staff_standard_v1'], ['code' => 'data_analyst', 'name' => 'Data Analyst', 'quota' => 40, 'template_code' => 'staff_standard_v1']],
                'participants_count' => 150,
                'nine_box_distribution' => [
                    'K-1' => 5,
                    'K-2' => 8,
                    'K-3' => 7,
                    'K-4' => 10,
                    'K-5' => 20,
                    'K-6' => 10,
                    'K-7' => 15,
                    'K-8' => 15,
                    'K-9' => 10,
                ],
            ],

            // Configuration 5: PT. Bank Mandiri
            [
                'institution_code' => 'mandiri',
                'project_code' => 'AP-302',
                'event' => [
                    'code' => 'PR-A-370',
                    'name' => 'Leadership Development Mandiri 2025',
                    'description' => 'Program assessment pengembangan kepemimpinan Bank Mandiri',
                    'year' => 2025,
                    'start_date' => '2025-02-01',
                    'end_date' => '2025-04-30',
                    'status' => 'ongoing',
                ],
                'batches' => [['code' => 'BATCH-1-JAKARTA', 'name' => 'Gelombang 1 - Jakarta', 'location' => 'Jakarta', 'batch_number' => 1, 'start_date' => '2025-02-10', 'end_date' => '2025-02-11']],
                'positions' => [['code' => 'branch_manager', 'name' => 'Branch Manager', 'quota' => 40, 'template_code' => 'supervisor_standard_v1'], ['code' => 'risk_analyst', 'name' => 'Risk Analyst', 'quota' => 30, 'template_code' => 'professional_standard_v1'], ['code' => 'relationship_officer', 'name' => 'Relationship Officer', 'quota' => 30, 'template_code' => 'staff_standard_v1']],
                'participants_count' => 150,
                'nine_box_distribution' => [
                    'K-1' => 5,
                    'K-2' => 8,
                    'K-3' => 7,
                    'K-4' => 10,
                    'K-5' => 20,
                    'K-6' => 10,
                    'K-7' => 15,
                    'K-8' => 15,
                    'K-9' => 10,
                ],
            ],

            // Configuration 6: Universitas Indonesia
            [
                'institution_code' => 'ui',
                'project_code' => 'AP-401',
                'event' => [
                    'code' => 'PR-A-380',
                    'name' => 'Seleksi Dosen Universitas Indonesia 2025',
                    'description' => 'Assessment calon dosen Universitas Indonesia',
                    'year' => 2025,
                    'start_date' => '2025-03-01',
                    'end_date' => '2025-05-31',
                    'status' => 'draft',
                ],
                'batches' => [['code' => 'BATCH-1-DEPOK', 'name' => 'Gelombang 1 - Depok', 'location' => 'Depok', 'batch_number' => 1, 'start_date' => '2025-03-15', 'end_date' => '2025-03-16']],
                'positions' => [['code' => 'dosen_teknik', 'name' => 'Dosen Teknik', 'quota' => 20, 'template_code' => 'professional_standard_v1'], ['code' => 'dosen_ekonomi', 'name' => 'Dosen Ekonomi', 'quota' => 15, 'template_code' => 'professional_standard_v1'], ['code' => 'dosen_kedokteran', 'name' => 'Dosen Kedokteran', 'quota' => 15, 'template_code' => 'professional_standard_v1']],
                'participants_count' => 100,
                'nine_box_distribution' => [
                    'K-1' => 5,
                    'K-2' => 6,
                    'K-3' => 6,
                    'K-4' => 8,
                    'K-5' => 18,
                    'K-6' => 12,
                    'K-7' => 15,
                    'K-8' => 17,
                    'K-9' => 13,
                ],
            ],

            // Configuration 7: PT. Gojek Indonesia
            [
                'institution_code' => 'gojek',
                'project_code' => 'AP-501',
                'event' => [
                    'code' => 'PR-A-385',
                    'name' => 'Tech Talent Assessment Gojek 2025',
                    'description' => 'Assessment program tech talent Gojek Indonesia',
                    'year' => 2025,
                    'start_date' => '2025-01-10',
                    'end_date' => '2025-12-31',
                    'status' => 'ongoing',
                ],
                'batches' => [['code' => 'BATCH-1-JAKARTA', 'name' => 'Gelombang 1 - Jakarta', 'location' => 'Jakarta', 'batch_number' => 1, 'start_date' => '2025-02-01', 'end_date' => '2025-02-02'], ['code' => 'BATCH-2-JAKARTA', 'name' => 'Gelombang 2 - Jakarta', 'location' => 'Jakarta', 'batch_number' => 2, 'start_date' => '2025-06-01', 'end_date' => '2025-06-02']],
                'positions' => [['code' => 'software_engineer', 'name' => 'Software Engineer', 'quota' => 80, 'template_code' => 'staff_standard_v1'], ['code' => 'product_manager', 'name' => 'Product Manager', 'quota' => 30, 'template_code' => 'supervisor_standard_v1'], ['code' => 'data_scientist', 'name' => 'Data Scientist', 'quota' => 40, 'template_code' => 'professional_standard_v1']],
                'participants_count' => 100,
                'nine_box_distribution' => [
                    'K-1' => 5,
                    'K-2' => 8,
                    'K-3' => 7,
                    'K-4' => 10,
                    'K-5' => 20,
                    'K-6' => 10,
                    'K-7' => 15,
                    'K-8' => 15,
                    'K-9' => 10,
                ],
            ],

            // Configuration 8: Badan Kepegawaian Negara (BKN)
            [
                'institution_code' => 'bkn',
                'project_code' => 'AP-602',
                'event' => [
                    'code' => 'PR-A-390',
                    'name' => 'Seleksi CPNS BKN 2025',
                    'description' => 'Assessment CPNS untuk Badan Kepegawaian Negara tahun 2025',
                    'year' => 2025,
                    'start_date' => '2025-08-01',
                    'end_date' => '2025-12-31',
                    'status' => 'ongoing',
                ],
                'batches' => [['code' => 'BATCH-1-JAKARTA', 'name' => 'Gelombang 1 - Jakarta', 'location' => 'Jakarta', 'batch_number' => 1, 'start_date' => '2025-09-01', 'end_date' => '2025-09-02']],
                'positions' => [['code' => 'analis_sdm', 'name' => 'Analis SDM', 'quota' => 40, 'template_code' => 'professional_standard_v1'], ['code' => 'admin_kepegawaian', 'name' => 'Admin Kepegawaian', 'quota' => 60, 'template_code' => 'staff_standard_v1']],
                'participants_count' => 1000,
                'nine_box_distribution' => [
                    'K-1' => 5,
                    'K-2' => 10,
                    'K-3' => 5,
                    'K-4' => 10,
                    'K-5' => 20,
                    'K-6' => 10,
                    'K-7' => 15,
                    'K-8' => 15,
                    'K-9' => 10,
                ],
            ],

            // Configuration 9: Kementerian Pendidikan dan Kebudayaan
            [
                'institution_code' => 'kemendikbud',
                'project_code' => 'AP-703',
                'event' => [
                    'code' => 'PR-A-395',
                    'name' => 'Seleksi Guru Kemendikbud 2025',
                    'description' => 'Assessment calon guru Kementerian Pendidikan dan Kebudayaan',
                    'year' => 2025,
                    'start_date' => '2025-06-01',
                    'end_date' => '2025-09-30',
                    'status' => 'ongoing',
                ],
                'batches' => [['code' => 'BATCH-1-JAKARTA', 'name' => 'Gelombang 1 - Jakarta', 'location' => 'Jakarta', 'batch_number' => 1, 'start_date' => '2025-07-01', 'end_date' => '2025-07-02'], ['code' => 'BATCH-2-SURABAYA', 'name' => 'Gelombang 2 - Surabaya', 'location' => 'Surabaya', 'batch_number' => 2, 'start_date' => '2025-08-01', 'end_date' => '2025-08-02']],
                'positions' => [['code' => 'guru_sd', 'name' => 'Guru SD', 'quota' => 100, 'template_code' => 'professional_standard_v1'], ['code' => 'guru_smp', 'name' => 'Guru SMP', 'quota' => 80, 'template_code' => 'professional_standard_v1']],
                'participants_count' => 100,
                'nine_box_distribution' => [
                    'K-1' => 7,
                    'K-2' => 10,
                    'K-3' => 8,
                    'K-4' => 10,
                    'K-5' => 20,
                    'K-6' => 10,
                    'K-7' => 12,
                    'K-8' => 13,
                    'K-9' => 10,
                ],
            ],

            // Configuration 10: PT. Pertamina
            [
                'institution_code' => 'pertamina',
                'project_code' => 'AP-804',
                'event' => [
                    'code' => 'PR-A-400',
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
                'nine_box_distribution' => [
                    'K-1' => 5,
                    'K-2' => 8,
                    'K-3' => 7,
                    'K-4' => 10,
                    'K-5' => 20,
                    'K-6' => 10,
                    'K-7' => 15,
                    'K-8' => 15,
                    'K-9' => 10,
                ],
            ],

            // Configuration 11: Universitas Gadjah Mada
            [
                'institution_code' => 'ugm',
                'project_code' => 'AP-805',
                'event' => [
                    'code' => 'PR-A-405',
                    'name' => 'Seleksi Dosen UGM 2025',
                    'description' => 'Assessment calon dosen Universitas Gadjah Mada',
                    'year' => 2025,
                    'start_date' => '2025-04-01',
                    'end_date' => '2025-07-31',
                    'status' => 'ongoing',
                ],
                'batches' => [['code' => 'BATCH-1-YOGYAKARTA', 'name' => 'Gelombang 1 - Yogyakarta', 'location' => 'Yogyakarta', 'batch_number' => 1, 'start_date' => '2025-05-01', 'end_date' => '2025-05-02']],
                'positions' => [['code' => 'dosen_soshum', 'name' => 'Dosen Sosial Humaniora', 'quota' => 25, 'template_code' => 'professional_standard_v1'], ['code' => 'dosen_saintek', 'name' => 'Dosen Saintek', 'quota' => 25, 'template_code' => 'professional_standard_v1']],
                'participants_count' => 100,
                'nine_box_distribution' => [
                    'K-1' => 5,
                    'K-2' => 6,
                    'K-3' => 6,
                    'K-4' => 8,
                    'K-5' => 18,
                    'K-6' => 12,
                    'K-7' => 15,
                    'K-8' => 17,
                    'K-9' => 13,
                ],
            ],

            // Configuration 12: Institut Teknologi Bandung
            [
                'institution_code' => 'itb',
                'project_code' => 'AP-806',
                'event' => [
                    'code' => 'PR-A-410',
                    'name' => 'Seleksi Dosen ITB 2025',
                    'description' => 'Assessment calon dosen Institut Teknologi Bandung',
                    'year' => 2025,
                    'start_date' => '2025-05-01',
                    'end_date' => '2025-08-31',
                    'status' => 'ongoing',
                ],
                'batches' => [['code' => 'BATCH-1-BANDUNG', 'name' => 'Gelombang 1 - Bandung', 'location' => 'Bandung', 'batch_number' => 1, 'start_date' => '2025-06-01', 'end_date' => '2025-06-02']],
                'positions' => [['code' => 'dosen_teknik', 'name' => 'Dosen Teknik', 'quota' => 30, 'template_code' => 'professional_standard_v1'], ['code' => 'dosen_informatika', 'name' => 'Dosen Informatika', 'quota' => 20, 'template_code' => 'professional_standard_v1']],
                'participants_count' => 100,
                'nine_box_distribution' => [
                    'K-1' => 4,
                    'K-2' => 6,
                    'K-3' => 5,
                    'K-4' => 8,
                    'K-5' => 17,
                    'K-6' => 12,
                    'K-7' => 16,
                    'K-8' => 18,
                    'K-9' => 14,
                ],
            ],

            // Configuration 13: PT. Unilever Indonesia
            [
                'institution_code' => 'unilever',
                'project_code' => 'AP-807',
                'event' => [
                    'code' => 'PR-A-415',
                    'name' => 'Management Trainee Unilever 2025',
                    'description' => 'Assessment program Management Trainee PT. Unilever Indonesia',
                    'year' => 2025,
                    'start_date' => '2025-01-15',
                    'end_date' => '2025-04-30',
                    'status' => 'ongoing',
                ],
                'batches' => [['code' => 'BATCH-1-JAKARTA', 'name' => 'Gelombang 1 - Jakarta', 'location' => 'Jakarta', 'batch_number' => 1, 'start_date' => '2025-02-15', 'end_date' => '2025-02-16']],
                'positions' => [['code' => 'mt_marketing', 'name' => 'MT Marketing', 'quota' => 30, 'template_code' => 'staff_standard_v1'], ['code' => 'mt_supply_chain', 'name' => 'MT Supply Chain', 'quota' => 20, 'template_code' => 'staff_standard_v1']],
                'participants_count' => 100,
                'nine_box_distribution' => [
                    'K-1' => 5,
                    'K-2' => 6,
                    'K-3' => 6,
                    'K-4' => 8,
                    'K-5' => 18,
                    'K-6' => 12,
                    'K-7' => 15,
                    'K-8' => 17,
                    'K-9' => 13,
                ],
            ],

            // Configuration 14: PT. Tokopedia
            [
                'institution_code' => 'tokopedia',
                'project_code' => 'AP-808',
                'event' => [
                    'code' => 'PR-A-420',
                    'name' => 'Tech Hiring Tokopedia 2025',
                    'description' => 'Assessment program tech hiring PT. Tokopedia',
                    'year' => 2025,
                    'start_date' => '2025-02-01',
                    'end_date' => '2025-05-31',
                    'status' => 'ongoing',
                ],
                'batches' => [['code' => 'BATCH-1-JAKARTA', 'name' => 'Gelombang 1 - Jakarta', 'location' => 'Jakarta', 'batch_number' => 1, 'start_date' => '2025-03-01', 'end_date' => '2025-03-02']],
                'positions' => [['code' => 'backend_engineer', 'name' => 'Backend Engineer', 'quota' => 40, 'template_code' => 'staff_standard_v1'], ['code' => 'frontend_engineer', 'name' => 'Frontend Engineer', 'quota' => 30, 'template_code' => 'staff_standard_v1'], ['code' => 'tech_lead', 'name' => 'Tech Lead', 'quota' => 10, 'template_code' => 'supervisor_standard_v1']],
                'participants_count' => 100,
                'nine_box_distribution' => [
                    'K-1' => 5,
                    'K-2' => 8,
                    'K-3' => 7,
                    'K-4' => 10,
                    'K-5' => 20,
                    'K-6' => 10,
                    'K-7' => 13,
                    'K-8' => 15,
                    'K-9' => 12,
                ],
            ],

            // Configuration 15: RSUP Dr. Cipto Mangunkusumo
            [
                'institution_code' => 'rscm',
                'project_code' => 'AP-809',
                'event' => [
                    'code' => 'PR-A-425',
                    'name' => 'Seleksi Tenaga Medis RSCM 2025',
                    'description' => 'Assessment tenaga medis RSUP Dr. Cipto Mangunkusumo',
                    'year' => 2025,
                    'start_date' => '2025-03-01',
                    'end_date' => '2025-06-30',
                    'status' => 'ongoing',
                ],
                'batches' => [['code' => 'BATCH-1-JAKARTA', 'name' => 'Gelombang 1 - Jakarta', 'location' => 'Jakarta', 'batch_number' => 1, 'start_date' => '2025-04-01', 'end_date' => '2025-04-02']],
                'positions' => [['code' => 'dokter_spesialis', 'name' => 'Dokter Spesialis', 'quota' => 20, 'template_code' => 'professional_standard_v1'], ['code' => 'perawat_icu', 'name' => 'Perawat ICU', 'quota' => 40, 'template_code' => 'staff_standard_v1'], ['code' => 'tenaga_laboratorium', 'name' => 'Tenaga Laboratorium', 'quota' => 20, 'template_code' => 'staff_standard_v1']],
                'participants_count' => 100,
                'nine_box_distribution' => [
                    'K-1' => 5,
                    'K-2' => 8,
                    'K-3' => 7,
                    'K-4' => 10,
                    'K-5' => 20,
                    'K-6' => 10,
                    'K-7' => 15,
                    'K-8' => 15,
                    'K-9' => 10,
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

            // 1b. Create or get Master Project (AP-XXX)
            $projectCode = $config['project_code'] ?? 'AP-' . str_pad((string) rand(85, 999), 3, '0', STR_PAD_LEFT);
            $project = Project::firstOrCreate(
                ['code' => $projectCode],
                [
                    'institution_id' => $institution->id,
                    'name' => "Master Proyek Assessment {$institution->name} ({$projectCode})",
                    'year' => $config['event']['year'] ?? date('Y'),
                    'contract_number' => 'QHI' . rand(1000, 9999) . '-MR-' . rand(100, 999),
                    'pic_name' => 'Technical Coordinator',
                    'pic_phone' => '081234567890',
                    'project_type' => 'Seleksi & Pemetaan',
                    'status' => 'completed',
                ]
            );

            // 2. Create event with project_id link
            $event = AssessmentEvent::create([
                'institution_id' => $institution->id,
                'project_id' => $project->id,
                'location' => $config['batches'][0]['location'] ?? 'Pusat',
                'target_participants' => $config['participants_count'] ?? 100,
                'assessment_type' => 'Psikotes & Kompetensi',
                ...$config['event'],
            ]);

            $this->info("  📅 Event created: {$event->name}");

            // 3. Create batches
            $batches = [];
            foreach ($config['batches'] as $batchData) {
                $batches[] = Batch::create([
                    'event_id' => $event->id,
                    'institution_id' => $institution->id,
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
                    'institution_id' => $institution->id,
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

        // ⚡ ADAPTIVE CHUNK SIZE: Optimized for speed (bulk insert approach allows larger chunks)
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

            // Create progress bar for THIS chunk only
            $this->info("     Batch {$chunkNumber}/{$totalChunks}: Processing {$currentChunkSize} participants...");
            $chunkProgressBar = $this->command->getOutput()->createProgressBar($currentChunkSize);
            $chunkProgressBar->start();

            // ⚡ Process chunk in separate transaction
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

                // ⚡ CRITICAL: Clear Eloquent memory after each chunk
                $this->clearEloquentMemory();

                $chunkProgressBar->finish();
                $chunkDuration = microtime(true) - $chunkStartTime;
                $processedTotal += $currentChunkSize;
                $overallProgress = round(($processedTotal / $totalParticipants) * 100, 1);
                $avgSpeed = $processedTotal / (microtime(true) - $startTime);

                $this->info("\n     ✓ Batch {$chunkNumber} completed in " . number_format($chunkDuration, 2) . 's');
                $this->info("     📈 Overall: {$processedTotal}/{$totalParticipants} ({$overallProgress}%) | Speed: " . number_format($avgSpeed, 1) . " p/s\n");

                // ⚡ Memory management: Lighter GC every 5 chunks (bulk insert uses less memory)
                if ($chunkIndex % 5 === 0 && $chunkIndex > 0) {
                    gc_collect_cycles();
                    $memoryMB = round(memory_get_usage(true) / 1024 / 1024, 2);
                    $this->info("     🗑️  Memory cleanup | Current: {$memoryMB} MB\n");
                }
            } catch (\Exception $e) {
                DB::rollBack();
                $chunkProgressBar->finish();
                throw $e;
            }
        }

        $totalDuration = microtime(true) - $startTime;
        $avgSpeed = $totalParticipants / $totalDuration;

        $this->info("  ✅ {$totalParticipants} participants created successfully!");
        $this->info('  ⏱️  Total time: ' . number_format($totalDuration, 2) . 's | Average: ' . number_format($avgSpeed, 1) . ' participants/second');
    }

    /**
     * ⚡ Process single chunk of participants with batch inserts (OPTIMIZED)
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
        $categoryAssessmentsData = [];
        $aspectAssessmentsData = [];
        $subAspectAssessmentsData = [];
        $finalAssessmentsData = [];

        // Generate all participants data first
        for ($i = 0; $i < $chunkSize; $i++) {
            $batch = fake()->randomElement($batches);
            $position = fake()->randomElement($positions);

            // Generate participant data
            $participantData = $this->generateParticipantData($event, $batch, $position);
            $participantsData[] = $participantData;
        }

        // ⚡ BULK INSERT: Insert all participants in safe chunks (max 200 rows per query to avoid MySQL 65,535 placeholder limit with 35 columns)
        foreach (array_chunk($participantsData, 200) as $pChunk) {
            DB::table('participants')->insert($pChunk);
        }

        // ⚡ Get inserted participants (using username as identifier)
        $insertedParticipants = Participant::where('event_id', $event->id)
            ->whereIn('username', collect($participantsData)->pluck('username'))
            ->get()
            ->keyBy('username');

        // ⚡ NEW APPROACH: Generate ALL assessment data first, then bulk insert
        $categoryIdCounter = DB::table('category_assessments')->max('id') ?? 0;
        $aspectIdCounter = DB::table('aspect_assessments')->max('id') ?? 0;
        $subAspectIdCounter = DB::table('sub_aspect_assessments')->max('id') ?? 0;

        foreach ($participantsData as $pData) {
            $participant = $insertedParticipants->get($pData['username']);
            if (! $participant) {
                continue;
            }

            // 🎯 Determine nine box category based on distribution
            $boxCategory = $this->determineNineBoxCategory($config['nine_box_distribution']);

            // Get position and template
            $position = collect($positions)->firstWhere('id', $participant->position_formation_id);
            $template = $position->template;

            // Get categories from cache
            $categories = $categoriesCache->get($template->id);
            $potensiCategory = $categories->firstWhere('code', 'potensi');
            $kompetensiCategory = $categories->firstWhere('code', 'kompetensi');

            // Generate assessment data with separate potensi and kompetensi levels
            $assessmentsData = $this->generateAssessmentsDataForNineBox(
                $template,
                $potensiCategory,
                $kompetensiCategory,
                $boxCategory,
                $aspectsCache
            );

            // ⚡ NEW: Generate all assessment records manually (FAST)
            $this->generateAssessmentRecords(
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

            // Prepare psychological test data
            $psychTestsData[] = $this->generatePsychTestData($participant, $boxCategory);

            // Prepare interpretations data
            $interpretationsData[] = $this->generateInterpretationData($participant, $potensiCategory, $boxCategory, 'potensi');
            $interpretationsData[] = $this->generateInterpretationData($participant, $kompetensiCategory, $boxCategory, 'kompetensi');

            $progressBar->advance();
        }

        // ⚡ BULK INSERT: All assessment data in chunks (avoid MySQL placeholder limit)
        // MySQL has a limit of 65,535 placeholders per query
        // Each sub_aspect has 10 columns, so max ~6,500 rows per insert
        // We use 1000 rows per chunk to be safe
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

        // ⚡ BULK INSERT: Insert MMPI tests
        if (! empty($psychTestsData)) {
            foreach (array_chunk($psychTestsData, $insertChunkSize) as $chunk) {
                DB::table('mmpi')->insert($chunk);
            }
        }

        // ⚡ BULK INSERT: Insert interpretations
        if (! empty($interpretationsData)) {
            foreach (array_chunk($interpretationsData, $insertChunkSize) as $chunk) {
                DB::table('interpretations')->insert($chunk);
            }
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
        $gelarDepan = fake()->optional(0.2)->randomElement(['Drs.', 'Dr.', 'Ir.', 'H.']);
        $gelarBelakang = fake()->randomElement(['S.Si', 'S.T', 'S.Kom', 'S.E', 'S.H', 'S.Ak', 'S.Psi', 'S.Pd', 'S.Sos', 'M.Si', 'M.T']);
        $pendidikan = fake()->randomElement(['S1', 'S2', 'D3']);
        $agama = fake()->randomElement(['Islam', 'Kristen', 'Katolik', 'Hindu', 'Buddha']);
        $statusPerkawinan = fake()->randomElement(['Menikah', 'Belum Menikah']);
        $pangkat = fake()->randomElement(['Penata Muda', 'Penata', 'Penata Tingkat I', 'Pembina']);
        $golongan = fake()->randomElement(['III/a', 'III/b', 'III/c', 'III/d', 'IV/a']);
        $statusKepegawaian = fake()->randomElement(['PNS', 'CPNS', 'PPPK', 'BUMN', 'Swasta']);

        return [
            'event_id' => $event->id,
            'institution_id' => $event->institution_id,
            'batch_id' => $batch->id,
            'position_formation_id' => $position->id,
            'username' => $this->generateUniqueUsername(),
            'test_number' => $this->generateUniqueTestNumber(),
            'skb_number' => $this->generateUniqueSkbNumber(),
            'name' => strtoupper($firstName . ' ' . $lastName) . ', ' . $gelarBelakang,
            'tempat_lahir' => fake()->city(),
            'tanggal_lahir' => fake()->dateTimeBetween('-40 years', '-22 years')->format('Y-m-d'),
            'gelar_depan' => $gelarDepan,
            'gelar_belakang' => $gelarBelakang,
            'pendidikan' => $pendidikan,
            'agama' => $agama,
            'status_perkawinan' => $statusPerkawinan,
            'email' => $this->generateUniqueEmail(),
            'phone' => fake()->numerify('08##########'),
            'gender' => $gender,
            'photo_path' => null,
            'assessment_date' => fake()->dateTimeBetween('-30 days', 'now')->format('Y-m-d'),
            'nik' => fake()->numerify('35#############'),
            'no_kjg' => fake()->numerify('24400240120######'),
            'jabatan_pelaksana' => strtoupper($position->name),
            'jbt_fungsional' => $position->name,
            'jbt_struktural' => '-',
            'pangkat' => $pangkat,
            'golongan' => $golongan,
            'status_kepegawaian' => $statusKepegawaian,
            'unit_kerja' => 'Instansi ' . fake()->city(),
            'minat_penempatan' => $position->name,
            'pengalaman_kerja' => fake()->numberBetween(2, 12) . ' Tahun',
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    /**
     * Generate psychological test data for bulk insert
     */
    private function generatePsychTestData(Participant $participant, string $boxCategory): array
    {
        $baseData = [
            'participant_id' => $participant->id,
            'event_id' => $participant->event_id,
            'no_test' => $participant->test_number,
            'username' => $participant->username,
            'created_at' => now(),
            'updated_at' => now(),
        ];

        // Map box category to overall performance level
        // High performers: K-7, K-8, K-9 (top right quadrant)
        // Medium performers: K-2, K-4, K-5, K-6 (middle boxes)
        // Low performers: K-1, K-3 (bottom left quadrant)
        $performanceLevel = match ($boxCategory) {
            'K-7', 'K-8', 'K-9' => 'high',
            'K-1', 'K-3' => 'low',
            default => 'medium',
        };

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
        string $boxCategory,
        string $categoryCode
    ): array {
        // Map box category to specific category level (potensi or kompetensi)
        $boxMapping = [
            'K-1' => ['kompetensi' => 'low', 'potensi' => 'low'],
            'K-2' => ['kompetensi' => 'medium', 'potensi' => 'low'],
            'K-3' => ['kompetensi' => 'low', 'potensi' => 'medium'],
            'K-4' => ['kompetensi' => 'high', 'potensi' => 'low'],
            'K-5' => ['kompetensi' => 'medium', 'potensi' => 'medium'],
            'K-6' => ['kompetensi' => 'low', 'potensi' => 'high'],
            'K-7' => ['kompetensi' => 'high', 'potensi' => 'medium'],
            'K-8' => ['kompetensi' => 'medium', 'potensi' => 'high'],
            'K-9' => ['kompetensi' => 'high', 'potensi' => 'high'],
        ];

        $levels = $boxMapping[$boxCategory] ?? ['kompetensi' => 'medium', 'potensi' => 'medium'];
        $performanceLevel = $levels[$categoryCode] ?? 'medium';

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
     * 🎯 NEW: Generate assessments data for Nine Box Matrix
     * Generates separate multipliers for Potensi and Kompetensi based on box category
     */
    private function generateAssessmentsDataForNineBox(
        AssessmentTemplate $template,
        CategoryType $potensiCategory,
        CategoryType $kompetensiCategory,
        string $boxCategory,
        $aspectsCache
    ): array {
        // Map box categories to performance levels
        // Box format: K-{1-9}
        // Structure: [Kompetensi Level, Potensi Level]
        $boxMapping = [
            'K-1' => ['kompetensi' => 'low', 'potensi' => 'low'],
            'K-2' => ['kompetensi' => 'medium', 'potensi' => 'low'],
            'K-3' => ['kompetensi' => 'low', 'potensi' => 'medium'],
            'K-4' => ['kompetensi' => 'high', 'potensi' => 'low'],
            'K-5' => ['kompetensi' => 'medium', 'potensi' => 'medium'],
            'K-6' => ['kompetensi' => 'low', 'potensi' => 'high'],
            'K-7' => ['kompetensi' => 'high', 'potensi' => 'medium'],
            'K-8' => ['kompetensi' => 'medium', 'potensi' => 'high'],
            'K-9' => ['kompetensi' => 'high', 'potensi' => 'high'],
        ];

        $levels = $boxMapping[$boxCategory] ?? ['kompetensi' => 'medium', 'potensi' => 'medium'];

        // Get multiplier ranges for each category
        $kompetensiMultiplier = $this->getPerformanceMultiplier($levels['kompetensi']);
        $potensiMultiplier = $this->getPerformanceMultiplier($levels['potensi']);

        $assessmentsData = [
            'potensi' => [],
            'kompetensi' => [],
        ];

        // ⚡ POTENSI: Get aspects from cache and apply potensi multiplier
        $potensiAspects = $aspectsCache->get($potensiCategory->id)?->sortBy('order') ?? collect();

        foreach ($potensiAspects as $aspect) {
            $subAspectsData = [];

            foreach ($aspect->subAspects as $subAspect) {
                // Add random variation per sub-aspect (±0.3 variance)
                $variance = fake()->randomFloat(2, -0.3, 0.3);
                $performanceMultiplier = fake()->randomFloat(2, $potensiMultiplier[0], $potensiMultiplier[1]);

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

        // ⚡ KOMPETENSI: Get aspects from cache and apply kompetensi multiplier
        $kompetensiAspects = $aspectsCache->get($kompetensiCategory->id)?->sortBy('order') ?? collect();

        foreach ($kompetensiAspects as $aspect) {
            // Add random variation per aspect (±0.3 variance)
            $variance = fake()->randomFloat(2, -0.3, 0.3);
            $performanceMultiplier = fake()->randomFloat(2, $kompetensiMultiplier[0], $kompetensiMultiplier[1]);

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
     * Get performance multiplier range based on level
     */
    private function getPerformanceMultiplier(string $level): array
    {
        return match ($level) {
            'high' => [1.05, 1.25], // Exceed standard significantly (rating 3.15-5.00)
            'medium' => [0.85, 1.1], // Around standard (rating 2.55-4.40)
            'low' => [0.40, 0.75], // Below standard (rating 1.20-3.00, includes Kelas I & II)
            default => [0.85, 1.1],
        };
    }

    /**
     * @deprecated Use generateAssessmentsDataForNineBox instead
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
     * 🎯 NEW: Determine nine box category based on distribution
     */
    private function determineNineBoxCategory(array $distribution): string
    {
        $random = fake()->numberBetween(1, 100);
        $cumulative = 0;

        foreach ($distribution as $box => $percentage) {
            $cumulative += $percentage;
            if ($random <= $cumulative) {
                return $box;
            }
        }

        // Fallback to K-5 (middle box) if something goes wrong
        return 'K-5';
    }

    /**
     * @deprecated Use determineNineBoxCategory instead
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
     * ⚡ Generate all assessment records manually (without Eloquent overhead)
     * This method calculates and prepares all data for bulk insert
     */
    private function generateAssessmentRecords(
        Participant $participant,
        AssessmentTemplate $template,
        array $assessmentsData,
        $categoriesCache,
        $aspectsCache,
        int &$categoryIdCounter,
        int &$aspectIdCounter,
        int &$subAspectIdCounter,
        array &$categoryAssessmentsData,
        array &$aspectAssessmentsData,
        array &$subAspectAssessmentsData,
        array &$finalAssessmentsData
    ): void {
        $categoryResults = [];
        $now = now();

        // Process each category (potensi, kompetensi)
        foreach ($assessmentsData as $categoryCode => $categoryData) {
            $categoryIdCounter++;
            $categoryId = $categoryIdCounter;

            // Get category from cache
            $category = $categoriesCache->get($template->id)->firstWhere('code', $categoryCode);

            // Initialize category totals
            $categoryTotalStandardRating = 0;
            $categoryTotalStandardScore = 0;
            $categoryTotalIndividualRating = 0;
            $categoryTotalIndividualScore = 0;

            // Process each aspect in category
            foreach ($categoryData as $aspectData) {
                $aspectIdCounter++;
                $aspectId = $aspectIdCounter;

                // Get aspect from cache
                $aspect = $aspectsCache->get($category->id)?->firstWhere('code', $aspectData['aspect_code']);
                if (! $aspect) {
                    continue;
                }

                // Calculate aspect values
                $hasSubAspects = isset($aspectData['sub_aspects']) && ! empty($aspectData['sub_aspects']);

                // Initialize variables
                $standardRating = 0;
                $individualRating = 0;

                if ($hasSubAspects) {
                    // Has sub-aspects: calculate from sub-aspects
                    $subAspectRatings = [];
                    $standardRating = (float) collect($aspect->subAspects)->avg('standard_rating');

                    foreach ($aspectData['sub_aspects'] as $subAspectData) {
                        $subAspectIdCounter++;

                        $subAspect = $aspect->subAspects->firstWhere('code', $subAspectData['sub_aspect_code']);
                        if (! $subAspect) {
                            continue;
                        }

                        $rating = (int) $subAspectData['individual_rating'];
                        $subAspectRatings[] = $rating;

                        // Add sub-aspect assessment
                        $subAspectAssessmentsData[] = [
                            'id' => $subAspectIdCounter,
                            'aspect_assessment_id' => $aspectId,
                            'participant_id' => $participant->id,
                            'event_id' => $participant->event_id,
                            'sub_aspect_id' => $subAspect->id,
                            'standard_rating' => (int) $subAspect->standard_rating,
                            'individual_rating' => $rating,
                            'rating_label' => $this->getRatingLabel($rating),
                            'created_at' => $now,
                            'updated_at' => $now,
                        ];
                    }

                    $individualRating = (float) collect($subAspectRatings)->avg();
                } else {
                    // No sub-aspects: use aspect's own rating
                    $standardRating = (float) $aspect->standard_rating;
                    $individualRating = (float) $aspectData['individual_rating'];
                }

                // Calculate scores (weight is 100% for seeder simplicity)
                $weight = (float) $aspect->weight_percentage;
                $standardScore = $standardRating * $weight;
                $individualScore = $individualRating * $weight;
                $gapRating = $individualRating - $standardRating;
                $gapScore = $individualScore - $standardScore;
                $percentageScore = (int) round(($individualRating / 5) * 100);

                // Determine conclusion
                $conclusionCode = $this->determineAspectConclusion($gapRating);
                $conclusionText = $this->getAspectConclusionText($conclusionCode);

                // Add to category totals
                $categoryTotalStandardRating += $standardRating;
                $categoryTotalStandardScore += $standardScore;
                $categoryTotalIndividualRating += $individualRating;
                $categoryTotalIndividualScore += $individualScore;

                // Add aspect assessment
                $aspectAssessmentsData[] = [
                    'id' => $aspectId,
                    'category_assessment_id' => $categoryId,
                    'participant_id' => $participant->id,
                    'event_id' => $participant->event_id,
                    'batch_id' => $participant->batch_id,
                    'position_formation_id' => $participant->position_formation_id,
                    'aspect_id' => $aspect->id,
                    'standard_rating' => round($standardRating, 2),
                    'standard_score' => round($standardScore, 2),
                    'individual_rating' => round($individualRating, 2),
                    'individual_score' => round($individualScore, 2),
                    'gap_rating' => round($gapRating, 2),
                    'gap_score' => round($gapScore, 2),
                    'percentage_score' => $percentageScore,
                    'conclusion_code' => $conclusionCode,
                    'conclusion_text' => $conclusionText,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            // Calculate category gaps
            $categoryGapRating = $categoryTotalIndividualRating - $categoryTotalStandardRating;
            $categoryGapScore = $categoryTotalIndividualScore - $categoryTotalStandardScore;
            $categoryConclusionCode = $this->determineCategoryConclusion($categoryGapScore);
            $categoryConclusionText = $this->getCategoryConclusionText($categoryConclusionCode);

            // Add category assessment
            $categoryAssessmentsData[] = [
                'id' => $categoryId,
                'participant_id' => $participant->id,
                'event_id' => $participant->event_id,
                'batch_id' => $participant->batch_id,
                'position_formation_id' => $participant->position_formation_id,
                'category_type_id' => $category->id,
                'total_standard_rating' => round($categoryTotalStandardRating, 2),
                'total_standard_score' => round($categoryTotalStandardScore, 2),
                'total_individual_rating' => round($categoryTotalIndividualRating, 2),
                'total_individual_score' => round($categoryTotalIndividualScore, 2),
                'gap_rating' => round($categoryGapRating, 2),
                'gap_score' => round($categoryGapScore, 2),
                'conclusion_code' => $categoryConclusionCode,
                'conclusion_text' => $categoryConclusionText,
                'created_at' => $now,
                'updated_at' => $now,
            ];

            $categoryResults[$categoryCode] = [
                'weight' => $category->weight_percentage,
                'score' => $categoryTotalIndividualScore,
            ];
        }

        // Calculate final assessment based on actual table structure
        $potensiData = $categoryResults['potensi'] ?? null;
        $kompetensiData = $categoryResults['kompetensi'] ?? null;

        if ($potensiData && $kompetensiData) {
            // Get category assessments to get standard scores
            $potensiCategoryAssessment = collect($categoryAssessmentsData)->firstWhere('category_type_id', $categoriesCache->get($template->id)->firstWhere('code', 'potensi')->id);
            $kompetensiCategoryAssessment = collect($categoryAssessmentsData)->firstWhere('category_type_id', $categoriesCache->get($template->id)->firstWhere('code', 'kompetensi')->id);

            $totalStandardScore = ($potensiCategoryAssessment['total_standard_score'] ?? 0) + ($kompetensiCategoryAssessment['total_standard_score'] ?? 0);
            $totalIndividualScore = ($potensiCategoryAssessment['total_individual_score'] ?? 0) + ($kompetensiCategoryAssessment['total_individual_score'] ?? 0);

            $achievementPercentage = $totalStandardScore > 0
                ? round(($totalIndividualScore / $totalStandardScore) * 100, 2)
                : 0;

            // Determine conclusion
            $conclusionCode = $this->determineFinalConclusion($achievementPercentage);
            $conclusionText = $this->getFinalConclusionText($conclusionCode);

            // Add final assessment
            $finalAssessmentsData[] = [
                'participant_id' => $participant->id,
                'event_id' => $participant->event_id,
                'batch_id' => $participant->batch_id,
                'position_formation_id' => $participant->position_formation_id,
                'potensi_weight' => $potensiData['weight'],
                'potensi_standard_score' => round($potensiCategoryAssessment['total_standard_score'] ?? 0, 2),
                'potensi_individual_score' => round($potensiCategoryAssessment['total_individual_score'] ?? 0, 2),
                'kompetensi_weight' => $kompetensiData['weight'],
                'kompetensi_standard_score' => round($kompetensiCategoryAssessment['total_standard_score'] ?? 0, 2),
                'kompetensi_individual_score' => round($kompetensiCategoryAssessment['total_individual_score'] ?? 0, 2),
                'total_standard_score' => round($totalStandardScore, 2),
                'total_individual_score' => round($totalIndividualScore, 2),
                'achievement_percentage' => $achievementPercentage,
                'conclusion_code' => $conclusionCode,
                'conclusion_text' => $conclusionText,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }
    }

    /**
     * Get rating label for sub-aspect
     */
    private function getRatingLabel(int $rating): string
    {
        return match ($rating) {
            1 => 'Sangat Kurang',
            2 => 'Kurang',
            3 => 'Cukup',
            4 => 'Baik',
            5 => 'Sangat Baik',
            default => 'Cukup',
        };
    }

    /**
     * Determine aspect conclusion
     */
    private function determineAspectConclusion(float $gapRating): string
    {
        if ($gapRating < -0.5) {
            return 'below_standard';
        } elseif ($gapRating < 0.5) {
            return 'meets_standard';
        } else {
            return 'exceeds_standard';
        }
    }

    /**
     * Get aspect conclusion text
     */
    private function getAspectConclusionText(string $code): string
    {
        return match ($code) {
            'below_standard' => 'Kurang Memenuhi Standard',
            'meets_standard' => 'Memenuhi Standard',
            'exceeds_standard' => 'Melebihi Standard',
            default => 'Memenuhi Standard',
        };
    }

    /**
     * Determine category conclusion
     */
    private function determineCategoryConclusion(float $gapScore): string
    {
        if ($gapScore < -10) {
            return 'DBS';
        } elseif ($gapScore < 0) {
            return 'MS';
        } elseif ($gapScore < 20) {
            return 'K';
        } else {
            return 'SK';
        }
    }

    /**
     * Get category conclusion text
     */
    private function getCategoryConclusionText(string $code): string
    {
        return match ($code) {
            'DBS' => 'DI BAWAH STANDARD',
            'MS' => 'MEMENUHI STANDARD',
            'K' => 'KOMPETEN',
            'SK' => 'SANGAT KOMPETEN',
            default => 'MEMENUHI STANDARD',
        };
    }

    /**
     * Determine final conclusion based on achievement percentage
     */
    private function determineFinalConclusion(float $percentage): string
    {
        if ($percentage >= 120) {
            return 'SK'; // Sangat Kompeten
        } elseif ($percentage >= 100) {
            return 'K'; // Kompeten
        } elseif ($percentage >= 80) {
            return 'MS'; // Memenuhi Standard
        } else {
            return 'DBS'; // Di Bawah Standard
        }
    }

    /**
     * Get final conclusion text
     */
    private function getFinalConclusionText(string $code): string
    {
        return match ($code) {
            'SK' => 'SANGAT KOMPETEN',
            'K' => 'KOMPETEN',
            'MS' => 'MEMENUHI STANDARD',
            'DBS' => 'DI BAWAH STANDARD',
            default => 'MEMENUHI STANDARD',
        };
    }

    /**
     * ⚡ Clear Eloquent memory to prevent memory leak
     */
    private function clearEloquentMemory(): void
    {
        // Clear query log
        DB::connection()->flushQueryLog();

        // Reset Eloquent's internal caches
        Participant::clearBootedModels();

        // Clear model event listeners cache
        foreach (
            [
                Participant::class,
                CategoryAssessment::class,
                AspectAssessment::class,
                SubAspectAssessment::class,
                FinalAssessment::class,
            ] as $model
        ) {
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
