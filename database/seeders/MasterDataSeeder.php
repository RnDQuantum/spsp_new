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
        $kecerdasanSubAspects = [
            ['code' => 'kecerdasan_umum', 'name' => 'Kecerdasan Umum', 'description' => 'Kemampuan intelektual secara umum', 'standard_rating' => 3, 'order' => 1],
            ['code' => 'daya_tangkap', 'name' => 'Daya Tangkap', 'description' => 'Kemampuan memahami informasi dengan cepat', 'standard_rating' => 3, 'order' => 2],
            ['code' => 'kemampuan_analisa', 'name' => 'Kemampuan Analisa', 'description' => 'Kemampuan menganalisa masalah', 'standard_rating' => 4, 'order' => 3],
            ['code' => 'berpikir_konseptual', 'name' => 'Berpikir Konseptual', 'description' => 'Kemampuan berpikir secara konseptual', 'standard_rating' => 4, 'order' => 4],
            ['code' => 'logika_berpikir', 'name' => 'Logika Berpikir', 'description' => 'Kemampuan berpikir logis dan sistematis', 'standard_rating' => 4, 'order' => 5],
            ['code' => 'kemampuan_numerik', 'name' => 'Kemampuan Numerik', 'description' => 'Kemampuan dalam hal angka dan perhitungan', 'standard_rating' => 3, 'order' => 6],
        ];

        // Calculate standard_rating as AVERAGE of sub-aspects (POTENSI pattern)
        $kecerdasanStandardRating = collect($kecerdasanSubAspects)->avg('standard_rating');

        $aspectKecerdasan = Aspect::create([
            'template_id' => $template->id,
            'category_type_id' => $categoryPotensi->id,
            'code' => 'kecerdasan',
            'name' => 'Kecerdasan',
            'description' => 'Mengukur kemampuan kognitif dan intelektual dalam memahami, menganalisa, dan memecahkan masalah secara logis dan sistematis.',
            'weight_percentage' => 30,
            'standard_rating' => round($kecerdasanStandardRating, 2),
            'order' => 1,
        ]);

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
        $sikapKerjaSubAspects = [
            ['code' => 'sistematika_kerja', 'name' => 'Sistematika Kerja', 'description' => 'Kemampuan bekerja secara sistematis', 'standard_rating' => 3, 'order' => 1],
            ['code' => 'perhatian_terhadap_detail', 'name' => 'Perhatian Terhadap Detail', 'description' => 'Ketelitian dalam bekerja', 'standard_rating' => 3, 'order' => 2],
            ['code' => 'ketekunan_kerja', 'name' => 'Ketekunan Kerja', 'description' => 'Konsistensi dan ketekunan dalam bekerja', 'standard_rating' => 3, 'order' => 3],
            ['code' => 'kerjasama', 'name' => 'Kerjasama', 'description' => 'Kemampuan bekerja dalam tim', 'standard_rating' => 4, 'order' => 4],
            ['code' => 'tanggung_jawab', 'name' => 'Tanggung Jawab', 'description' => 'Rasa tanggung jawab terhadap tugas', 'standard_rating' => 4, 'order' => 5],
            ['code' => 'dorongan_berprestasi', 'name' => 'Dorongan Berprestasi', 'description' => 'Motivasi untuk berprestasi', 'standard_rating' => 3, 'order' => 6],
            ['code' => 'inisiatif', 'name' => 'Inisiatif', 'description' => 'Kemampuan mengambil inisiatif', 'standard_rating' => 3, 'order' => 7],
        ];

        // Calculate standard_rating as AVERAGE of sub-aspects (POTENSI pattern)
        $sikapKerjaStandardRating = collect($sikapKerjaSubAspects)->avg('standard_rating');

        $aspectSikapKerja = Aspect::create([
            'template_id' => $template->id,
            'category_type_id' => $categoryPotensi->id,
            'code' => 'sikap_kerja',
            'name' => 'Sikap Kerja',
            'description' => 'Menilai perilaku dan etos kerja yang mencakup sistematika, ketelitian, ketekunan, kerjasama, tanggung jawab, dan dorongan berprestasi.',
            'weight_percentage' => 20,
            'standard_rating' => round($sikapKerjaStandardRating, 2),
            'order' => 2,
        ]);

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
        $hubunganSosialSubAspects = [
            ['code' => 'kepekaan_interpersonal', 'name' => 'Kepekaan Interpersonal', 'description' => 'Kepekaan terhadap hubungan antar pribadi', 'standard_rating' => 4, 'order' => 1],
            ['code' => 'komunikasi', 'name' => 'Komunikasi', 'description' => 'Kemampuan berkomunikasi efektif', 'standard_rating' => 4, 'order' => 2],
            ['code' => 'hubungan_interpersonal', 'name' => 'Hubungan Interpersonal', 'description' => 'Kemampuan menjalin hubungan dengan orang lain', 'standard_rating' => 3, 'order' => 3],
            ['code' => 'penyesuaian_diri', 'name' => 'Penyesuaian Diri', 'description' => 'Kemampuan menyesuaikan diri dengan lingkungan', 'standard_rating' => 4, 'order' => 4],
        ];

        // Calculate standard_rating as AVERAGE of sub-aspects (POTENSI pattern)
        $hubunganSosialStandardRating = collect($hubunganSosialSubAspects)->avg('standard_rating');

        $aspectHubunganSosial = Aspect::create([
            'template_id' => $template->id,
            'category_type_id' => $categoryPotensi->id,
            'code' => 'hubungan_sosial',
            'name' => 'Hubungan Sosial',
            'description' => 'Mengukur kemampuan berinteraksi, berkomunikasi, dan menjalin hubungan interpersonal yang efektif dengan berbagai pihak.',
            'weight_percentage' => 20,
            'standard_rating' => round($hubunganSosialStandardRating, 2),
            'order' => 3,
        ]);

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
        $kepribadianSubAspects = [
            ['code' => 'stabilitas_kematangan_emosi', 'name' => 'Stabilitas/Kematangan Emosi', 'description' => 'Kemampuan mengelola emosi', 'standard_rating' => 4, 'order' => 1],
            ['code' => 'agility', 'name' => 'Agility', 'description' => 'Kelincahan dalam beradaptasi', 'standard_rating' => 3, 'order' => 2],
            ['code' => 'kepercayaan_diri', 'name' => 'Kepercayaan Diri', 'description' => 'Tingkat kepercayaan diri', 'standard_rating' => 4, 'order' => 3],
            ['code' => 'daya_tahan_stress', 'name' => 'Daya Tahan Stress', 'description' => 'Kemampuan mengelola tekanan', 'standard_rating' => 4, 'order' => 4],
            ['code' => 'kepemimpinan', 'name' => 'Kepemimpinan', 'description' => 'Kemampuan memimpin', 'standard_rating' => 3, 'order' => 5],
            ['code' => 'loyalitas', 'name' => 'Loyalitas', 'description' => 'Kesetiaan terhadap organisasi', 'standard_rating' => 4, 'order' => 6],
        ];

        // Calculate standard_rating as AVERAGE of sub-aspects (POTENSI pattern)
        $kepribadianStandardRating = collect($kepribadianSubAspects)->avg('standard_rating');

        $aspectKepribadian = Aspect::create([
            'template_id' => $template->id,
            'category_type_id' => $categoryPotensi->id,
            'code' => 'kepribadian',
            'name' => 'Kepribadian',
            'description' => 'Menilai karakteristik pribadi yang mencakup stabilitas emosi, kepercayaan diri, daya tahan terhadap stress, dan kemampuan kepemimpinan.',
            'weight_percentage' => 30,
            'standard_rating' => round($kepribadianStandardRating, 2),
            'order' => 4,
        ]);

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
            [
                'code' => 'integritas',
                'name' => 'Integritas',
                'description' => "Konsisten berperilaku selaras dengan nilai, norma dan/atau etika organisasi, dan jujur dalam hubungan dengan manajemen, rekan kerja, bawahan langsung, dan pemangku kepentingan, menciptakan budaya etika tinggi, bertanggung jawab atas tindakan atau keputusan beserta risiko yang menyertainya.
        Level 4
        Mampu menciptakan situasi kerja yang mendorong kepatuhan pada nilai, norma, dan etika organisasi.",
                'weight' => 12,
                'standard_rating' => 3.5,
                'order' => 1,
            ],
            [
                'code' => 'kerjasama',
                'name' => 'Kerjasama',
                'description' => "Kemampuan menjalin, membina, memertahankan hubungan kerja yang efektif, memiliki komitmen saling membantu dalam penyelesaian tugas, dan mengoptimalkan segala sumber daya untuk mencapai tujuan strategis organisasi.
        Level 4
        Membangun komitmen tim, sinergi.",
                'weight' => 11,
                'standard_rating' => 3.25,
                'order' => 2,
            ],
            [
                'code' => 'komunikasi',
                'name' => 'Komunikasi',
                'description' => "Kemampuan untuk menerangkan pandangan dan gagasan secara jelas, sistematis disertai argumentasi yang logis dengan cara-cara yang sesuai baik secara lisan maupun tertulis; memastikan pemahaman; mendengarkan secara aktif dan efektif; mempersuasi, meyakinkan dan membujuk orang lain dalam rangka mencapai tujuan organisasi.
        Level 4
        Mampu mengemukakan pemikiran multidimensi secara lisan dan tertulis untuk mendorong kesepakatan dengan tujuan meningkatkan kinerja secara keseluruhan.",
                'weight' => 11,
                'standard_rating' => 3.75,
                'order' => 3,
            ],
            [
                'code' => 'orientasi_pada_hasil',
                'name' => 'Orientasi Pada Hasil',
                'description' => "Kemampuan memertahankan komitmen pribadi yang tinggi untuk menyelesaikan tugas, dapat diandalkan, bertanggung jawab, mampu secara sistematis mengidentifikasi risiko dan peluang dengan memperhatikan keterhubungan antara perencanaan dan hasil, untuk keberhasilan organisasi.
        Level 4
        Mendorong unit kerja mencapai target yang ditetapkan atau melebihi hasil kerja sebelumnya.",
                'weight' => 11,
                'standard_rating' => 3.5,
                'order' => 4,
            ],
            [
                'code' => 'pelayanan_publik',
                'name' => 'Pelayanan Publik',
                'description' => "Kemampuan dalam melaksanakan tugas-tugas pemerintahan, pembangunan dan kegiatan pemenuhan kebutuhan pelayanan publik secara profesional, transparan, mengikuti standar pelayanan yang objektif, netral, tidak memihak, tidak diskriminatif, serta tidak terpengaruh kepentingan pribadi/kelompok/golongan/partai politik.
        Level 4
        Mampu memonitor, mengevaluasi, memperhitungkan dan mengantisipasi dampak dari isu-isu jangka panjang, kesempatan, atau kekuatan politik dalam hal pelayanan kebutuhan pemangku kepentingan yang transparan, objektif, dan profesional.",
                'weight' => 11,
                'standard_rating' => 3.6,
                'order' => 5,
            ],
            [
                'code' => 'pengembangan_diri_orang_lain',
                'name' => 'Pengembangan Diri & Orang Lain',
                'description' => "Kemampuan untuk meningkatkan pengetahuan dan menyempurnakan keterampilan diri; menginspirasi orang lain untuk mengembangkan dan menyempurnakan pengetahuan dan keterampilan yang relevan dengan pekerjaan dan pengembangan karir jangka panjang, mendorong kemauan belajar sepanjang hidup, memberikan saran/bantuan, umpan balik, bimbingan untuk membantu orang lain untuk mengembangkan potensi dirinya.
        Level 4
        Menyusun program pengembangan jangka panjang dalam rangka mendorong manajemen pembelajaran.",
                'weight' => 11,
                'standard_rating' => 3.4,
                'order' => 6,
            ],
            [
                'code' => 'mengelola_perubahan',
                'name' => 'Mengelola Perubahan',
                'description' => "Kemampuan dalam menyesuaikan diri dengan situasi yang baru atau berubah dan tidak bergantung secara berlebihan pada metode dan proses lama, mengambil tindakan untuk mendukung dan melaksanakan inisiatif perubahan, memimpin usaha perubahan, mengambil tanggung jawab pribadi untuk memastikan perubahan berhasil diimplementasikan secara efektif.
        Level 4
        Memimpin perubahan pada unit kerja.",
                'weight' => 11,
                'standard_rating' => 3.3,
                'order' => 7,
            ],
            [
                'code' => 'pengambilan_keputusan',
                'name' => 'Pengambilan Keputusan',
                'description' => "Kemampuan membuat keputusan yang baik secara tepat waktu dan dengan keyakinan diri setelah mempertimbangkan prinsip kehati-hatian, dirumuskan secara sistematis dan seksama berdasarkan berbagai informasi, alternatif pemecahan masalah dan konsekuensinya, serta bertanggung jawab atas keputusan yang diambil.
        Level 4
        Menyelesaikan masalah yang mengandung risiko tinggi, mengantisipasi dampak keputusan, membuat tindakan pengamanan; mitigasi risiko.",
                'weight' => 11,
                'standard_rating' => 3.45,
                'order' => 8,
            ],
            [
                'code' => 'perekat_bangsa',
                'name' => 'Perekat Bangsa',
                'description' => "Kemampuan dalam mempromosikan sikap toleransi, keterbukaan, peka terhadap perbedaan individu/kelompok masyarakat; mampu menjadi perpanjangan tangan pemerintah dalam mempersatukan masyarakat dan membangun hubungan sosial psikologis dengan masyarakat di tengah kemajemukan Indonesia sehingga menciptakan kelekatan yang kuat antara ASN dan para pemangku kepentingan serta di antara para pemangku kepentingan itu sendiri; menjaga, mengembangkan, dan mewujudkan rasa persatuan dan kesatuan dalam kehidupan bermasyarakat, berbangsa dan bernegara Indonesia.
        Level 4
        Mendayagunakan perbedaan secara konstruktif dan kreatif untuk meningkatkan efektivitas organisasi.",
                'weight' => 11,
                'standard_rating' => 3.55,
                'order' => 9,
            ],
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
