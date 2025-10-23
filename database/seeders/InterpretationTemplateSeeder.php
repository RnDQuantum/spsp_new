<?php

namespace Database\Seeders;

use App\Models\InterpretationTemplate;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class InterpretationTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('interpretation_templates')->truncate();

        // GENERIC TEMPLATES (Fallback for any sub-aspect/aspect)
        $this->seedGenericTemplates();

        // SPECIFIC SUB-ASPECT TEMPLATES (From PDF analysis)
        $this->seedSubAspectTemplates();

        // KOMPETENSI ASPECT TEMPLATES (From PDF)
        $this->seedKompetensiTemplates();
    }

    /**
     * Generic fallback templates for all rating levels
     */
    private function seedGenericTemplates(): void
    {
        $genericTemplates = [
            // Rating 1 - Sangat Kurang
            [
                'rating' => 1,
                'text' => 'Kemampuan dalam aspek ini tergolong sangat kurang dan memerlukan pengembangan intensif. Individu mengalami kesulitan yang signifikan dalam menunjukkan kompetensi yang diperlukan, sehingga membutuhkan pelatihan dan bimbingan khusus untuk dapat mencapai standar yang ditetapkan.',
                'tone' => 'negative',
                'category' => 'development_area',
            ],
            // Rating 2 - Kurang
            [
                'rating' => 2,
                'text' => 'Kemampuan dalam aspek ini masih kurang memadai dan perlu ditingkatkan. Individu menunjukkan beberapa kesulitan dalam menampilkan kompetensi yang diharapkan, sehingga diperlukan upaya pengembangan yang konsisten untuk dapat memenuhi standar yang ditetapkan.',
                'tone' => 'negative',
                'category' => 'development_area',
            ],
            // Rating 3 - Cukup
            [
                'rating' => 3,
                'text' => 'Kemampuan dalam aspek ini cukup memadai dan sesuai dengan ekspektasi umum. Individu mampu menampilkan kompetensi yang diperlukan pada level yang dapat diterima, meskipun masih ada ruang untuk pengembangan lebih lanjut guna meningkatkan kinerja.',
                'tone' => 'neutral',
                'category' => 'neutral',
            ],
            // Rating 4 - Baik
            [
                'rating' => 4,
                'text' => 'Kemampuan dalam aspek ini tergolong baik dan dapat diandalkan. Individu secara konsisten mampu menampilkan kompetensi yang diperlukan dengan kualitas yang baik, menunjukkan kapasitas yang solid untuk menjalankan tanggung jawab dengan efektif.',
                'tone' => 'positive',
                'category' => 'strength',
            ],
            // Rating 5 - Sangat Baik
            [
                'rating' => 5,
                'text' => 'Kemampuan dalam aspek ini sangat baik dan menjadi kekuatan utama individu. Individu secara konsisten menampilkan kompetensi yang sangat tinggi, melampaui ekspektasi standar, dan dapat menjadi role model bagi rekan kerja dalam aspek ini.',
                'tone' => 'positive',
                'category' => 'strength',
            ],
        ];

        // We'll use these as fallback - no specific interpretable_id
        // Will be handled in service logic
        foreach ($genericTemplates as $template) {
            InterpretationTemplate::create([
                'interpretable_type' => 'sub_aspect',
                'interpretable_id' => 0, // 0 = generic fallback
                'rating_value' => $template['rating'],
                'template_text' => $template['text'],
                'tone' => $template['tone'],
                'category' => $template['category'],
                'is_active' => true,
            ]);

            // Also create for aspect type
            InterpretationTemplate::create([
                'interpretable_type' => 'aspect',
                'interpretable_id' => 0, // 0 = generic fallback
                'rating_value' => $template['rating'],
                'template_text' => $template['text'],
                'tone' => $template['tone'],
                'category' => $template['category'],
                'is_active' => true,
            ]);
        }
    }

    /**
     * Specific templates for POTENSI sub-aspects (from PDF analysis)
     */
    private function seedSubAspectTemplates(): void
    {
        $templates = [
            // KECERDASAN UMUM
            ['id' => 1, 'code' => 'kecerdasan_umum', 'rating' => 2, 'tone' => 'negative', 'text' => 'Individu memiliki kapasitas kecerdasan umum yang masih kurang memadai. Hal ini menunjukkan bahwa individu mengalami kesulitan dalam mempelajari tugas baru dan memerlukan waktu yang lebih lama untuk dapat memahami konsep-konsep yang kompleks.'],
            ['id' => 1, 'code' => 'kecerdasan_umum', 'rating' => 3, 'tone' => 'neutral', 'text' => 'Individu memiliki kapasitas kecerdasan umum yang cukup memadai. Hal ini menunjukkan kemampuannya untuk dapat secara cukup cepat mempelajari tugas baru yang akan diberikan kepadanya.'],
            ['id' => 1, 'code' => 'kecerdasan_umum', 'rating' => 4, 'tone' => 'positive', 'text' => 'Individu memiliki kapasitas kecerdasan umum yang baik. Hal ini menunjukkan kemampuannya untuk dapat dengan cepat mempelajari tugas baru dan memahami konsep-konsep kompleks yang diberikan kepadanya.'],

            // DAYA TANGKAP
            ['id' => 2, 'code' => 'daya_tangkap', 'rating' => 2, 'tone' => 'negative', 'text' => 'Kemampuan individu dalam menangkap informasi masih kurang memadai, sehingga hal ini berpengaruh terhadap kemampuannya yang kurang dalam memberikan umpan balik yang sesuai. Individu memerlukan penjelasan yang lebih detail dan berulang untuk dapat memahami instruksi dengan baik.'],
            ['id' => 2, 'code' => 'daya_tangkap', 'rating' => 3, 'tone' => 'neutral', 'text' => 'Kemampuan individu dalam menangkap informasi cukup memadai. Individu mampu memahami instruksi yang diberikan dengan cukup baik dan dapat memberikan respons yang sesuai dalam situasi kerja.'],
            ['id' => 2, 'code' => 'daya_tangkap', 'rating' => 4, 'tone' => 'positive', 'text' => 'Kemampuan individu dalam menangkap informasi tergolong baik. Ia mampu dengan cepat memahami instruksi dan informasi yang diberikan sehingga dapat memberikan respons yang tepat dan akurat.'],

            // KOMUNIKASI (Sub-aspect dalam Hubungan Sosial)
            ['id' => null, 'code' => 'komunikasi', 'rating' => 2, 'tone' => 'negative', 'text' => 'Kemampuan komunikasi individu masih perlu dioptimalkan kembali. Ia masih kesulitan untuk menyampaikan pendapatnya secara jelas sehingga orang lain yang mendengar merasa kesulitan dalam menangkap inti dari yang disampaikan. Hal ini dapat menjadi penghambat dalam pelaksanaan tugas yang memerlukan komunikasi efektif.'],
            ['id' => null, 'code' => 'komunikasi', 'rating' => 3, 'tone' => 'neutral', 'text' => 'Kemampuan komunikasi individu cukup memadai. Individu mampu menyampaikan informasi dengan cukup jelas dan dapat dipahami oleh rekan kerja, meskipun dalam situasi tertentu masih memerlukan penyempurnaan.'],
            ['id' => null, 'code' => 'komunikasi', 'rating' => 4, 'tone' => 'positive', 'text' => 'Kemampuan komunikasi individu tergolong baik. Individu mampu menyampaikan ide dan informasi dengan jelas, sistematis, dan mudah dipahami oleh berbagai kalangan.'],

            // STABILITAS EMOSI
            ['id' => null, 'code' => 'stabilitas_emosi', 'rating' => 3, 'tone' => 'neutral', 'text' => 'Individu mampu mengelola emosinya dengan cukup baik. Ia cukup mampu menjaga tampilan reaksi emosinya meskipun dihadapkan pada situasi yang kurang nyaman baginya, sehingga dapat tetap terjaga konsentrasi dan konsistensinya dalam situasi yang bervariasi.'],
            ['id' => null, 'code' => 'stabilitas_emosi', 'rating' => 4, 'tone' => 'positive', 'text' => 'Individu mampu mengelola emosinya dengan baik. Ia mampu menjaga tampilan reaksi emosinya meskipun dihadapkan pada situasi yang kurang nyaman baginya. Hal ini membantunya untuk tetap dapat terjaga konsentrasi dan konsistensinya dalam situasi apapun.'],

            // TANGGUNG JAWAB
            ['id' => 9, 'code' => 'tanggung_jawab', 'rating' => 3, 'tone' => 'neutral', 'text' => 'Individu menunjukkan rasa tanggung jawab yang cukup memadai dalam bekerja. Hal ini memungkinkan individu untuk dapat menyelesaikan tugas sesuai dengan waktu dan standar yang diberikan kepadanya.'],
            ['id' => 9, 'code' => 'tanggung_jawab', 'rating' => 4, 'tone' => 'positive', 'text' => 'Individu menunjukkan rasa tanggung jawab yang tergolong besar dalam bekerja. Hal ini dapat menunjangnya dalam melaksanakan tugasnya nanti, di mana individu mampu untuk bekerja hingga tuntas sesuai dengan waktu dan standar yang diberikan kepadanya.'],

            // KETELITIAN
            ['id' => null, 'code' => 'ketelitian', 'rating' => 3, 'tone' => 'neutral', 'text' => 'Ketelitian yang dimiliki individu tergolong cukup baik. Ia cukup mampu melakukan pengecekan terhadap hasil kerjanya untuk meminimalkan terjadinya kesalahan.'],
            ['id' => null, 'code' => 'ketelitian', 'rating' => 4, 'tone' => 'positive', 'text' => 'Ketelitian yang dimiliki individu tergolong tinggi. Ia mampu secara konsisten melakukan pengecekan ulang terhadap seluruh hasil kerjanya untuk menghindari terjadinya kesalahan.'],

            // KETEKUNAN
            ['id' => 6, 'code' => 'ketekunan', 'rating' => 3, 'tone' => 'neutral', 'text' => 'Individu mampu menampilkan ketekunan kerja yang memadai. Individu mampu menjaga konsentrasinya dalam menjalankan tugas-tugas rutinnya dengan cukup konsisten.'],
            ['id' => 6, 'code' => 'ketekunan', 'rating' => 4, 'tone' => 'positive', 'text' => 'Individu mampu menampilkan ketekunan kerja yang tinggi. Individu mampu menjaga konsentrasinya dalam menjalankan tugas-tugas rutinnya yang monoton dengan sangat konsisten.'],

            // INISIATIF
            ['id' => null, 'code' => 'inisiatif', 'rating' => 3, 'tone' => 'neutral', 'text' => 'Inisiatif yang dimiliki individu tergolong memadai. Ia cukup mampu untuk melakukan pekerjaan yang menjadi tanggung jawabnya dengan arahan minimal dari atasan.'],
            ['id' => null, 'code' => 'inisiatif', 'rating' => 4, 'tone' => 'positive', 'text' => 'Inisiatif yang dimiliki individu tergolong besar. Ia mampu untuk melakukan pekerjaan yang menjadi tanggung jawabnya tanpa perlu menunggu arahan dari orang lain. Ia secara mandiri berusaha memenuhi kebutuhan yang diperlukan untuk menyelesaikan pekerjaan.'],

            // MOTIVASI BERPRESTASI
            ['id' => 10, 'code' => 'motivasi_berprestasi', 'rating' => 3, 'tone' => 'neutral', 'text' => 'Individu memiliki dorongan yang cukup baik untuk menunjukkan hasil kinerja yang optimal. Ia berusaha untuk dapat menyelesaikan pekerjaan sesuai dengan standar yang ditetapkan.'],
            ['id' => 10, 'code' => 'motivasi_berprestasi', 'rating' => 4, 'tone' => 'positive', 'text' => 'Individu memiliki dorongan yang besar untuk menunjukkan hasil kinerja terbaiknya. Ia berusaha untuk dapat menyelesaikan pekerjaan melebihi dari standar yang ditetapkan.'],
        ];

        foreach ($templates as $template) {
            // Get sub_aspect ID by code if not specified
            if ($template['id'] === null) {
                $subAspect = DB::table('sub_aspects')
                    ->where('code', $template['code'])
                    ->first();

                if ($subAspect) {
                    $template['id'] = $subAspect->id;
                } else {
                    continue; // Skip if sub_aspect not found
                }
            }

            InterpretationTemplate::create([
                'interpretable_type' => 'sub_aspect',
                'interpretable_id' => $template['id'],
                'rating_value' => $template['rating'],
                'template_text' => $template['text'],
                'tone' => $template['tone'],
                'category' => $template['tone'] === 'positive' ? 'strength' : ($template['tone'] === 'negative' ? 'development_area' : 'neutral'),
                'is_active' => true,
            ]);
        }
    }

    /**
     * Templates for KOMPETENSI aspects (from PDF)
     */
    private function seedKompetensiTemplates(): void
    {
        $templates = [
            // INTEGRITAS
            ['code' => 'integritas', 'rating' => 3, 'tone' => 'neutral', 'text' => 'Individu cukup kompeten menampilkan kompetensi integritas. Individu cukup mampu mengingatkan rekan kerja untuk bertindak sesuai dengan etika dan kode etik dalam pelaksanaan tugas.'],
            ['code' => 'integritas', 'rating' => 4, 'tone' => 'positive', 'text' => 'Individu kompeten menampilkan kompetensi integritas sesuai dengan standar level yang ditetapkan. Secara konsisten mampu mengingatkan dan mengajak rekan kerja untuk bertindak sesuai dengan etika dan kode etik. Hal ini tentunya akan memberikan dukungan terhadap peran tugasnya sesuai dengan formasi yang dituju.'],

            // KERJASAMA
            ['code' => 'kerjasama', 'rating' => 3, 'tone' => 'neutral', 'text' => 'Kemampuan menumbuhkan tim kerja cukup kompeten. Hal ini cukup tergambarkan dengan bukti perilaku individu dalam membantu orang lain dan berbagi informasi yang relevan. Kemampuan ini memudahkannya untuk bekerjasama dalam melakukan tugas dalam memberikan pelayanan sesuai bidang tugas yang dituju.'],
            ['code' => 'kerjasama', 'rating' => 4, 'tone' => 'positive', 'text' => 'Kemampuan individu untuk bekerja sama dengan orang lain tergolong baik. Ia menunjukkan kesediaan untuk dapat berpartisipasi secara aktif dalam mencapai tujuan bersama dan mampu berkontribusi positif dalam tim.'],

            // KOMUNIKASI
            ['code' => 'komunikasi', 'rating' => 2, 'tone' => 'negative', 'text' => 'Individu masih perlu mengembangkan kompetensi komunikasi. Dalam level ini individu masih kurang aktif dalam menjalankan komunikasi formal dan informal, masih perlu meningkatkan kemampuan mendengarkan dan menyampaikan pesan dengan jelas.'],
            ['code' => 'komunikasi', 'rating' => 3, 'tone' => 'neutral', 'text' => 'Individu cukup mampu menampilkan kompetensi komunikasi dalam level yang ditetapkan dengan cukup kompeten. Dalam level ini individu cukup aktif menjalankan komunikasi secara formal dan informal; cukup menampilkan kesediaan mendengarkan orang lain, menginterpretasikan pesan dengan respon yang sesuai serta cukup mampu menyusun materi presentasi, pidato, naskah, laporan yang cukup bisa dimanfaatkan dalam pelaksanaan tugas.'],
            ['code' => 'komunikasi', 'rating' => 4, 'tone' => 'positive', 'text' => 'Individu mampu menampilkan kompetensi komunikasi dengan baik. Individu aktif dalam komunikasi formal dan informal, mampu mendengarkan dengan efektif, dan dapat menyusun berbagai materi komunikasi yang berkualitas untuk menunjang pelaksanaan tugas.'],

            // ORIENTASI PADA HASIL
            ['code' => 'orientasi_pada_hasil', 'rating' => 3, 'tone' => 'neutral', 'text' => 'Individu cukup kompeten dalam menampilkan kompetensi berorientasi pada hasil. Individu berupaya untuk meningkatkan hasil kerja sesuai dengan standar yang ditetapkan.'],
            ['code' => 'orientasi_pada_hasil', 'rating' => 4, 'tone' => 'positive', 'text' => 'Kompeten dalam menampilkan kompetensi berorientasi pada hasil. Dalam level ini individu mampu berupaya meningkatkan hasil kerja pribadi yang lebih tinggi dari standar yang ditetapkan, mencari, mencoba metode alternatif untuk peningkatan kinerja. Kapasitas ini akan sangat mampu dimanfaatkan individu untuk bertanggung jawab atas tugas-tugasnya sesuai formasi yang dituju.'],

            // PELAYANAN PUBLIK
            ['code' => 'pelayanan_publik', 'rating' => 3, 'tone' => 'neutral', 'text' => 'Cukup kompeten dalam menampilkan kompetensi pelayanan publik sesuai dengan standar level yang ditetapkan. Individu cukup mampu menunjukkan sikap yakin dalam mengerjakan tugas-tugas pelayanan publiknya serta cukup mampu secara aktif mencari informasi untuk mengenali kebutuhan dari pemangku kepentingan.'],
            ['code' => 'pelayanan_publik', 'rating' => 4, 'tone' => 'positive', 'text' => 'Kompeten dalam menampilkan kompetensi pelayanan publik. Individu mampu menunjukkan sikap profesional dan proaktif dalam memberikan pelayanan kepada publik serta mampu mengidentifikasi kebutuhan pemangku kepentingan dengan baik.'],

            // PENGEMBANGAN DIRI & ORANG LAIN
            ['code' => 'pengembangan_diri_orang_lain', 'rating' => 3, 'tone' => 'neutral', 'text' => 'Individu cukup kompeten dalam menampilkan kompetensi pengembangan diri dan orang lain. Individu menunjukkan upaya untuk mengembangkan kemampuan diri sendiri dan cukup mampu membantu orang lain dalam proses pembelajaran.'],
            ['code' => 'pengembangan_diri_orang_lain', 'rating' => 4, 'tone' => 'positive', 'text' => 'Kompeten dalam menampilkan kompetensi pengembangan diri dan orang lain. Individu memiliki kompetensi dalam mengembangkan kemampuan diri dan orang lain yang memenuhi standar. Ia tidak hanya dapat meningkatkan kemampuan diri, namun juga meningkatkan kemampuan orang lain guna mengajarkan metode lain yang dapat memudahkan pekerjaan mereka.'],

            // MENGELOLA PERUBAHAN
            ['code' => 'mengelola_perubahan', 'rating' => 3, 'tone' => 'neutral', 'text' => 'Individu cukup kompeten menampilkan kompetensi mengelola perubahan. Individu cukup mampu beradaptasi mengikuti perubahan yang ada di lingkungan kerja dan cukup tanggap dalam menerima perubahan.'],
            ['code' => 'mengelola_perubahan', 'rating' => 4, 'tone' => 'positive', 'text' => 'Kompeten menampilkan kompetensi mengelola perubahan sesuai tuntutan dalam level jabatan. Individu mampu proaktif dalam beradaptasi mengikuti perubahan yang ada di lingkungan kerja. Ia juga cepat dan tanggap dalam menerima perubahan.'],

            // PENGAMBILAN KEPUTUSAN
            ['code' => 'pengambilan_keputusan', 'rating' => 3, 'tone' => 'neutral', 'text' => 'Cukup kompeten dalam menampilkan kompetensi pengambilan keputusan. Dalam level ini individu cukup mampu menampilkan perilaku untuk menganalisa suatu masalah secara memadai serta membuat keputusan operasional berdasarkan kesimpulan dari berbagai sumber yang diterimanya.'],
            ['code' => 'pengambilan_keputusan', 'rating' => 4, 'tone' => 'positive', 'text' => 'Kompeten dalam menampilkan kompetensi pengambilan keputusan. Individu mampu menganalisa masalah dengan baik dan membuat keputusan yang tepat berdasarkan informasi yang tersedia dengan mempertimbangkan berbagai aspek yang relevan.'],

            // PEREKAT BANGSA
            ['code' => 'perekat_bangsa', 'rating' => 3, 'tone' => 'neutral', 'text' => 'Kemampuan dalam mempromosikan sikap toleransi cukup baik ditampilkan individu. Individu cukup mampu mengembangkan sikap saling menghargai dan menekankan persamaan dalam keberagaman.'],
            ['code' => 'perekat_bangsa', 'rating' => 4, 'tone' => 'positive', 'text' => 'Kemampuan dalam mempromosikan sikap toleransi mampu ditampilkan individu sesuai dengan standar level yang ditetapkan. Mampu secara aktif mengembangkan sikap saling menghargai dan menekankan persamaan dan persatuan. Kemampuan ini memungkinkannya untuk mampu bersikap tenang apabila menghadapi konflik yang melibatkan perbedaan suku, ras, dan agama.'],
        ];

        foreach ($templates as $template) {
            // Get aspect ID by code
            $aspect = DB::table('aspects')
                ->where('code', $template['code'])
                ->first();

            if (!$aspect) {
                continue; // Skip if not found
            }

            InterpretationTemplate::create([
                'interpretable_type' => 'aspect',
                'interpretable_id' => $aspect->id,
                'rating_value' => $template['rating'],
                'template_text' => $template['text'],
                'tone' => $template['tone'],
                'category' => $template['tone'] === 'positive' ? 'strength' : ($template['tone'] === 'negative' ? 'development_area' : 'neutral'),
                'is_active' => true,
            ]);
        }
    }
}
