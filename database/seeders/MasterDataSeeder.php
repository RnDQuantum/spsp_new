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
        // KECERDASAN - Basic (4 sub-aspects, rating 3)
        $this->potensiAspectLibrary['kecerdasan_basic'] = [
            'code' => 'kecerdasan',
            'name' => 'Kecerdasan',
            'description' => 'Mengukur kemampuan kognitif dan intelektual dalam memahami, menganalisa, dan memecahkan masalah secara logis dan sistematis.',
            'sub_aspects' => [
                ['code' => 'kecerdasan_umum', 'name' => 'Kecerdasan Umum', 'description' => 'Kemampuan intelektual secara umum', 'standard_rating' => 3],
                ['code' => 'daya_tangkap', 'name' => 'Daya Tangkap', 'description' => 'Kemampuan memahami informasi dengan cepat', 'standard_rating' => 3],
                ['code' => 'daya_analisa', 'name' => 'Daya Analisa', 'description' => 'Kemampuan menganalisa dan memecahkan masalah', 'standard_rating' => 3],
                ['code' => 'kemampuan_logika', 'name' => 'Kemampuan Logika', 'description' => 'Kemampuan berpikir logis dan sistematis', 'standard_rating' => 3],
            ],
        ];

        // KECERDASAN - Advanced (6 sub-aspects, rating 4)
        $this->potensiAspectLibrary['kecerdasan_advanced'] = [
            'code' => 'kecerdasan',
            'name' => 'Kecerdasan',
            'description' => 'Mengukur kemampuan kognitif tingkat tinggi untuk analisis kompleks, pemikiran strategis, dan kreativitas dalam pemecahan masalah.',
            'sub_aspects' => [
                ['code' => 'kecerdasan_umum', 'name' => 'Kecerdasan Umum', 'description' => 'Kemampuan intelektual secara umum', 'standard_rating' => 4],
                ['code' => 'daya_tangkap', 'name' => 'Daya Tangkap', 'description' => 'Kemampuan memahami informasi dengan cepat dan akurat', 'standard_rating' => 4],
                ['code' => 'daya_analisa', 'name' => 'Daya Analisa', 'description' => 'Kemampuan menganalisa masalah kompleks secara mendalam', 'standard_rating' => 4],
                ['code' => 'berpikir_konseptual', 'name' => 'Berpikir Konseptual', 'description' => 'Kemampuan berpikir secara konseptual dan strategis', 'standard_rating' => 4],
                ['code' => 'kemampuan_logika', 'name' => 'Kemampuan Logika', 'description' => 'Kemampuan berpikir logis dan sistematis tingkat tinggi', 'standard_rating' => 4],
                ['code' => 'kreativitas', 'name' => 'Kreativitas', 'description' => 'Kemampuan berpikir kreatif dan inovatif dalam pemecahan masalah', 'standard_rating' => 4],
            ],
        ];

        // CARA KERJA (Work Style)
        $this->potensiAspectLibrary['cara_kerja'] = [
            'code' => 'cara_kerja',
            'name' => 'Cara Kerja',
            'description' => 'Menilai pendekatan dan gaya individu dalam melaksanakan tugas dan pekerjaan sehari-hari.',
            'sub_aspects' => [
                ['code' => 'sistematika_kerja', 'name' => 'Sistematika Kerja', 'description' => 'Kemampuan bekerja secara teratur dan sistematis', 'standard_rating' => 3],
                ['code' => 'ketekunan', 'name' => 'Ketekunan', 'description' => 'Ketekunan dan konsistensi dalam menyelesaikan pekerjaan', 'standard_rating' => 3],
                ['code' => 'kerjasama', 'name' => 'Kerjasama', 'description' => 'Kemampuan bekerja sama dengan orang lain', 'standard_rating' => 4],
                ['code' => 'kedisiplinan', 'name' => 'Kedisiplinan', 'description' => 'Ketaatan terhadap aturan dan prosedur kerja', 'standard_rating' => 4],
                ['code' => 'tanggung_jawab', 'name' => 'Tanggung Jawab', 'description' => 'Rasa tanggung jawab terhadap tugas dan kewajiban', 'standard_rating' => 4],
            ],
        ];

        // POTENSI KERJA (Work Potential)
        $this->potensiAspectLibrary['potensi_kerja'] = [
            'code' => 'potensi_kerja',
            'name' => 'Potensi Kerja',
            'description' => 'Mengukur dorongan internal dan motivasi individu dalam mengembangkan diri dan mencapai prestasi kerja.',
            'sub_aspects' => [
                ['code' => 'motivasi_berprestasi', 'name' => 'Motivasi Berprestasi', 'description' => 'Dorongan untuk mencapai prestasi dan keunggulan', 'standard_rating' => 3],
                ['code' => 'inisiatif', 'name' => 'Inisiatif', 'description' => 'Kemampuan mengambil prakarsa dan bertindak proaktif', 'standard_rating' => 3],
                ['code' => 'semangat_kerja', 'name' => 'Semangat Kerja', 'description' => 'Antusiasme dan energi dalam melaksanakan tugas', 'standard_rating' => 3],
                ['code' => 'kestabilan_kerja', 'name' => 'Kestabilan Kerja', 'description' => 'Konsistensi dan stabilitas dalam performa kerja', 'standard_rating' => 3],
            ],
        ];

        // HUBUNGAN SOSIAL (Social Relations)
        $this->potensiAspectLibrary['hubungan_sosial'] = [
            'code' => 'hubungan_sosial',
            'name' => 'Hubungan Sosial',
            'description' => 'Mengukur kemampuan berinteraksi, berkomunikasi, dan menjalin hubungan interpersonal yang efektif.',
            'sub_aspects' => [
                ['code' => 'kepekaan_interpersonal', 'name' => 'Kepekaan Interpersonal', 'description' => 'Kepekaan terhadap perasaan dan kebutuhan orang lain', 'standard_rating' => 4],
                ['code' => 'komunikasi', 'name' => 'Komunikasi', 'description' => 'Kemampuan menyampaikan dan menerima informasi secara efektif', 'standard_rating' => 4],
                ['code' => 'kontak_sosial', 'name' => 'Kontak Sosial', 'description' => 'Kemampuan menjalin dan mempertahankan hubungan sosial', 'standard_rating' => 3],
                ['code' => 'penyesuaian_diri', 'name' => 'Penyesuaian Diri', 'description' => 'Kemampuan beradaptasi dengan lingkungan sosial yang berbeda', 'standard_rating' => 4],
            ],
        ];

        // KEPRIBADIAN - Basic (4 sub-aspects, rating 3)
        $this->potensiAspectLibrary['kepribadian_basic'] = [
            'code' => 'kepribadian',
            'name' => 'Kepribadian',
            'description' => 'Menilai karakteristik pribadi yang mencakup kepercayaan diri, stabilitas emosi, dan daya tahan terhadap tekanan.',
            'sub_aspects' => [
                ['code' => 'kepercayaan_diri', 'name' => 'Kepercayaan Diri', 'description' => 'Keyakinan terhadap kemampuan diri sendiri', 'standard_rating' => 3],
                ['code' => 'stabilitas_emosi', 'name' => 'Stabilitas Emosi', 'description' => 'Kestabilan emosi dalam berbagai situasi', 'standard_rating' => 3],
                ['code' => 'daya_tahan_stress', 'name' => 'Daya Tahan Stress', 'description' => 'Kemampuan mengelola tekanan dan stress', 'standard_rating' => 3],
                ['code' => 'kepemimpinan', 'name' => 'Kepemimpinan', 'description' => 'Kemampuan dasar dalam memimpin dan mempengaruhi orang lain', 'standard_rating' => 3],
            ],
        ];

        // KEPRIBADIAN - Leadership (4 sub-aspects, rating 4 - for supervisory roles)
        $this->potensiAspectLibrary['kepribadian_leadership'] = [
            'code' => 'kepribadian',
            'name' => 'Kepribadian',
            'description' => 'Menilai karakteristik pribadi dengan penekanan pada kepemimpinan, kematangan emosi, dan kemampuan menghadapi tantangan tingkat tinggi.',
            'sub_aspects' => [
                ['code' => 'kepercayaan_diri', 'name' => 'Kepercayaan Diri', 'description' => 'Keyakinan kuat terhadap kemampuan diri dalam situasi kompleks', 'standard_rating' => 4],
                ['code' => 'stabilitas_emosi', 'name' => 'Stabilitas Emosi', 'description' => 'Kematangan emosi dalam menghadapi berbagai tekanan', 'standard_rating' => 4],
                ['code' => 'daya_tahan_stress', 'name' => 'Daya Tahan Stress', 'description' => 'Ketahanan mental dalam menghadapi tekanan kerja tinggi', 'standard_rating' => 4],
                ['code' => 'kepemimpinan', 'name' => 'Kepemimpinan', 'description' => 'Kemampuan memimpin, memotivasi, dan menginspirasi tim', 'standard_rating' => 4],
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

        // Potensi aspects: 5 aspects - Basic level for entry positions (Total: 100%)
        $potensiAspects = [
            ['library_key' => 'kecerdasan_basic', 'weight' => 25],      // 25%
            ['library_key' => 'cara_kerja', 'weight' => 20],            // 20%
            ['library_key' => 'potensi_kerja', 'weight' => 20],         // 20%
            ['library_key' => 'hubungan_sosial', 'weight' => 20],       // 20%
            ['library_key' => 'kepribadian_basic', 'weight' => 15],     // 15%
        ];                                                               // TOTAL = 100%

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

        // Potensi aspects: 4 aspects - Advanced with leadership focus (Total: 100%)
        $potensiAspects = [
            ['library_key' => 'kecerdasan_advanced', 'weight' => 30],      // 30%
            ['library_key' => 'cara_kerja', 'weight' => 25],               // 25%
            ['library_key' => 'hubungan_sosial', 'weight' => 20],          // 20%
            ['library_key' => 'kepribadian_leadership', 'weight' => 25],   // 25%
        ];                                                                  // TOTAL = 100%

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

        // Potensi aspects: 4 aspects - Strategic thinking emphasis (Total: 100%)
        $potensiAspects = [
            ['library_key' => 'kecerdasan_advanced', 'weight' => 35],      // 35%
            ['library_key' => 'potensi_kerja', 'weight' => 20],            // 20%
            ['library_key' => 'hubungan_sosial', 'weight' => 20],          // 20%
            ['library_key' => 'kepribadian_leadership', 'weight' => 25],   // 25%
        ];                                                                  // TOTAL = 100%

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

        // Potensi aspects: 5 aspects - Professional with technical focus (Total: 100%)
        $potensiAspects = [
            ['library_key' => 'kecerdasan_advanced', 'weight' => 30],   // 30%
            ['library_key' => 'cara_kerja', 'weight' => 25],            // 25%
            ['library_key' => 'potensi_kerja', 'weight' => 15],         // 15%
            ['library_key' => 'hubungan_sosial', 'weight' => 20],       // 20%
            ['library_key' => 'kepribadian_basic', 'weight' => 10],     // 10%
        ];                                                               // TOTAL = 100%

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

        // Potensi aspects: 5 aspects - Balanced government standard (Total: 100%)
        $potensiAspects = [
            ['library_key' => 'kecerdasan_basic', 'weight' => 20],      // 20%
            ['library_key' => 'cara_kerja', 'weight' => 25],            // 25%
            ['library_key' => 'potensi_kerja', 'weight' => 20],         // 20%
            ['library_key' => 'hubungan_sosial', 'weight' => 20],       // 20%
            ['library_key' => 'kepribadian_basic', 'weight' => 15],     // 15%
        ];                                                               // TOTAL = 100%

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

            if (! $libraryAspect) {
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
