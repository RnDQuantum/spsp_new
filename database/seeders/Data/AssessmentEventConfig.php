<?php

declare(strict_types=1);

namespace Database\Seeders\Data;

/**
 * AssessmentEventConfig - Master Konfigurasi Event, Proyek, Batches, & Formasi Jabatan
 *
 * Menyimpan konfigurasi 15 pelaksanaan asesmen (Jalur A Legacy & Jalur B API Online)
 * untuk kebutuhan seeding data SPSP.
 */
class AssessmentEventConfig
{
    /**
     * Mengembalikan seluruh konfigurasi event asesmen.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function getConfigurations(): array
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
                'batches' => [
                    ['code' => 'BATCH-1-MOJOKERTO', 'name' => 'Gelombang 1 - Mojokerto', 'location' => 'Mojokerto', 'batch_number' => 1, 'start_date' => '2025-09-27', 'end_date' => '2025-09-28'],
                    ['code' => 'BATCH-2-SURABAYA', 'name' => 'Gelombang 2 - Surabaya', 'location' => 'Surabaya', 'batch_number' => 2, 'start_date' => '2025-10-15', 'end_date' => '2025-10-16'],
                    ['code' => 'BATCH-3-JAKARTA', 'name' => 'Gelombang 3 - Jakarta', 'location' => 'Jakarta', 'batch_number' => 3, 'start_date' => '2025-11-05', 'end_date' => '2025-11-06'],
                ],
                'positions' => [
                    ['code' => 'fisikawan_medis', 'name' => 'Fisikawan Medis', 'quota' => 20, 'template_code' => 'professional_standard_v1'],
                    ['code' => 'analis_kebijakan', 'name' => 'Analis Kebijakan', 'quota' => 30, 'template_code' => 'staff_standard_v1'],
                    ['code' => 'auditor', 'name' => 'Auditor', 'quota' => 25, 'template_code' => 'supervisor_standard_v1'],
                    ['code' => 'pranata_komputer', 'name' => 'Pranata Komputer', 'quota' => 25, 'template_code' => 'staff_standard_v1'],
                ],
                'participants_count' => 20000,
                'nine_box_distribution' => [
                    'K-1' => 5, 'K-2' => 10, 'K-3' => 5,
                    'K-4' => 10, 'K-5' => 20, 'K-6' => 10,
                    'K-7' => 15, 'K-8' => 15, 'K-9' => 10,
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
                'batches' => [
                    ['code' => 'BATCH-1-JAKARTA', 'name' => 'Gelombang 1 - Jakarta', 'location' => 'Jakarta', 'batch_number' => 1, 'start_date' => '2025-10-10', 'end_date' => '2025-10-11'],
                ],
                'positions' => [
                    ['code' => 'analis_hukum', 'name' => 'Analis Hukum', 'quota' => 50, 'template_code' => 'professional_standard_v1'],
                    ['code' => 'pengelola_perkara', 'name' => 'Pengelola Penanganan Perkara', 'quota' => 100, 'template_code' => 'staff_standard_v1'],
                ],
                'participants_count' => 10000,
                'nine_box_distribution' => [
                    'K-1' => 15, 'K-2' => 20, 'K-3' => 10,
                    'K-4' => 10, 'K-5' => 15, 'K-6' => 8,
                    'K-7' => 8, 'K-8' => 9, 'K-9' => 5,
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
                'batches' => [
                    ['code' => 'BATCH-1-BANDUNG', 'name' => 'Gelombang 1 - Bandung', 'location' => 'Bandung', 'batch_number' => 1, 'start_date' => '2025-10-10', 'end_date' => '2025-10-11'],
                    ['code' => 'BATCH-2-YOGYAKARTA', 'name' => 'Gelombang 2 - Yogyakarta', 'location' => 'Yogyakarta', 'batch_number' => 2, 'start_date' => '2025-11-10', 'end_date' => '2025-11-11'],
                ],
                'positions' => [
                    ['code' => 'dokter_umum', 'name' => 'Dokter Umum', 'quota' => 50, 'template_code' => 'professional_standard_v1'],
                    ['code' => 'perawat', 'name' => 'Perawat', 'quota' => 100, 'template_code' => 'staff_standard_v1'],
                    ['code' => 'apoteker', 'name' => 'Apoteker', 'quota' => 50, 'template_code' => 'supervisor_standard_v1'],
                ],
                'participants_count' => 2000,
                'nine_box_distribution' => [
                    'K-1' => 15, 'K-2' => 20, 'K-3' => 10,
                    'K-4' => 10, 'K-5' => 15, 'K-6' => 8,
                    'K-7' => 8, 'K-8' => 9, 'K-9' => 5,
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
                'batches' => [
                    ['code' => 'BATCH-1-JAKARTA', 'name' => 'Gelombang 1 - Jakarta', 'location' => 'Jakarta', 'batch_number' => 1, 'start_date' => '2025-01-15', 'end_date' => '2025-01-16'],
                    ['code' => 'BATCH-2-BANDUNG', 'name' => 'Gelombang 2 - Bandung', 'location' => 'Bandung', 'batch_number' => 2, 'start_date' => '2025-02-15', 'end_date' => '2025-02-16'],
                ],
                'positions' => [
                    ['code' => 'it_manager', 'name' => 'IT Manager', 'quota' => 30, 'template_code' => 'supervisor_standard_v1'],
                    ['code' => 'network_engineer', 'name' => 'Network Engineer', 'quota' => 50, 'template_code' => 'staff_standard_v1'],
                    ['code' => 'data_analyst', 'name' => 'Data Analyst', 'quota' => 40, 'template_code' => 'staff_standard_v1'],
                ],
                'participants_count' => 150,
                'nine_box_distribution' => [
                    'K-1' => 5, 'K-2' => 8, 'K-3' => 7,
                    'K-4' => 10, 'K-5' => 20, 'K-6' => 10,
                    'K-7' => 15, 'K-8' => 15, 'K-9' => 10,
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
                'batches' => [
                    ['code' => 'BATCH-1-JAKARTA', 'name' => 'Gelombang 1 - Jakarta', 'location' => 'Jakarta', 'batch_number' => 1, 'start_date' => '2025-02-10', 'end_date' => '2025-02-11'],
                ],
                'positions' => [
                    ['code' => 'branch_manager', 'name' => 'Branch Manager', 'quota' => 40, 'template_code' => 'supervisor_standard_v1'],
                    ['code' => 'risk_analyst', 'name' => 'Risk Analyst', 'quota' => 30, 'template_code' => 'professional_standard_v1'],
                    ['code' => 'relationship_officer', 'name' => 'Relationship Officer', 'quota' => 30, 'template_code' => 'staff_standard_v1'],
                ],
                'participants_count' => 150,
                'nine_box_distribution' => [
                    'K-1' => 5, 'K-2' => 8, 'K-3' => 7,
                    'K-4' => 10, 'K-5' => 20, 'K-6' => 10,
                    'K-7' => 15, 'K-8' => 15, 'K-9' => 10,
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
                'batches' => [
                    ['code' => 'BATCH-1-DEPOK', 'name' => 'Gelombang 1 - Depok', 'location' => 'Depok', 'batch_number' => 1, 'start_date' => '2025-03-15', 'end_date' => '2025-03-16'],
                ],
                'positions' => [
                    ['code' => 'dosen_teknik', 'name' => 'Dosen Teknik', 'quota' => 20, 'template_code' => 'professional_standard_v1'],
                    ['code' => 'dosen_ekonomi', 'name' => 'Dosen Ekonomi', 'quota' => 15, 'template_code' => 'professional_standard_v1'],
                    ['code' => 'dosen_kedokteran', 'name' => 'Dosen Kedokteran', 'quota' => 15, 'template_code' => 'professional_standard_v1'],
                ],
                'participants_count' => 100,
                'nine_box_distribution' => [
                    'K-1' => 5, 'K-2' => 6, 'K-3' => 6,
                    'K-4' => 8, 'K-5' => 18, 'K-6' => 12,
                    'K-7' => 15, 'K-8' => 17, 'K-9' => 13,
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
                'batches' => [
                    ['code' => 'BATCH-1-JAKARTA', 'name' => 'Gelombang 1 - Jakarta', 'location' => 'Jakarta', 'batch_number' => 1, 'start_date' => '2025-02-01', 'end_date' => '2025-02-02'],
                    ['code' => 'BATCH-2-JAKARTA', 'name' => 'Gelombang 2 - Jakarta', 'location' => 'Jakarta', 'batch_number' => 2, 'start_date' => '2025-06-01', 'end_date' => '2025-06-02'],
                ],
                'positions' => [
                    ['code' => 'software_engineer', 'name' => 'Software Engineer', 'quota' => 80, 'template_code' => 'staff_standard_v1'],
                    ['code' => 'product_manager', 'name' => 'Product Manager', 'quota' => 30, 'template_code' => 'supervisor_standard_v1'],
                    ['code' => 'data_scientist', 'name' => 'Data Scientist', 'quota' => 40, 'template_code' => 'professional_standard_v1'],
                ],
                'participants_count' => 100,
                'nine_box_distribution' => [
                    'K-1' => 5, 'K-2' => 8, 'K-3' => 7,
                    'K-4' => 10, 'K-5' => 20, 'K-6' => 10,
                    'K-7' => 15, 'K-8' => 15, 'K-9' => 10,
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
                'batches' => [
                    ['code' => 'BATCH-1-JAKARTA', 'name' => 'Gelombang 1 - Jakarta', 'location' => 'Jakarta', 'batch_number' => 1, 'start_date' => '2025-09-01', 'end_date' => '2025-09-02'],
                ],
                'positions' => [
                    ['code' => 'analis_sdm', 'name' => 'Analis SDM', 'quota' => 40, 'template_code' => 'professional_standard_v1'],
                    ['code' => 'admin_kepegawaian', 'name' => 'Admin Kepegawaian', 'quota' => 60, 'template_code' => 'staff_standard_v1'],
                ],
                'participants_count' => 1000,
                'nine_box_distribution' => [
                    'K-1' => 5, 'K-2' => 10, 'K-3' => 5,
                    'K-4' => 10, 'K-5' => 20, 'K-6' => 10,
                    'K-7' => 15, 'K-8' => 15, 'K-9' => 10,
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
                'batches' => [
                    ['code' => 'BATCH-1-JAKARTA', 'name' => 'Gelombang 1 - Jakarta', 'location' => 'Jakarta', 'batch_number' => 1, 'start_date' => '2025-07-01', 'end_date' => '2025-07-02'],
                    ['code' => 'BATCH-2-SURABAYA', 'name' => 'Gelombang 2 - Surabaya', 'location' => 'Surabaya', 'batch_number' => 2, 'start_date' => '2025-08-01', 'end_date' => '2025-08-02'],
                ],
                'positions' => [
                    ['code' => 'guru_sd', 'name' => 'Guru SD', 'quota' => 100, 'template_code' => 'professional_standard_v1'],
                    ['code' => 'guru_smp', 'name' => 'Guru SMP', 'quota' => 80, 'template_code' => 'professional_standard_v1'],
                ],
                'participants_count' => 100,
                'nine_box_distribution' => [
                    'K-1' => 7, 'K-2' => 10, 'K-3' => 8,
                    'K-4' => 10, 'K-5' => 20, 'K-6' => 10,
                    'K-7' => 12, 'K-8' => 13, 'K-9' => 10,
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
                'batches' => [
                    ['code' => 'BATCH-1-JAKARTA', 'name' => 'Gelombang 1 - Jakarta', 'location' => 'Jakarta', 'batch_number' => 1, 'start_date' => '2025-04-01', 'end_date' => '2025-04-02'],
                ],
                'positions' => [
                    ['code' => 'engineer', 'name' => 'Engineer', 'quota' => 50, 'template_code' => 'professional_standard_v1'],
                    ['code' => 'analyst', 'name' => 'Analyst', 'quota' => 30, 'template_code' => 'staff_standard_v1'],
                    ['code' => 'supervisor', 'name' => 'Supervisor', 'quota' => 20, 'template_code' => 'supervisor_standard_v1'],
                ],
                'participants_count' => 100,
                'nine_box_distribution' => [
                    'K-1' => 5, 'K-2' => 8, 'K-3' => 7,
                    'K-4' => 10, 'K-5' => 20, 'K-6' => 10,
                    'K-7' => 15, 'K-8' => 15, 'K-9' => 10,
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
                'batches' => [
                    ['code' => 'BATCH-1-YOGYAKARTA', 'name' => 'Gelombang 1 - Yogyakarta', 'location' => 'Yogyakarta', 'batch_number' => 1, 'start_date' => '2025-05-01', 'end_date' => '2025-05-02'],
                ],
                'positions' => [
                    ['code' => 'dosen_soshum', 'name' => 'Dosen Sosial Humaniora', 'quota' => 25, 'template_code' => 'professional_standard_v1'],
                    ['code' => 'dosen_saintek', 'name' => 'Dosen Saintek', 'quota' => 25, 'template_code' => 'professional_standard_v1'],
                ],
                'participants_count' => 100,
                'nine_box_distribution' => [
                    'K-1' => 5, 'K-2' => 6, 'K-3' => 6,
                    'K-4' => 8, 'K-5' => 18, 'K-6' => 12,
                    'K-7' => 15, 'K-8' => 17, 'K-9' => 13,
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
                'batches' => [
                    ['code' => 'BATCH-1-BANDUNG', 'name' => 'Gelombang 1 - Bandung', 'location' => 'Bandung', 'batch_number' => 1, 'start_date' => '2025-06-01', 'end_date' => '2025-06-02'],
                ],
                'positions' => [
                    ['code' => 'dosen_teknik', 'name' => 'Dosen Teknik', 'quota' => 30, 'template_code' => 'professional_standard_v1'],
                    ['code' => 'dosen_informatika', 'name' => 'Dosen Informatika', 'quota' => 20, 'template_code' => 'professional_standard_v1'],
                ],
                'participants_count' => 100,
                'nine_box_distribution' => [
                    'K-1' => 4, 'K-2' => 6, 'K-3' => 5,
                    'K-4' => 8, 'K-5' => 17, 'K-6' => 12,
                    'K-7' => 16, 'K-8' => 18, 'K-9' => 14,
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
                'batches' => [
                    ['code' => 'BATCH-1-JAKARTA', 'name' => 'Gelombang 1 - Jakarta', 'location' => 'Jakarta', 'batch_number' => 1, 'start_date' => '2025-02-15', 'end_date' => '2025-02-16'],
                ],
                'positions' => [
                    ['code' => 'mt_marketing', 'name' => 'MT Marketing', 'quota' => 30, 'template_code' => 'staff_standard_v1'],
                    ['code' => 'mt_supply_chain', 'name' => 'MT Supply Chain', 'quota' => 20, 'template_code' => 'staff_standard_v1'],
                ],
                'participants_count' => 100,
                'nine_box_distribution' => [
                    'K-1' => 5, 'K-2' => 6, 'K-3' => 6,
                    'K-4' => 8, 'K-5' => 18, 'K-6' => 12,
                    'K-7' => 15, 'K-8' => 17, 'K-9' => 13,
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
                'batches' => [
                    ['code' => 'BATCH-1-JAKARTA', 'name' => 'Gelombang 1 - Jakarta', 'location' => 'Jakarta', 'batch_number' => 1, 'start_date' => '2025-03-01', 'end_date' => '2025-03-02'],
                ],
                'positions' => [
                    ['code' => 'backend_engineer', 'name' => 'Backend Engineer', 'quota' => 40, 'template_code' => 'staff_standard_v1'],
                    ['code' => 'frontend_engineer', 'name' => 'Frontend Engineer', 'quota' => 30, 'template_code' => 'staff_standard_v1'],
                    ['code' => 'tech_lead', 'name' => 'Tech Lead', 'quota' => 10, 'template_code' => 'supervisor_standard_v1'],
                ],
                'participants_count' => 100,
                'nine_box_distribution' => [
                    'K-1' => 5, 'K-2' => 8, 'K-3' => 7,
                    'K-4' => 10, 'K-5' => 20, 'K-6' => 10,
                    'K-7' => 13, 'K-8' => 15, 'K-9' => 12,
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
                'batches' => [
                    ['code' => 'BATCH-1-JAKARTA', 'name' => 'Gelombang 1 - Jakarta', 'location' => 'Jakarta', 'batch_number' => 1, 'start_date' => '2025-04-01', 'end_date' => '2025-04-02'],
                ],
                'positions' => [
                    ['code' => 'dokter_spesialis', 'name' => 'Dokter Spesialis', 'quota' => 20, 'template_code' => 'professional_standard_v1'],
                    ['code' => 'perawat_icu', 'name' => 'Perawat ICU', 'quota' => 40, 'template_code' => 'staff_standard_v1'],
                    ['code' => 'tenaga_laboratorium', 'name' => 'Tenaga Laboratorium', 'quota' => 20, 'template_code' => 'staff_standard_v1'],
                ],
                'participants_count' => 100,
                'nine_box_distribution' => [
                    'K-1' => 5, 'K-2' => 8, 'K-3' => 7,
                    'K-4' => 10, 'K-5' => 20, 'K-6' => 10,
                    'K-7' => 15, 'K-8' => 15, 'K-9' => 10,
                ],
            ],

            // Configuration 16: Master Debug Assessment (100 Peserta x Seluruh 10 Alat Tes Lengkap)
            [
                'institution_code' => 'bkn',
                'project_code' => 'AP-999',
                'event' => [
                    'code' => 'PR-DEBUG-ALL',
                    'name' => 'Pelaksanaan Uji Coba Komprehensif Seluruh Alat Tes (100 Peserta)',
                    'description' => 'Simulasi komprehensif memuat 100 peserta dengan seluruh 10 instrumen tes psikometri (A.1, A.2, A.5, B.1, D.1, B.2, D.2, F.1, G.1, H.1), MMPI klinis, dan penilaian 9-Box.',
                    'year' => 2026,
                    'start_date' => '2026-01-15',
                    'end_date' => '2026-12-31',
                    'status' => 'completed',
                ],
                'batches' => [
                    ['code' => 'BATCH-DEBUG-01', 'name' => 'Gelombang 1 - Laboratorium Debug SPSP', 'location' => 'Pusat Riset Psikometri Jakarta', 'batch_number' => 1, 'start_date' => '2026-02-01', 'end_date' => '2026-02-02'],
                ],
                'positions' => [
                    ['code' => 'analis_talenta', 'name' => 'Analis Potensi & Pengembangan Talenta', 'quota' => 35, 'template_code' => 'professional_standard_v1'],
                    ['code' => 'supervisor_operasional', 'name' => 'Supervisor Operasional & Tata Kelola', 'quota' => 35, 'template_code' => 'supervisor_standard_v1'],
                    ['code' => 'pranata_it', 'name' => 'Pranata Komputer & Sistem Informasi', 'quota' => 30, 'template_code' => 'staff_standard_v1'],
                ],
                'participants_count' => 100,
                'nine_box_distribution' => [
                    'K-1' => 10, 'K-2' => 10, 'K-3' => 10,
                    'K-4' => 10, 'K-5' => 20, 'K-6' => 10,
                    'K-7' => 10, 'K-8' => 10, 'K-9' => 10,
                ],
            ],
        ];
    }
}
