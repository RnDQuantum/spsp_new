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
        // Get template SPSP2024
        $template = AssessmentTemplate::where('code', 'SPSP2024')->first();

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
            'category_type_id' => $categoryPotensi->id,
            'code' => 'kecerdasan',
            'name' => 'Kecerdasan',
            'order' => 1,
        ]);

        $kecerdasanSubAspects = [
            ['code' => 'kecerdasan_umum', 'name' => 'Kecerdasan Umum', 'description' => 'Kemampuan intelektual secara umum', 'order' => 1],
            ['code' => 'daya_tangkap', 'name' => 'Daya Tangkap', 'description' => 'Kemampuan memahami informasi dengan cepat', 'order' => 2],
            ['code' => 'kemampuan_analisa', 'name' => 'Kemampuan Analisa', 'description' => 'Kemampuan menganalisa masalah', 'order' => 3],
            ['code' => 'berpikir_konseptual', 'name' => 'Berpikir Konseptual', 'description' => 'Kemampuan berpikir secara konseptual', 'order' => 4],
            ['code' => 'logika_berpikir', 'name' => 'Logika Berpikir', 'description' => 'Kemampuan berpikir logis dan sistematis', 'order' => 5],
            ['code' => 'kemampuan_numerik', 'name' => 'Kemampuan Numerik', 'description' => 'Kemampuan dalam hal angka dan perhitungan', 'order' => 6],
        ];

        foreach ($kecerdasanSubAspects as $subAspect) {
            SubAspect::create([
                'aspect_id' => $aspectKecerdasan->id,
                'code' => $subAspect['code'],
                'name' => $subAspect['name'],
                'description' => $subAspect['description'],
                'order' => $subAspect['order'],
            ]);
        }

        // --- Aspect: SIKAP KERJA (20%) ---
        $aspectSikapKerja = Aspect::create([
            'category_type_id' => $categoryPotensi->id,
            'code' => 'sikap_kerja',
            'name' => 'Sikap Kerja',
            'order' => 2,
        ]);

        $sikapKerjaSubAspects = [
            ['code' => 'sistematika_kerja', 'name' => 'Sistematika Kerja', 'description' => 'Kemampuan bekerja secara sistematis', 'order' => 1],
            ['code' => 'perhatian_terhadap_detail', 'name' => 'Perhatian Terhadap Detail', 'description' => 'Ketelitian dalam bekerja', 'order' => 2],
            ['code' => 'ketekunan_kerja', 'name' => 'Ketekunan Kerja', 'description' => 'Konsistensi dan ketekunan dalam bekerja', 'order' => 3],
            ['code' => 'kerjasama', 'name' => 'Kerjasama', 'description' => 'Kemampuan bekerja dalam tim', 'order' => 4],
            ['code' => 'tanggung_jawab', 'name' => 'Tanggung Jawab', 'description' => 'Rasa tanggung jawab terhadap tugas', 'order' => 5],
            ['code' => 'dorongan_berprestasi', 'name' => 'Dorongan Berprestasi', 'description' => 'Motivasi untuk berprestasi', 'order' => 6],
            ['code' => 'inisiatif', 'name' => 'Inisiatif', 'description' => 'Kemampuan mengambil inisiatif', 'order' => 7],
        ];

        foreach ($sikapKerjaSubAspects as $subAspect) {
            SubAspect::create([
                'aspect_id' => $aspectSikapKerja->id,
                'code' => $subAspect['code'],
                'name' => $subAspect['name'],
                'description' => $subAspect['description'],
                'order' => $subAspect['order'],
            ]);
        }

        // --- Aspect: HUBUNGAN SOSIAL (20%) ---
        $aspectHubunganSosial = Aspect::create([
            'category_type_id' => $categoryPotensi->id,
            'code' => 'hubungan_sosial',
            'name' => 'Hubungan Sosial',
            'order' => 3,
        ]);

        $hubunganSosialSubAspects = [
            ['code' => 'kepekaan_interpersonal', 'name' => 'Kepekaan Interpersonal', 'description' => 'Kepekaan terhadap hubungan antar pribadi', 'order' => 1],
            ['code' => 'komunikasi', 'name' => 'Komunikasi', 'description' => 'Kemampuan berkomunikasi efektif', 'order' => 2],
            ['code' => 'hubungan_interpersonal', 'name' => 'Hubungan Interpersonal', 'description' => 'Kemampuan menjalin hubungan dengan orang lain', 'order' => 3],
            ['code' => 'penyesuaian_diri', 'name' => 'Penyesuaian Diri', 'description' => 'Kemampuan menyesuaikan diri dengan lingkungan', 'order' => 4],
        ];

        foreach ($hubunganSosialSubAspects as $subAspect) {
            SubAspect::create([
                'aspect_id' => $aspectHubunganSosial->id,
                'code' => $subAspect['code'],
                'name' => $subAspect['name'],
                'description' => $subAspect['description'],
                'order' => $subAspect['order'],
            ]);
        }

        // --- Aspect: KEPRIBADIAN (30%) ---
        $aspectKepribadian = Aspect::create([
            'category_type_id' => $categoryPotensi->id,
            'code' => 'kepribadian',
            'name' => 'Kepribadian',
            'order' => 4,
        ]);

        $kepribadianSubAspects = [
            ['code' => 'stabilitas_kematangan_emosi', 'name' => 'Stabilitas/Kematangan Emosi', 'description' => 'Kemampuan mengelola emosi', 'order' => 1],
            ['code' => 'agility', 'name' => 'Agility', 'description' => 'Kelincahan dalam beradaptasi', 'order' => 2],
            ['code' => 'kepercayaan_diri', 'name' => 'Kepercayaan Diri', 'description' => 'Tingkat kepercayaan diri', 'order' => 3],
            ['code' => 'daya_tahan_stress', 'name' => 'Daya Tahan Stress', 'description' => 'Kemampuan mengelola tekanan', 'order' => 4],
            ['code' => 'kepemimpinan', 'name' => 'Kepemimpinan', 'description' => 'Kemampuan memimpin', 'order' => 5],
            ['code' => 'loyalitas', 'name' => 'Loyalitas', 'description' => 'Kesetiaan terhadap organisasi', 'order' => 6],
        ];

        foreach ($kepribadianSubAspects as $subAspect) {
            SubAspect::create([
                'aspect_id' => $aspectKepribadian->id,
                'code' => $subAspect['code'],
                'name' => $subAspect['name'],
                'description' => $subAspect['description'],
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
        $kompetensiAspects = [
            ['code' => 'integritas', 'name' => 'Integritas', 'order' => 1],
            ['code' => 'kerjasama', 'name' => 'Kerjasama', 'order' => 2],
            ['code' => 'komunikasi', 'name' => 'Komunikasi', 'order' => 3],
            ['code' => 'orientasi_pada_hasil', 'name' => 'Orientasi Pada Hasil', 'order' => 4],
            ['code' => 'pelayanan_publik', 'name' => 'Pelayanan Publik', 'order' => 5],
            ['code' => 'pengembangan_diri_orang_lain', 'name' => 'Pengembangan Diri & Orang Lain', 'order' => 6],
            ['code' => 'mengelola_perubahan', 'name' => 'Mengelola Perubahan', 'order' => 7],
            ['code' => 'pengambilan_keputusan', 'name' => 'Pengambilan Keputusan', 'order' => 8],
            ['code' => 'perekat_bangsa', 'name' => 'Perekat Bangsa', 'order' => 9],
        ];

        foreach ($kompetensiAspects as $aspect) {
            Aspect::create([
                'category_type_id' => $categoryKompetensi->id,
                'code' => $aspect['code'],
                'name' => $aspect['name'],
                'order' => $aspect['order'],
            ]);
        }
    }
}
