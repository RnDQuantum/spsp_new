<?php

namespace Database\Seeders;

use App\Models\Aspect;
use App\Models\AssessmentTemplate;
use App\Models\CategoryType;
use App\Models\SubAspect;
use Illuminate\Database\Seeder;

class MasterDataSeeder extends Seeder
{
    /**
     * Aspect Library - Collection of all available aspects and sub-aspects
     * Can be mixed and matched per template for realistic variations
     */
    private array $potensiAspectLibrary = [];

    private array $kompetensiAspectLibrary = [];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Initialize aspect libraries
        $this->initializePotensiLibrary();
        $this->initializeKompetensiLibrary();

        // Seed templates with different configurations
        $this->seedStaffTemplate();
        $this->seedSupervisorTemplate();
        $this->seedManagerTemplate();
        $this->seedProfessionalTemplate();
        $this->seedP3kTemplate();
    }

    /**
     * Initialize Potensi Aspect Library
     * Contains all available Potensi aspects with their sub-aspects
     * Based on realistic assessment bank data
     */
    private function initializePotensiLibrary(): void
    {
        // 1. INTELEKTUAL (Gabungan: Intelegensi + Intelektual dan Kemampuan Umum)
        $this->potensiAspectLibrary['intelektual'] = [
            'code' => 'intelektual',
            'name' => 'Intelektual',
            'description' => 'Mengukur kemampuan berpikir, analisa, logika, dan kreativitas dalam penyelesaian masalah.',
            'sub_aspects' => [
                ['code' => 'kecerdasan_umum', 'name' => 'Kecerdasan Umum', 'description' => 'Kemampuan intelektual secara umum', 'standard_rating' => 3],
                ['code' => 'daya_tangkap', 'name' => 'Daya Tangkap', 'description' => 'Kemampuan memahami informasi baru', 'standard_rating' => 3],
                ['code' => 'daya_analisa', 'name' => 'Daya Analisa', 'description' => 'Kemampuan mengurai masalah menjadi komponen yang lebih kecil', 'standard_rating' => 3],
                ['code' => 'logika_berpikir', 'name' => 'Logika Berpikir', 'description' => 'Kemampuan berpikir runtut dan masuk akal', 'standard_rating' => 3],
                ['code' => 'daya_ingat', 'name' => 'Daya Ingat', 'description' => 'Kemampuan menyimpan dan memanggil kembali informasi', 'standard_rating' => 3],
                ['code' => 'daya_konsentrasi', 'name' => 'Daya Konsentrasi', 'description' => 'Kemampuan memusatkan perhatian pada tugas', 'standard_rating' => 3],
                ['code' => 'daya_abstraksi', 'name' => 'Daya Abstraksi', 'description' => 'Kemampuan menangkap inti permasalahan dari hal-hal yang tidak nyata', 'standard_rating' => 3],
                ['code' => 'kemampuan_numerik', 'name' => 'Kemampuan Numerik', 'description' => 'Kemampuan bekerja dengan angka dan data kuantitatif', 'standard_rating' => 3],
                ['code' => 'kreativitas', 'name' => 'Kreativitas', 'description' => 'Kemampuan menghasilkan ide-ide baru yang bermanfaat', 'standard_rating' => 3],
                ['code' => 'komunikasi_intelektual', 'name' => 'Komunikasi', 'description' => 'Kemampuan menyampaikan gagasan secara lisan maupun tulisan', 'standard_rating' => 3],
            ],
        ];

        // 2. SIKAP & CARA KERJA
        $this->potensiAspectLibrary['sikap_cara_kerja'] = [
            'code' => 'sikap_cara_kerja',
            'name' => 'Sikap & Cara Kerja',
            'description' => 'Mengukur perilaku dan pendekatan seseorang dalam melaksanakan tugas pekerjaannya.',
            'sub_aspects' => [
                ['code' => 'sistematika_kerja', 'name' => 'Sistematika Kerja', 'description' => 'Keteraturan dalam merencanakan dan melaksanakan pekerjaan', 'standard_rating' => 3],
                ['code' => 'ketelitian', 'name' => 'Ketelitian', 'description' => 'Kecermatan dalam bekerja untuk meminimalkan kesalahan', 'standard_rating' => 3],
                ['code' => 'kerapian_kerja', 'name' => 'Kerapian Kerja', 'description' => 'Kebersihan dan keteraturan hasil kerja', 'standard_rating' => 3],
                ['code' => 'tempo_kerja', 'name' => 'Tempo Kerja', 'description' => 'Kecepatan dalam menuntaskan tugas', 'standard_rating' => 3],
                ['code' => 'ketekunan', 'name' => 'Ketekunan', 'description' => 'Daya tahan untuk tetap bekerja sampai tuntas', 'standard_rating' => 3],
                ['code' => 'mobilitas', 'name' => 'Mobilitas', 'description' => 'Kesiapan untuk bergerak dan beradaptasi dengan lokasi kerja', 'standard_rating' => 3],
                ['code' => 'kerjasama', 'name' => 'Kerjasama', 'description' => 'Kemampuan bekerja efektif dalam kelompok', 'standard_rating' => 3],
                ['code' => 'kedisiplinan', 'name' => 'Kedisiplinan', 'description' => 'Kepatuhan terhadap norma dan aturan yang berlaku', 'standard_rating' => 3],
                ['code' => 'tanggung_jawab', 'name' => 'Tanggung Jawab', 'description' => 'Kesadaran untuk menyelesaikan kewajiban dengan sebaik-baiknya', 'standard_rating' => 3],
            ],
        ];

        // 3. POTENSI KERJA
        $this->potensiAspectLibrary['potensi_kerja'] = [
            'code' => 'potensi_kerja',
            'name' => 'Potensi Kerja',
            'description' => 'Mengukur dorongan internal untuk berprestasi dan bertahan dalam situasi kerja.',
            'sub_aspects' => [
                ['code' => 'motivasi_kerja', 'name' => 'Motivasi Kerja', 'description' => 'Dorongan dari dalam diri untuk bekerja', 'standard_rating' => 3],
                ['code' => 'inisiatif', 'name' => 'Inisiatif', 'description' => 'Kemampuan mengambil langkah awal tanpa menunggu perintah', 'standard_rating' => 3],
                ['code' => 'semangat_kerja', 'name' => 'Semangat Kerja', 'description' => 'Gairah dan energi positif dalam bekerja', 'standard_rating' => 3],
                ['code' => 'daya_tahan_kerja', 'name' => 'Daya Tahan Kerja', 'description' => 'Kemampuan fisik dan mental dalam menghadapi beban kerja', 'standard_rating' => 3],
                ['code' => 'hasrat_berprestasi', 'name' => 'Hasrat Berprestasi', 'description' => 'Keinginan kuat untuk mencapai hasil terbaik', 'standard_rating' => 3],
                ['code' => 'kestabilan_kerja', 'name' => 'Kestabilan Kerja', 'description' => 'Konsistensi performa dalam jangka waktu tertentu', 'standard_rating' => 3],
                ['code' => 'daya_kompetisi', 'name' => 'Daya Kompetisi', 'description' => 'Keinginan untuk menjadi lebih baik dibandingkan orang lain/standar', 'standard_rating' => 3],
            ],
        ];

        // 4. SOSIALITAS (Human Relation)
        $this->potensiAspectLibrary['sosualitas'] = [
            'code' => 'sosualitas',
            'name' => 'Sosualitas',
            'description' => 'Mengukur kemampuan membina hubungan antar manusia.',
            'sub_aspects' => [
                ['code' => 'kepekaan_interpersonal', 'name' => 'Kepekaan Interpersonal', 'description' => 'Kemampuan memahami perasaan dan situasi orang lain', 'standard_rating' => 3],
                ['code' => 'komunikasi_sosial', 'name' => 'Komunikasi Sosial', 'description' => 'Kemampuan berinteraksi luwes dalam pergaulan', 'standard_rating' => 3],
                ['code' => 'kontak_sosial', 'name' => 'Kontak Sosial', 'description' => 'Kecenderungan untuk berada di lingkungan sosial', 'standard_rating' => 3],
                ['code' => 'penyesuaian_diri', 'name' => 'Penyesuaian Diri', 'description' => 'Kemampuan beradaptasi dengan lingkungan baru', 'standard_rating' => 3],
            ],
        ];

        // 5. KEPRIBADIAN (Emosi + Lengkap)
        $this->potensiAspectLibrary['kepribadian'] = [
            'code' => 'kepribadian',
            'name' => 'Kepribadian',
            'description' => 'Mengukur aspek-aspek personalitas, emosi, dan integritas diri.',
            'sub_aspects' => [
                ['code' => 'kepercayaan_diri', 'name' => 'Kepercayaan Diri', 'description' => 'Keyakinan akan kemampuan diri sendiri', 'standard_rating' => 3],
                ['code' => 'stabilitas_emosi', 'name' => 'Stabilitas Emosi', 'description' => 'Kemampuan mengelola emosi secara seimbang', 'standard_rating' => 3],
                ['code' => 'daya_tahan_stres', 'name' => 'Daya Tahan Stres', 'description' => 'Kemampuan tetap tenang di bawah tekanan', 'standard_rating' => 3],
                ['code' => 'kemandirian', 'name' => 'Kemandirian', 'description' => 'Kemampuan bertindak tanpa bergantung pada orang lain', 'standard_rating' => 3],
                ['code' => 'kematangan_diri', 'name' => 'Kematangan Diri', 'description' => 'Sikap dewasa dalam menyikapi masalah pribadi', 'standard_rating' => 3],
                ['code' => 'kematangan_sosial', 'name' => 'Kematangan Sosial', 'description' => 'Sikap dewasa dalam menyikapi masalah sosial', 'standard_rating' => 3],
                ['code' => 'kejujuran', 'name' => 'Kejujuran', 'description' => 'Kesesuaian antara perkataan dan perbuatan', 'standard_rating' => 3],
                ['code' => 'loyalitas', 'name' => 'Loyalitas', 'description' => 'Kesetiaan terhadap organisasi atau pimpinan', 'standard_rating' => 3],
                ['code' => 'penampilan', 'name' => 'Penampilan', 'description' => 'Kerapian dan kepantasan dalam berpenampilan', 'standard_rating' => 3],
            ],
        ];

        // 6. MANAJERIAL
        $this->potensiAspectLibrary['manajerial'] = [
            'code' => 'manajerial',
            'name' => 'Manajerial',
            'description' => 'Mengukur kemampuan pengelolaan tugas, orang, dan situasi.',
            'sub_aspects' => [
                ['code' => 'identifikasi_masalah', 'name' => 'Identifikasi Masalah', 'description' => 'Kemampuan menemukan inti persoalan', 'standard_rating' => 4],
                ['code' => 'analisa_sintesa', 'name' => 'Analisa dan Sintesa', 'description' => 'Kemampuan mengurai dan menyimpulkan kembali informasi', 'standard_rating' => 4],
                ['code' => 'pemecahan_masalah', 'name' => 'Pemecahan Masalah', 'description' => 'Kemampuan memberikan solusi efektif', 'standard_rating' => 4],
                ['code' => 'perencanaan', 'name' => 'Perencanaan', 'description' => 'Kemampuan menyusun langkah-langkah pencapaian tujuan', 'standard_rating' => 4],
                ['code' => 'koordinasi', 'name' => 'Koordinasi', 'description' => 'Kemampuan menyelaraskan berbagai aktivitas/orang', 'standard_rating' => 4],
                ['code' => 'mempengaruhi', 'name' => 'Mempengaruhi', 'description' => 'Kemampuan meyakinkan orang lain', 'standard_rating' => 4],
                ['code' => 'mengarahkan', 'name' => 'Mengarahkan', 'description' => 'Kemampuan memberikan petunjuk yang jelas', 'standard_rating' => 4],
                ['code' => 'pengendalian', 'name' => 'Pengendalian', 'description' => 'Kemampuan memantau dan mengoreksi proses', 'standard_rating' => 4],
                ['code' => 'pembuatan_keputusan', 'name' => 'Pembuatan Keputusan', 'description' => 'Kemampuan memilih alternatif terbaik', 'standard_rating' => 4],
                ['code' => 'pengetahuan_manajemen', 'name' => 'Pengetahuan Manajemen', 'description' => 'Pemahaman tentang prinsip-prinsip manajemen', 'standard_rating' => 4],
            ],
        ];

        // 7. GOAL & OBJECTIVE SETTING
        $this->potensiAspectLibrary['goal_setting'] = [
            'code' => 'goal_setting',
            'name' => 'Goal & Objective Setting',
            'description' => 'Mengukur kemampuan penetapan tujuan strategis.',
            'sub_aspects' => [
                ['code' => 'vision_clarity', 'name' => 'Vision Clarity', 'description' => 'Kejelasan visi ke depan', 'standard_rating' => 4],
                ['code' => 'direction_setting', 'name' => 'Direction Setting', 'description' => 'Penetapan arah dan target', 'standard_rating' => 4],
                ['code' => 'planning_organizing', 'name' => 'Planning & Organizing', 'description' => 'Perencanaan dan pengorganisasian', 'standard_rating' => 4],
                ['code' => 'measurement_milestones', 'name' => 'Measurement', 'description' => 'Pengukuran pencapaian', 'standard_rating' => 4],
                ['code' => 'communication_engagement', 'name' => 'Communication', 'description' => 'Komunikasi visi untuk mendorong keterlibatan', 'standard_rating' => 4],
                ['code' => 'motivations_commitment', 'name' => 'Commitment', 'description' => 'Membangun motivasi dan komitmen', 'standard_rating' => 4],
                ['code' => 'result_focus', 'name' => 'Result Focus', 'description' => 'Fokus pada hasil akhir', 'standard_rating' => 4],
            ],
        ];

        // 8. CREATIVE LEADERSHIP
        $this->potensiAspectLibrary['creative_leadership'] = [
            'code' => 'creative_leadership',
            'name' => 'Creative Leadership',
            'description' => 'Mengukur kepemimpinan yang inovatif dan adaptif.',
            'sub_aspects' => [
                ['code' => 'kepemimpinan', 'name' => 'Kepemimpinan', 'description' => 'Kemampuan memimpin tim', 'standard_rating' => 4],
                ['code' => 'agen_perubahan', 'name' => 'Agen Perubahan', 'description' => 'Kemampuan mendorong perubahan positif', 'standard_rating' => 4],
                ['code' => 'kreativitas_leadership', 'name' => 'Kreativitas', 'description' => 'Kreativitas dalam pendekatan kepemimpinan', 'standard_rating' => 4],
            ],
        ];
    }

    /**
     * Initialize Kompetensi Aspect Library
     * Contains all available Kompetensi aspects (no sub-aspects)
     */
    private function initializeKompetensiLibrary(): void
    {
        // Basic competencies (for entry-level positions)
        $this->kompetensiAspectLibrary['basic'] = [
            ['code' => 'integritas', 'name' => 'Integritas', 'description' => 'Konsisten berperilaku selaras dengan nilai, norma dan/atau etika organisasi. Level 3: Mengikuti nilai dan etika organisasi dengan konsisten.', 'standard_rating' => 3],
            ['code' => 'kerjasama', 'name' => 'Kerjasama', 'description' => 'Kemampuan menjalin hubungan kerja yang efektif. Level 3: Berpartisipasi aktif dalam tim untuk mencapai tujuan bersama.', 'standard_rating' => 3],
            ['code' => 'komunikasi', 'name' => 'Komunikasi', 'description' => 'Kemampuan menyampaikan informasi secara jelas. Level 3: Berkomunikasi dengan jelas dan efektif dalam berbagai situasi.', 'standard_rating' => 3],
            ['code' => 'orientasi_pada_hasil', 'name' => 'Orientasi Pada Hasil', 'description' => 'Komitmen untuk menyelesaikan tugas dengan baik. Level 3: Menyelesaikan tugas sesuai target dengan kualitas yang baik.', 'standard_rating' => 3],
            ['code' => 'pelayanan_publik', 'name' => 'Pelayanan Publik', 'description' => 'Kemampuan melayani publik secara profesional. Level 3: Memberikan pelayanan publik yang profesional dan transparan.', 'standard_rating' => 3],
            ['code' => 'pengembangan_diri_orang_lain', 'name' => 'Pengembangan Diri & Orang Lain', 'description' => 'Kemampuan mengembangkan diri dan orang lain. Level 3: Aktif mengembangkan diri dan membantu pengembangan rekan kerja.', 'standard_rating' => 3],
            ['code' => 'mengelola_perubahan', 'name' => 'Mengelola Perubahan', 'description' => 'Kemampuan beradaptasi dengan perubahan. Level 3: Menyesuaikan diri dengan perubahan dan mendukung implementasinya.', 'standard_rating' => 3],
        ];

        // Advanced competencies (for supervisory/managerial positions)
        $this->kompetensiAspectLibrary['advanced'] = [
            ['code' => 'integritas', 'name' => 'Integritas', 'description' => 'Konsisten berperilaku selaras dengan nilai, norma dan/atau etika organisasi, menciptakan budaya etika tinggi. Level 4: Menciptakan situasi kerja yang mendorong kepatuhan pada nilai, norma, dan etika organisasi.', 'standard_rating' => 4],
            ['code' => 'kerjasama', 'name' => 'Kerjasama', 'description' => 'Kemampuan menjalin hubungan kerja yang efektif dan membangun komitmen tim. Level 4: Membangun komitmen tim dan sinergi untuk mencapai tujuan strategis.', 'standard_rating' => 4],
            ['code' => 'komunikasi', 'name' => 'Komunikasi', 'description' => 'Kemampuan mengemukakan pemikiran multidimensi untuk mendorong kesepakatan. Level 4: Mampu mengemukakan pemikiran kompleks untuk meningkatkan kinerja keseluruhan.', 'standard_rating' => 4],
            ['code' => 'orientasi_pada_hasil', 'name' => 'Orientasi Pada Hasil', 'description' => 'Kemampuan mendorong unit kerja mencapai target. Level 4: Mendorong unit kerja mencapai target yang ditetapkan atau melebihi hasil kerja sebelumnya.', 'standard_rating' => 4],
            ['code' => 'pelayanan_publik', 'name' => 'Pelayanan Publik', 'description' => 'Kemampuan memonitor dan mengevaluasi dampak pelayanan publik. Level 4: Mampu memonitor dan mengantisipasi dampak jangka panjang dalam pelayanan publik.', 'standard_rating' => 4],
            ['code' => 'pengembangan_diri_orang_lain', 'name' => 'Pengembangan Diri & Orang Lain', 'description' => 'Kemampuan menyusun program pengembangan. Level 4: Menyusun program pengembangan jangka panjang untuk manajemen pembelajaran.', 'standard_rating' => 4],
            ['code' => 'mengelola_perubahan', 'name' => 'Mengelola Perubahan', 'description' => 'Kemampuan memimpin perubahan. Level 4: Memimpin perubahan pada unit kerja secara efektif.', 'standard_rating' => 4],
            ['code' => 'pengambilan_keputusan', 'name' => 'Pengambilan Keputusan', 'description' => 'Kemampuan membuat keputusan strategis dengan mitigasi risiko. Level 4: Menyelesaikan masalah yang mengandung risiko tinggi dengan tindakan pengamanan.', 'standard_rating' => 4],
            ['code' => 'perekat_bangsa', 'name' => 'Perekat Bangsa', 'description' => 'Kemampuan mendayagunakan perbedaan untuk efektivitas organisasi. Level 4: Mendayagunakan perbedaan secara konstruktif dan kreatif.', 'standard_rating' => 4],
        ];
    }

    /**
     * Seed Staff Standard Template (Balanced: 50% Potensi, 50% Kompetensi)
     * For entry-level positions with basic requirements
     */
    private function seedStaffTemplate(): void
    {
        $template = AssessmentTemplate::where('code', 'staff_standard_v1')->firstOrFail();

        // Create categories
        $potensiCategory = CategoryType::create([
            'template_id' => $template->id,
            'code' => 'potensi',
            'name' => 'Potensi',
            'weight_percentage' => 50,
            'order' => 1,
        ]);

        $kompetensiCategory = CategoryType::create([
            'template_id' => $template->id,
            'code' => 'kompetensi',
            'name' => 'Kompetensi',
            'weight_percentage' => 50,
            'order' => 2,
        ]);

        // Potensi aspects: Entry Level Standard
        $potensiAspects = [
            ['library_key' => 'intelektual', 'weight' => 25],          // 25%
            ['library_key' => 'sikap_cara_kerja', 'weight' => 25],     // 25%
            ['library_key' => 'potensi_kerja', 'weight' => 20],        // 20%
            ['library_key' => 'sosualitas', 'weight' => 15],           // 15%
            ['library_key' => 'kepribadian', 'weight' => 15],          // 15%
        ];                                                             // TOTAL = 100%

        $this->seedPotensiAspects($template->id, $potensiCategory->id, $potensiAspects);

        // Kompetensi aspects: Basic level (7 aspects)
        $this->seedKompetensiAspects($template->id, $kompetensiCategory->id, 'basic', [
            'integritas' => 15,
            'kerjasama' => 14,
            'komunikasi' => 14,
            'orientasi_pada_hasil' => 14,
            'pelayanan_publik' => 14,
            'pengembangan_diri_orang_lain' => 15,
            'mengelola_perubahan' => 14,
        ]);
    }

    /**
     * Seed Supervisor Standard Template (Competency-heavy: 30% Potensi, 70% Kompetensi)
     * For supervisory positions with leadership requirements
     */
    private function seedSupervisorTemplate(): void
    {
        $template = AssessmentTemplate::where('code', 'supervisor_standard_v1')->firstOrFail();

        // Create categories
        $potensiCategory = CategoryType::create([
            'template_id' => $template->id,
            'code' => 'potensi',
            'name' => 'Potensi',
            'weight_percentage' => 30,
            'order' => 1,
        ]);

        $kompetensiCategory = CategoryType::create([
            'template_id' => $template->id,
            'code' => 'kompetensi',
            'name' => 'Kompetensi',
            'weight_percentage' => 70,
            'order' => 2,
        ]);

        // Potensi aspects: Supervisory Level
        $potensiAspects = [
            ['library_key' => 'intelektual', 'weight' => 20],          // 20%
            ['library_key' => 'sikap_cara_kerja', 'weight' => 20],     // 20%
            ['library_key' => 'manajerial', 'weight' => 20],           // 20%
            ['library_key' => 'creative_leadership', 'weight' => 15],  // 15%
            ['library_key' => 'sosualitas', 'weight' => 10],           // 10%
            ['library_key' => 'kepribadian', 'weight' => 15],          // 15%
        ];                                                             // TOTAL = 100%

        $this->seedPotensiAspects($template->id, $potensiCategory->id, $potensiAspects);

        // Kompetensi aspects: Advanced level (9 aspects) with emphasis on leadership
        $this->seedKompetensiAspects($template->id, $kompetensiCategory->id, 'advanced', [
            'integritas' => 12,
            'kerjasama' => 11,
            'komunikasi' => 11,
            'orientasi_pada_hasil' => 11,
            'pelayanan_publik' => 11,
            'pengembangan_diri_orang_lain' => 11,
            'mengelola_perubahan' => 11,
            'pengambilan_keputusan' => 11,
            'perekat_bangsa' => 11,
        ]);
    }

    /**
     * Seed Manager Standard Template (Standard: 40% Potensi, 60% Kompetensi)
     * For managerial positions with strategic thinking requirements
     */
    private function seedManagerTemplate(): void
    {
        $template = AssessmentTemplate::where('code', 'manager_standard_v1')->firstOrFail();

        // Create categories
        $potensiCategory = CategoryType::create([
            'template_id' => $template->id,
            'code' => 'potensi',
            'name' => 'Potensi',
            'weight_percentage' => 40,
            'order' => 1,
        ]);

        $kompetensiCategory = CategoryType::create([
            'template_id' => $template->id,
            'code' => 'kompetensi',
            'name' => 'Kompetensi',
            'weight_percentage' => 60,
            'order' => 2,
        ]);

        // Potensi aspects: Managerial Level (Strategic)
        $potensiAspects = [
            ['library_key' => 'intelektual', 'weight' => 15],          // 15%
            ['library_key' => 'manajerial', 'weight' => 25],           // 25%
            ['library_key' => 'creative_leadership', 'weight' => 20],  // 20%
            ['library_key' => 'goal_setting', 'weight' => 15],         // 15%
            ['library_key' => 'potensi_kerja', 'weight' => 10],        // 10%
            ['library_key' => 'kepribadian', 'weight' => 15],          // 15%
        ];                                                             // TOTAL = 100%

        $this->seedPotensiAspects($template->id, $potensiCategory->id, $potensiAspects);

        // Kompetensi aspects: Advanced level (9 aspects)
        $this->seedKompetensiAspects($template->id, $kompetensiCategory->id, 'advanced', [
            'integritas' => 12,
            'kerjasama' => 11,
            'komunikasi' => 11,
            'orientasi_pada_hasil' => 11,
            'pelayanan_publik' => 11,
            'pengembangan_diri_orang_lain' => 11,
            'mengelola_perubahan' => 11,
            'pengambilan_keputusan' => 11,
            'perekat_bangsa' => 11,
        ]);
    }

    /**
     * Seed Professional Standard Template (45% Potensi, 55% Kompetensi)
     * For specialized professional positions
     */
    private function seedProfessionalTemplate(): void
    {
        $template = AssessmentTemplate::where('code', 'professional_standard_v1')->firstOrFail();

        // Create categories
        $potensiCategory = CategoryType::create([
            'template_id' => $template->id,
            'code' => 'potensi',
            'name' => 'Potensi',
            'weight_percentage' => 45,
            'order' => 1,
        ]);

        $kompetensiCategory = CategoryType::create([
            'template_id' => $template->id,
            'code' => 'kompetensi',
            'name' => 'Kompetensi',
            'weight_percentage' => 55,
            'order' => 2,
        ]);

        // Potensi aspects: Professional / Specialist (All Aspects)
        $potensiAspects = [
            ['library_key' => 'intelektual', 'weight' => 15],          // 15%
            ['library_key' => 'sikap_cara_kerja', 'weight' => 15],     // 15%
            ['library_key' => 'potensi_kerja', 'weight' => 10],        // 10%
            ['library_key' => 'sosualitas', 'weight' => 10],           // 10%
            ['library_key' => 'kepribadian', 'weight' => 10],          // 10%
            ['library_key' => 'manajerial', 'weight' => 15],           // 15%
            ['library_key' => 'goal_setting', 'weight' => 15],         // 15%
            ['library_key' => 'creative_leadership', 'weight' => 10],  // 10%
        ];                                                             // TOTAL = 100%

        $this->seedPotensiAspects($template->id, $potensiCategory->id, $potensiAspects);

        // Kompetensi aspects: Mix of basic and advanced (8 aspects)
        $this->seedKompetensiAspects($template->id, $kompetensiCategory->id, 'advanced', [
            'integritas' => 13,
            'kerjasama' => 12,
            'komunikasi' => 12,
            'orientasi_pada_hasil' => 13,
            'pelayanan_publik' => 13,
            'pengembangan_diri_orang_lain' => 12,
            'mengelola_perubahan' => 13,
            'pengambilan_keputusan' => 12,
        ]);
    }

    /**
     * Seed P3K Standard 2025 Template (Legacy: 40% Potensi, 60% Kompetensi)
     * For P3K recruitment with standard government requirements
     */
    private function seedP3kTemplate(): void
    {
        $template = AssessmentTemplate::where('code', 'p3k_standard_2025')->firstOrFail();

        // Create categories
        $potensiCategory = CategoryType::create([
            'template_id' => $template->id,
            'code' => 'potensi',
            'name' => 'Potensi',
            'weight_percentage' => 40,
            'order' => 1,
        ]);

        $kompetensiCategory = CategoryType::create([
            'template_id' => $template->id,
            'code' => 'kompetensi',
            'name' => 'Kompetensi',
            'weight_percentage' => 60,
            'order' => 2,
        ]);

        // Potensi aspects: P3K Standard (Using new aspects)
        $potensiAspects = [
            ['library_key' => 'intelektual', 'weight' => 20],          // 20%
            ['library_key' => 'sikap_cara_kerja', 'weight' => 25],     // 25%
            ['library_key' => 'potensi_kerja', 'weight' => 20],        // 20%
            ['library_key' => 'sosualitas', 'weight' => 20],           // 20%
            ['library_key' => 'kepribadian', 'weight' => 15],          // 15%
        ];                                                             // TOTAL = 100%

        $this->seedPotensiAspects($template->id, $potensiCategory->id, $potensiAspects);

        // Kompetensi aspects: Advanced level (9 aspects) - government standard
        $this->seedKompetensiAspects($template->id, $kompetensiCategory->id, 'advanced', [
            'integritas' => 12,
            'kerjasama' => 11,
            'komunikasi' => 11,
            'orientasi_pada_hasil' => 11,
            'pelayanan_publik' => 11,
            'pengembangan_diri_orang_lain' => 11,
            'mengelola_perubahan' => 11,
            'pengambilan_keputusan' => 11,
            'perekat_bangsa' => 11,
        ]);
    }

    /**
     * Helper: Seed Potensi aspects from library
     */
    private function seedPotensiAspects(int $templateId, int $categoryId, array $aspects): void
    {
        $order = 1;

        foreach ($aspects as $aspectConfig) {
            $libraryAspect = $this->potensiAspectLibrary[$aspectConfig['library_key']];

            // Calculate standard_rating as AVERAGE of sub-aspects
            $standardRating = collect($libraryAspect['sub_aspects'])->avg('standard_rating');

            // Create aspect
            $aspect = Aspect::create([
                'template_id' => $templateId,
                'category_type_id' => $categoryId,
                'code' => $libraryAspect['code'],
                'name' => $libraryAspect['name'],
                'description' => $libraryAspect['description'],
                'weight_percentage' => $aspectConfig['weight'],
                'standard_rating' => round($standardRating, 2),
                'order' => $order++,
            ]);

            // Create sub-aspects
            $subOrder = 1;
            foreach ($libraryAspect['sub_aspects'] as $subAspect) {
                SubAspect::create([
                    'aspect_id' => $aspect->id,
                    'code' => $subAspect['code'],
                    'name' => $subAspect['name'],
                    'description' => $subAspect['description'],
                    'standard_rating' => $subAspect['standard_rating'],
                    'order' => $subOrder++,
                ]);
            }
        }
    }

    /**
     * Helper: Seed Kompetensi aspects from library
     */
    private function seedKompetensiAspects(int $templateId, int $categoryId, string $libraryKey, array $aspectWeights): void
    {
        $library = $this->kompetensiAspectLibrary[$libraryKey];
        $order = 1;

        foreach ($aspectWeights as $code => $weight) {
            // Find aspect in library
            $libraryAspect = collect($library)->firstWhere('code', $code);

            if (!$libraryAspect) {
                continue;
            }

            // Create aspect (no sub-aspects for Kompetensi)
            Aspect::create([
                'template_id' => $templateId,
                'category_type_id' => $categoryId,
                'code' => $libraryAspect['code'],
                'name' => $libraryAspect['name'],
                'description' => $libraryAspect['description'],
                'weight_percentage' => $weight,
                'standard_rating' => $libraryAspect['standard_rating'],
                'order' => $order++,
            ]);
        }
    }
}
