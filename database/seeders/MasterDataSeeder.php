<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\{AssessmentTemplate, CategoryType, Aspect, SubAspect};

class MasterDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get template P3K Standard 2025
        $template = AssessmentTemplate::where('code', 'p3k_standard_2025')->first();

        // ==========================================
        // CATEGORY: POTENSI (40%)
        // ==========================================
        $categoryPotensi = CategoryType::create([
            'template_id' => $template->id,
            'code' => 'potensi',
            'name' => 'Potensi',
            'weight_percentage' => 40,
            'order' => 1,
        ]);

        // --- Aspect: KECERDASAN (30%) ---
        $aspectKecerdasan = Aspect::create([
            'template_id' => $template->id,
            'category_type_id' => $categoryPotensi->id,
            'code' => 'kecerdasan',
            'name' => 'Kecerdasan',
            'description' => 'Mengukur kemampuan kognitif dan intelektual dalam memahami, menganalisa, dan memecahkan masalah secara logis dan sistematis.',
            'weight_percentage' => 30,
            'standard_rating' => 3.50,
            'order' => 1,
        ]);

        $kecerdasanSubAspects = [
            ['code' => 'kecerdasan_umum', 'name' => 'Kecerdasan Umum', 'description' => 'Kemampuan intelektual secara umum', 'standard_rating' => 4, 'order' => 1],
            ['code' => 'daya_tangkap', 'name' => 'Daya Tangkap', 'description' => 'Kemampuan memahami informasi dengan cepat', 'standard_rating' => 3, 'order' => 2],
            ['code' => 'kemampuan_analisa', 'name' => 'Kemampuan Analisa', 'description' => 'Kemampuan menganalisa masalah', 'standard_rating' => 4, 'order' => 3],
            ['code' => 'berpikir_konseptual', 'name' => 'Berpikir Konseptual', 'description' => 'Kemampuan berpikir secara konseptual', 'standard_rating' => 3, 'order' => 4],
            ['code' => 'logika_berpikir', 'name' => 'Logika Berpikir', 'description' => 'Kemampuan berpikir logis dan sistematis', 'standard_rating' => 4, 'order' => 5],
            ['code' => 'kemampuan_numerik', 'name' => 'Kemampuan Numerik', 'description' => 'Kemampuan dalam hal angka dan perhitungan', 'standard_rating' => 3, 'order' => 6],
        ];

        foreach ($kecerdasanSubAspects as $subAspect) {
            SubAspect::create([
                'aspect_id' => $aspectKecerdasan->id,
                'code' => $subAspect['code'],
                'name' => $subAspect['name'],
                'description' => $subAspect['description'],
                'standard_rating' => $subAspect['standard_rating'],
                'order' => $subAspect['order'],
            ]);
        }

        // --- Aspect: SIKAP KERJA (20%) ---
        $aspectSikapKerja = Aspect::create([
            'template_id' => $template->id,
            'category_type_id' => $categoryPotensi->id,
            'code' => 'sikap_kerja',
            'name' => 'Sikap Kerja',
            'description' => 'Menilai perilaku dan etos kerja yang mencakup sistematika, ketelitian, ketekunan, kerjasama, tanggung jawab, dan dorongan berprestasi.',
            'weight_percentage' => 20,
            'standard_rating' => 3.20,
            'order' => 2,
        ]);

        $sikapKerjaSubAspects = [
            ['code' => 'sistematika_kerja', 'name' => 'Sistematika Kerja', 'description' => 'Kemampuan bekerja secara sistematis', 'standard_rating' => 3, 'order' => 1],
            ['code' => 'perhatian_terhadap_detail', 'name' => 'Perhatian Terhadap Detail', 'description' => 'Ketelitian dalam bekerja', 'standard_rating' => 4, 'order' => 2],
            ['code' => 'ketekunan_kerja', 'name' => 'Ketekunan Kerja', 'description' => 'Konsistensi dan ketekunan dalam bekerja', 'standard_rating' => 3, 'order' => 3],
            ['code' => 'kerjasama', 'name' => 'Kerjasama', 'description' => 'Kemampuan bekerja dalam tim', 'standard_rating' => 3, 'order' => 4],
            ['code' => 'tanggung_jawab', 'name' => 'Tanggung Jawab', 'description' => 'Rasa tanggung jawab terhadap tugas', 'standard_rating' => 4, 'order' => 5],
            ['code' => 'dorongan_berprestasi', 'name' => 'Dorongan Berprestasi', 'description' => 'Motivasi untuk berprestasi', 'standard_rating' => 3, 'order' => 6],
            ['code' => 'inisiatif', 'name' => 'Inisiatif', 'description' => 'Kemampuan mengambil inisiatif', 'standard_rating' => 3, 'order' => 7],
        ];

        foreach ($sikapKerjaSubAspects as $subAspect) {
            SubAspect::create([
                'aspect_id' => $aspectSikapKerja->id,
                'code' => $subAspect['code'],
                'name' => $subAspect['name'],
                'description' => $subAspect['description'],
                'standard_rating' => $subAspect['standard_rating'],
                'order' => $subAspect['order'],
            ]);
        }

        // --- Aspect: HUBUNGAN SOSIAL (20%) ---
        $aspectHubunganSosial = Aspect::create([
            'template_id' => $template->id,
            'category_type_id' => $categoryPotensi->id,
            'code' => 'hubungan_sosial',
            'name' => 'Hubungan Sosial',
            'description' => 'Mengukur kemampuan berinteraksi, berkomunikasi, dan menjalin hubungan interpersonal yang efektif dengan berbagai pihak.',
            'weight_percentage' => 20,
            'standard_rating' => 3.75,
            'order' => 3,
        ]);

        $hubunganSosialSubAspects = [
            ['code' => 'kepekaan_interpersonal', 'name' => 'Kepekaan Interpersonal', 'description' => 'Kepekaan terhadap hubungan antar pribadi', 'standard_rating' => 4, 'order' => 1],
            ['code' => 'komunikasi', 'name' => 'Komunikasi', 'description' => 'Kemampuan berkomunikasi efektif', 'standard_rating' => 4, 'order' => 2],
            ['code' => 'hubungan_interpersonal', 'name' => 'Hubungan Interpersonal', 'description' => 'Kemampuan menjalin hubungan dengan orang lain', 'standard_rating' => 3, 'order' => 3],
            ['code' => 'penyesuaian_diri', 'name' => 'Penyesuaian Diri', 'description' => 'Kemampuan menyesuaikan diri dengan lingkungan', 'standard_rating' => 4, 'order' => 4],
        ];

        foreach ($hubunganSosialSubAspects as $subAspect) {
            SubAspect::create([
                'aspect_id' => $aspectHubunganSosial->id,
                'code' => $subAspect['code'],
                'name' => $subAspect['name'],
                'description' => $subAspect['description'],
                'standard_rating' => $subAspect['standard_rating'],
                'order' => $subAspect['order'],
            ]);
        }

        // --- Aspect: KEPRIBADIAN (30%) ---
        $aspectKepribadian = Aspect::create([
            'template_id' => $template->id,
            'category_type_id' => $categoryPotensi->id,
            'code' => 'kepribadian',
            'name' => 'Kepribadian',
            'description' => 'Menilai karakteristik pribadi yang mencakup stabilitas emosi, kepercayaan diri, daya tahan terhadap stress, dan kemampuan kepemimpinan.',
            'weight_percentage' => 30,
            'standard_rating' => 3.67,
            'order' => 4,
        ]);

        $kepribadianSubAspects = [
            ['code' => 'stabilitas_kematangan_emosi', 'name' => 'Stabilitas/Kematangan Emosi', 'description' => 'Kemampuan mengelola emosi', 'standard_rating' => 4, 'order' => 1],
            ['code' => 'agility', 'name' => 'Agility', 'description' => 'Kelincahan dalam beradaptasi', 'standard_rating' => 3, 'order' => 2],
            ['code' => 'kepercayaan_diri', 'name' => 'Kepercayaan Diri', 'description' => 'Tingkat kepercayaan diri', 'standard_rating' => 4, 'order' => 3],
            ['code' => 'daya_tahan_stress', 'name' => 'Daya Tahan Stress', 'description' => 'Kemampuan mengelola tekanan', 'standard_rating' => 4, 'order' => 4],
            ['code' => 'kepemimpinan', 'name' => 'Kepemimpinan', 'description' => 'Kemampuan memimpin', 'standard_rating' => 3, 'order' => 5],
            ['code' => 'loyalitas', 'name' => 'Loyalitas', 'description' => 'Kesetiaan terhadap organisasi', 'standard_rating' => 4, 'order' => 6],
        ];

        foreach ($kepribadianSubAspects as $subAspect) {
            SubAspect::create([
                'aspect_id' => $aspectKepribadian->id,
                'code' => $subAspect['code'],
                'name' => $subAspect['name'],
                'description' => $subAspect['description'],
                'standard_rating' => $subAspect['standard_rating'],
                'order' => $subAspect['order'],
            ]);
        }

        // ==========================================
        // CATEGORY: KOMPETENSI (60%)
        // ==========================================
        $categoryKompetensi = CategoryType::create([
            'template_id' => $template->id,
            'code' => 'kompetensi',
            'name' => 'Kompetensi',
            'weight_percentage' => 60,
            'order' => 2,
        ]);

        // Kompetensi aspects (tidak ada sub-aspects)
        // Total weight harus 100%, 9 aspek masing-masing ~11%
        $kompetensiAspects = [
            ['code' => 'integritas', 'name' => 'Integritas', 'description' => 'Kemampuan bertindak konsisten sesuai nilai, norma, dan etika organisasi dengan jujur dan bertanggung jawab.', 'weight' => 12, 'standard_rating' => 3.50, 'order' => 1],
            ['code' => 'kerjasama', 'name' => 'Kerjasama', 'description' => 'Kemampuan bekerja bersama secara sinergis dalam mencapai tujuan organisasi dengan menghargai kontribusi setiap pihak.', 'weight' => 11, 'standard_rating' => 3.25, 'order' => 2],
            ['code' => 'komunikasi', 'name' => 'Komunikasi', 'description' => 'Kemampuan menyampaikan dan menerima informasi secara efektif, jelas, dan tepat sasaran baik lisan maupun tertulis.', 'weight' => 11, 'standard_rating' => 3.75, 'order' => 3],
            ['code' => 'orientasi_pada_hasil', 'name' => 'Orientasi Pada Hasil', 'description' => 'Komitmen untuk mencapai hasil berkualitas tinggi dengan efektif dan efisien sesuai target yang ditetapkan.', 'weight' => 11, 'standard_rating' => 3.50, 'order' => 4],
            ['code' => 'pelayanan_publik', 'name' => 'Pelayanan Publik', 'description' => 'Kemampuan memberikan pelayanan prima kepada masyarakat dengan responsif, ramah, dan berorientasi pada kepuasan publik.', 'weight' => 11, 'standard_rating' => 3.60, 'order' => 5],
            ['code' => 'pengembangan_diri_orang_lain', 'name' => 'Pengembangan Diri & Orang Lain', 'description' => 'Komitmen untuk terus belajar dan mengembangkan kompetensi diri serta membantu pengembangan orang lain.', 'weight' => 11, 'standard_rating' => 3.40, 'order' => 6],
            ['code' => 'mengelola_perubahan', 'name' => 'Mengelola Perubahan', 'description' => 'Kemampuan beradaptasi dan memimpin perubahan dengan proaktif untuk meningkatkan efektivitas organisasi.', 'weight' => 11, 'standard_rating' => 3.30, 'order' => 7],
            ['code' => 'pengambilan_keputusan', 'name' => 'Pengambilan Keputusan', 'description' => 'Kemampuan menganalisis informasi dan mengambil keputusan yang tepat, cepat, dan akurat berdasarkan data dan pertimbangan matang.', 'weight' => 11, 'standard_rating' => 3.45, 'order' => 8],
            ['code' => 'perekat_bangsa', 'name' => 'Perekat Bangsa', 'description' => 'Kemampuan menjaga persatuan dengan menghargai keberagaman dan mempromosikan toleransi dalam kebhinekaan.', 'weight' => 11, 'standard_rating' => 3.55, 'order' => 9],
        ]; // Total = 100%

        foreach ($kompetensiAspects as $aspect) {
            Aspect::create([
                'template_id' => $template->id,
                'category_type_id' => $categoryKompetensi->id,
                'code' => $aspect['code'],
                'name' => $aspect['name'],
                'description' => $aspect['description'],
                'weight_percentage' => $aspect['weight'],
                'standard_rating' => $aspect['standard_rating'],
                'order' => $aspect['order'],
            ]);
        }
    }
}
