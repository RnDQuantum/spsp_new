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
     */
    private function initializePotensiLibrary(): void
    {
        // KECERDASAN - Basic intelligence aspects
        $this->potensiAspectLibrary['kecerdasan_basic'] = [
            'code' => 'kecerdasan',
            'name' => 'Kecerdasan',
            'description' => 'Mengukur kemampuan kognitif dan intelektual dalam memahami, menganalisa, dan memecahkan masalah secara logis dan sistematis.',
            'sub_aspects' => [
                ['code' => 'kecerdasan_umum', 'name' => 'Kecerdasan Umum', 'description' => 'Kemampuan intelektual secara umum', 'standard_rating' => 3],
                ['code' => 'daya_tangkap', 'name' => 'Daya Tangkap', 'description' => 'Kemampuan memahami informasi dengan cepat', 'standard_rating' => 3],
                ['code' => 'kemampuan_analisa', 'name' => 'Kemampuan Analisa', 'description' => 'Kemampuan menganalisa masalah', 'standard_rating' => 3],
                ['code' => 'logika_berpikir', 'name' => 'Logika Berpikir', 'description' => 'Kemampuan berpikir logis dan sistematis', 'standard_rating' => 3],
            ],
        ];

        // KECERDASAN - Advanced (for higher positions)
        $this->potensiAspectLibrary['kecerdasan_advanced'] = [
            'code' => 'kecerdasan',
            'name' => 'Kecerdasan',
            'description' => 'Mengukur kemampuan kognitif tingkat tinggi untuk analisis kompleks, pemikiran strategis, dan pengambilan keputusan berbasis data.',
            'sub_aspects' => [
                ['code' => 'kecerdasan_umum', 'name' => 'Kecerdasan Umum', 'description' => 'Kemampuan intelektual secara umum', 'standard_rating' => 4],
                ['code' => 'daya_tangkap', 'name' => 'Daya Tangkap', 'description' => 'Kemampuan memahami informasi dengan cepat', 'standard_rating' => 4],
                ['code' => 'kemampuan_analisa', 'name' => 'Kemampuan Analisa', 'description' => 'Kemampuan menganalisa masalah kompleks', 'standard_rating' => 4],
                ['code' => 'berpikir_konseptual', 'name' => 'Berpikir Konseptual', 'description' => 'Kemampuan berpikir secara konseptual dan strategis', 'standard_rating' => 4],
                ['code' => 'logika_berpikir', 'name' => 'Logika Berpikir', 'description' => 'Kemampuan berpikir logis dan sistematis', 'standard_rating' => 4],
                ['code' => 'kemampuan_numerik', 'name' => 'Kemampuan Numerik', 'description' => 'Kemampuan dalam hal angka dan perhitungan', 'standard_rating' => 4],
            ],
        ];

        // SIKAP KERJA
        $this->potensiAspectLibrary['sikap_kerja'] = [
            'code' => 'sikap_kerja',
            'name' => 'Sikap Kerja',
            'description' => 'Menilai perilaku dan etos kerja yang mencakup sistematika, ketelitian, ketekunan, kerjasama, tanggung jawab, dan dorongan berprestasi.',
            'sub_aspects' => [
                ['code' => 'sistematika_kerja', 'name' => 'Sistematika Kerja', 'description' => 'Kemampuan bekerja secara sistematis', 'standard_rating' => 3],
                ['code' => 'perhatian_terhadap_detail', 'name' => 'Perhatian Terhadap Detail', 'description' => 'Ketelitian dalam bekerja', 'standard_rating' => 3],
                ['code' => 'ketekunan_kerja', 'name' => 'Ketekunan Kerja', 'description' => 'Konsistensi dan ketekunan dalam bekerja', 'standard_rating' => 3],
                ['code' => 'kerjasama', 'name' => 'Kerjasama', 'description' => 'Kemampuan bekerja dalam tim', 'standard_rating' => 4],
                ['code' => 'tanggung_jawab', 'name' => 'Tanggung Jawab', 'description' => 'Rasa tanggung jawab terhadap tugas', 'standard_rating' => 4],
                ['code' => 'dorongan_berprestasi', 'name' => 'Dorongan Berprestasi', 'description' => 'Motivasi untuk berprestasi', 'standard_rating' => 3],
                ['code' => 'inisiatif', 'name' => 'Inisiatif', 'description' => 'Kemampuan mengambil inisiatif', 'standard_rating' => 3],
            ],
        ];

        // HUBUNGAN SOSIAL
        $this->potensiAspectLibrary['hubungan_sosial'] = [
            'code' => 'hubungan_sosial',
            'name' => 'Hubungan Sosial',
            'description' => 'Mengukur kemampuan berinteraksi, berkomunikasi, dan menjalin hubungan interpersonal yang efektif dengan berbagai pihak.',
            'sub_aspects' => [
                ['code' => 'kepekaan_interpersonal', 'name' => 'Kepekaan Interpersonal', 'description' => 'Kepekaan terhadap hubungan antar pribadi', 'standard_rating' => 4],
                ['code' => 'komunikasi', 'name' => 'Komunikasi', 'description' => 'Kemampuan berkomunikasi efektif', 'standard_rating' => 4],
                ['code' => 'hubungan_interpersonal', 'name' => 'Hubungan Interpersonal', 'description' => 'Kemampuan menjalin hubungan dengan orang lain', 'standard_rating' => 3],
                ['code' => 'penyesuaian_diri', 'name' => 'Penyesuaian Diri', 'description' => 'Kemampuan menyesuaikan diri dengan lingkungan', 'standard_rating' => 4],
            ],
        ];

        // KEPRIBADIAN - Basic
        $this->potensiAspectLibrary['kepribadian_basic'] = [
            'code' => 'kepribadian',
            'name' => 'Kepribadian',
            'description' => 'Menilai karakteristik pribadi yang mencakup stabilitas emosi, kepercayaan diri, dan daya tahan terhadap stress.',
            'sub_aspects' => [
                ['code' => 'stabilitas_kematangan_emosi', 'name' => 'Stabilitas/Kematangan Emosi', 'description' => 'Kemampuan mengelola emosi', 'standard_rating' => 3],
                ['code' => 'agility', 'name' => 'Agility', 'description' => 'Kelincahan dalam beradaptasi', 'standard_rating' => 3],
                ['code' => 'kepercayaan_diri', 'name' => 'Kepercayaan Diri', 'description' => 'Tingkat kepercayaan diri', 'standard_rating' => 3],
                ['code' => 'daya_tahan_stress', 'name' => 'Daya Tahan Stress', 'description' => 'Kemampuan mengelola tekanan', 'standard_rating' => 3],
            ],
        ];

        // KEPRIBADIAN - Leadership (for supervisory roles)
        $this->potensiAspectLibrary['kepribadian_leadership'] = [
            'code' => 'kepribadian',
            'name' => 'Kepribadian',
            'description' => 'Menilai karakteristik pribadi dengan penekanan pada kepemimpinan, stabilitas emosi, kepercayaan diri, dan daya tahan terhadap stress.',
            'sub_aspects' => [
                ['code' => 'stabilitas_kematangan_emosi', 'name' => 'Stabilitas/Kematangan Emosi', 'description' => 'Kemampuan mengelola emosi', 'standard_rating' => 4],
                ['code' => 'agility', 'name' => 'Agility', 'description' => 'Kelincahan dalam beradaptasi', 'standard_rating' => 4],
                ['code' => 'kepercayaan_diri', 'name' => 'Kepercayaan Diri', 'description' => 'Tingkat kepercayaan diri', 'standard_rating' => 4],
                ['code' => 'daya_tahan_stress', 'name' => 'Daya Tahan Stress', 'description' => 'Kemampuan mengelola tekanan tinggi', 'standard_rating' => 4],
                ['code' => 'kepemimpinan', 'name' => 'Kepemimpinan', 'description' => 'Kemampuan memimpin dan memotivasi tim', 'standard_rating' => 4],
                ['code' => 'loyalitas', 'name' => 'Loyalitas', 'description' => 'Kesetiaan terhadap organisasi', 'standard_rating' => 4],
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

        // Potensi aspects: Basic level, balanced weights
        $potensiAspects = [
            ['library_key' => 'kecerdasan_basic', 'weight' => 30],
            ['library_key' => 'sikap_kerja', 'weight' => 40],
            ['library_key' => 'hubungan_sosial', 'weight' => 30],
        ];

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

        // Potensi aspects: Advanced intelligence + leadership personality
        $potensiAspects = [
            ['library_key' => 'kecerdasan_advanced', 'weight' => 40],
            ['library_key' => 'hubungan_sosial', 'weight' => 25],
            ['library_key' => 'kepribadian_leadership', 'weight' => 35],
        ];

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

        // Potensi aspects: Advanced intelligence + leadership
        $potensiAspects = [
            ['library_key' => 'kecerdasan_advanced', 'weight' => 50],
            ['library_key' => 'kepribadian_leadership', 'weight' => 50],
        ];

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

        // Potensi aspects: Advanced intelligence, basic personality, social skills
        $potensiAspects = [
            ['library_key' => 'kecerdasan_advanced', 'weight' => 40],
            ['library_key' => 'sikap_kerja', 'weight' => 30],
            ['library_key' => 'hubungan_sosial', 'weight' => 30],
        ];

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

        // Potensi aspects: Balanced approach
        $potensiAspects = [
            ['library_key' => 'kecerdasan_basic', 'weight' => 30],
            ['library_key' => 'sikap_kerja', 'weight' => 20],
            ['library_key' => 'hubungan_sosial', 'weight' => 20],
            ['library_key' => 'kepribadian_basic', 'weight' => 30],
        ];

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
