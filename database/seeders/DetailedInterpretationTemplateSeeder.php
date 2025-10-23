<?php

namespace Database\Seeders;

use App\Models\InterpretationTemplate;
use Illuminate\Database\Seeder;

class DetailedInterpretationTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear existing templates
        InterpretationTemplate::truncate();

        // Seed POTENSI sub-aspects
        $this->seedHubunganSosial();
        $this->seedKecerdasan();
        $this->seedKepribadian();
        $this->seedCaraKerja();

        // Seed KOMPETENSI aspects
        $this->seedKompetensiIntegritas();
        $this->seedKompetensiKerjasama();
        $this->seedKompetensiKomunikasi();
        $this->seedKompetensiOrientasiHasil();
        $this->seedKompetensiPelayananPublik();
        $this->seedKompetensiPengembanganDiri();
        $this->seedKompetensiMengelolaPerubahan();
        $this->seedKompetensiPengambilanKeputusan();
        $this->seedKompetensiPerekatBangsa();

        // Seed generic fallbacks
        $this->seedGenericFallbacks();
    }

    protected function seedHubunganSosial(): void
    {
        $templates = [
            // Kepekaan Interpersonal
            [
                'interpretable_type' => 'sub_aspect',
                'interpretable_name' => 'Kepekaan Interpersonal',
                'rating_value' => 2,
                'template_text' => 'Kepekaan interpersonal yang dimiliki individu masih perlu ditingkatkan. Individu terkadang kesulitan dalam memahami kebutuhan orang-orang yang ada di sekitarnya sehingga responnya kurang sesuai dengan harapan. Hal ini dapat menjadi area pengembangan untuk meningkatkan efektivitas dalam berinteraksi dengan rekan kerja maupun pihak eksternal.',
                'tone' => 'neutral',
                'category' => 'development_area',
            ],
            [
                'interpretable_type' => 'sub_aspect',
                'interpretable_name' => 'Kepekaan Interpersonal',
                'rating_value' => 3,
                'template_text' => 'Memiliki kepekaan yang cukup memadai dalam memahami kebutuhan orang-orang yang ada di sekitarnya. Individu berusaha untuk memenuhi kebutuhan yang diperlukan oleh orang yang ada di sekitarnya, terutama yang menjadi kebutuhan kelompoknya.',
                'tone' => 'neutral',
                'category' => 'development_area',
            ],
            [
                'interpretable_type' => 'sub_aspect',
                'interpretable_name' => 'Kepekaan Interpersonal',
                'rating_value' => 4,
                'template_text' => 'Individu memiliki kepekaan interpersonal yang baik. Ia mampu dengan cepat mengenali kebutuhan dan perasaan orang lain di sekitarnya, sehingga dapat memberikan dukungan yang tepat. Kemampuan ini sangat mendukung dalam membangun hubungan kerja yang harmonis dan produktif.',
                'tone' => 'neutral',
                'category' => 'development_area',
            ],

            // Komunikasi (sub_aspect_id: 15)
            [
                'interpretable_type' => 'sub_aspect',
                'interpretable_name' => 'Komunikasi',
                'rating_value' => 2,
                'template_text' => 'Kemampuan komunikasi individu masih perlu dioptimalkan kembali. Ia masih kesulitan untuk menyampaikan pendapatnya secara jelas sehingga orang lain yang mendengar merasa kesulitan dalam menangkap inti dari yang disampaikan oleh individu. Hal ini dikhawatirkan dapat menjadi penghambat dalam posisi yang individu tuju.',
                'tone' => 'neutral',
                'category' => 'development_area',
            ],
            [
                'interpretable_type' => 'sub_aspect',
                'interpretable_name' => 'Komunikasi',
                'rating_value' => 3,
                'template_text' => 'Kemampuan komunikasi individu tergolong cukup memadai. Ia mampu menyampaikan informasi dengan cukup jelas kepada orang lain, meskipun terkadang masih perlu penyesuaian dalam penyampaian untuk audiens yang berbeda. Dengan pengembangan lebih lanjut, kemampuan komunikasinya dapat menjadi lebih efektif.',
                'tone' => 'neutral',
                'category' => 'development_area',
            ],
            [
                'interpretable_type' => 'sub_aspect',
                'interpretable_name' => 'Komunikasi',
                'rating_value' => 4,
                'template_text' => 'Individu memiliki kemampuan komunikasi yang baik. Ia mampu menyampaikan gagasan dan informasi secara jelas, terstruktur, dan mudah dipahami oleh orang lain. Kemampuan ini mendukungnya dalam berkoordinasi dan berkolaborasi dengan berbagai pihak dalam menjalankan tugas.',
                'tone' => 'neutral',
                'category' => 'development_area',
            ],

            // Kontak Sosial/Hubungan Interpersonal (sub_aspect_id: 16)
            [
                'interpretable_type' => 'sub_aspect',
                'interpretable_name' => 'Kontak Sosial',
                'rating_value' => 2,
                'template_text' => 'Dalam membangun relasi dengan orang lain, individu masih perlu mengembangkan kemampuannya. Ia cenderung kesulitan dalam memulai dan mempertahankan hubungan dengan orang yang baru dikenal, sehingga jaringan relasinya masih terbatas. Pengembangan kemampuan ini penting untuk mendukung efektivitas kerja.',
                'tone' => 'neutral',
                'category' => 'development_area',
            ],
            [
                'interpretable_type' => 'sub_aspect',
                'interpretable_name' => 'Kontak Sosial',
                'rating_value' => 3,
                'template_text' => 'Individu cukup mampu membangun relasi dengan orang yang ada di sekitarnya. Hal ini menunjangnya dalam melakukan pekerjaan pada posisi yang dituju di mana ia dapat membangun hubungan dengan orang-orang berkepentingan yang dapat mendukungnya dalam menyelesaikan pekerjaan yang dilakukan.',
                'tone' => 'neutral',
                'category' => 'development_area',
            ],
            [
                'interpretable_type' => 'sub_aspect',
                'interpretable_name' => 'Kontak Sosial',
                'rating_value' => 4,
                'template_text' => 'Individu sangat mampu membangun dan memelihara hubungan interpersonal yang positif. Ia dengan mudah menjalin relasi dengan berbagai kalangan dan mampu mempertahankan hubungan tersebut dalam jangka panjang. Jaringan relasi yang luas ini menjadi aset penting dalam menjalankan tugas dan tanggung jawabnya.',
                'tone' => 'neutral',
                'category' => 'development_area',
            ],

            // Penyesuaian Diri (sub_aspect_id: 17)
            [
                'interpretable_type' => 'sub_aspect',
                'interpretable_name' => 'Penyesuaian Diri',
                'rating_value' => 2,
                'template_text' => 'Kemampuan penyesuaian diri individu masih perlu ditingkatkan. Ia memerlukan waktu yang cukup lama untuk dapat beradaptasi dengan lingkungan kerja baru atau perubahan yang terjadi. Hal ini dapat mempengaruhi kecepatan dalam memberikan kontribusi optimal di lingkungan baru.',
                'tone' => 'neutral',
                'category' => 'development_area',
            ],
            [
                'interpretable_type' => 'sub_aspect',
                'interpretable_name' => 'Penyesuaian Diri',
                'rating_value' => 3,
                'template_text' => 'Kemampuan individu dalam menyesuaikan diri terhadap lingkungan dan tugas baru tergolong cukup memadai. Ia tidak merasa kesulitan dalam melakukan adaptasi sehingga hal ini mendukungnya untuk dapat memberikan respons yang sesuai.',
                'tone' => 'neutral',
                'category' => 'development_area',
            ],
            [
                'interpretable_type' => 'sub_aspect',
                'interpretable_name' => 'Penyesuaian Diri',
                'rating_value' => 4,
                'template_text' => 'Individu memiliki kemampuan penyesuaian diri yang sangat baik. Ia dapat dengan cepat beradaptasi terhadap lingkungan kerja baru, perubahan kebijakan, maupun tantangan yang berbeda. Fleksibilitas ini memungkinkannya untuk tetap produktif dalam berbagai situasi.',
                'tone' => 'neutral',
                'category' => 'development_area',
            ],
        ];

        foreach ($templates as $template) {
            InterpretationTemplate::create([
                ...$template,
                'version' => 'v2.0',
                'is_active' => true,
            ]);
        }
    }

    protected function seedKecerdasan(): void
    {
        $templates = [
            // Kecerdasan Umum (sub_aspect_id: 1)
            [
                'interpretable_type' => 'sub_aspect',
                'interpretable_name' => 'Kecerdasan Umum',
                'rating_value' => 2,
                'template_text' => 'Berdasarkan hasil asesmen yang dilakukan, kapasitas kecerdasan umum individu masih kurang memadai. Hal ini menunjukkan individu memerlukan waktu yang lebih lama untuk mempelajari tugas baru yang akan diberikan kepadanya dan kesulitan dalam memahami konsep-konsep yang kompleks.',
                'tone' => 'neutral',
                'category' => 'development_area',
            ],
            [
                'interpretable_type' => 'sub_aspect',
                'interpretable_name' => 'Kecerdasan Umum',
                'rating_value' => 3,
                'template_text' => 'Berdasarkan hasil asesmen yang dilakukan, individu memiliki kapasitas kecerdasan yang cukup memadai. Hal ini menunjukkan kemampuannya untuk dapat secara cukup cepat mempelajari tugas baru yang akan diberikan kepadanya.',
                'tone' => 'neutral',
                'category' => 'development_area',
            ],
            [
                'interpretable_type' => 'sub_aspect',
                'interpretable_name' => 'Kecerdasan Umum',
                'rating_value' => 4,
                'template_text' => 'Berdasarkan hasil asesmen yang dilakukan, individu memiliki kapasitas kecerdasan umum yang baik. Hal ini menunjukkan kemampuannya untuk dapat dengan cepat mempelajari tugas baru, memahami konsep yang kompleks, dan menerapkannya dalam konteks pekerjaan.',
                'tone' => 'neutral',
                'category' => 'development_area',
            ],

            // Daya Tangkap (sub_aspect_id: 2)
            [
                'interpretable_type' => 'sub_aspect',
                'interpretable_name' => 'Daya Tangkap',
                'rating_value' => 2,
                'template_text' => 'Kemampuan individu dalam menangkap informasi masih kurang memadai, sehingga hal ini berpengaruh terhadap kemampuannya yang kurang dalam memberikan umpan balik yang sesuai.',
                'tone' => 'neutral',
                'category' => 'development_area',
            ],
            [
                'interpretable_type' => 'sub_aspect',
                'interpretable_name' => 'Daya Tangkap',
                'rating_value' => 3,
                'template_text' => 'Kemampuan individu dalam menangkap informasi cukup memadai. Individu mampu memahami instruksi yang diberikan dengan cukup baik dan dapat memberikan respons yang sesuai dalam situasi kerja.',
                'tone' => 'neutral',
                'category' => 'development_area',
            ],
            [
                'interpretable_type' => 'sub_aspect',
                'interpretable_name' => 'Daya Tangkap',
                'rating_value' => 4,
                'template_text' => 'Individu memiliki daya tangkap yang baik. Ia mampu dengan cepat memahami informasi yang diberikan, menangkap inti permasalahan, dan memberikan respons yang tepat. Kemampuan ini sangat mendukung efisiensi dalam bekerja.',
                'tone' => 'neutral',
                'category' => 'development_area',
            ],

            // Daya Analisa (sub_aspect_id: 3)
            [
                'interpretable_type' => 'sub_aspect',
                'interpretable_name' => 'Daya Analisa',
                'rating_value' => 2,
                'template_text' => 'Kemampuan analisa individu masih perlu ditingkatkan. Ia kesulitan dalam menguraikan permasalahan kompleks dan menemukan akar penyebab masalah. Diperlukan bimbingan untuk mengembangkan kemampuan berpikir analitis yang lebih baik.',
                'tone' => 'neutral',
                'category' => 'development_area',
            ],
            [
                'interpretable_type' => 'sub_aspect',
                'interpretable_name' => 'Daya Analisa',
                'rating_value' => 3,
                'template_text' => 'Pada dasarnya, individu cukup mampu melakukan analisa masalah untuk menemukan akar penyebab permasalahan yang terjadi. Akan tetapi untuk mendukungnya dalam melaksanakan tugas nantinya, ia masih perlu mengasah kemampuan analisanya agar lebih cepat dalam menguraikan permasalahan yang terjadi.',
                'tone' => 'neutral',
                'category' => 'development_area',
            ],
            [
                'interpretable_type' => 'sub_aspect',
                'interpretable_name' => 'Daya Analisa',
                'rating_value' => 4,
                'template_text' => 'Individu memiliki kemampuan analisa yang kuat. Ia mampu menguraikan permasalahan kompleks menjadi bagian-bagian yang lebih sederhana, mengidentifikasi pola dan hubungan sebab-akibat, serta menemukan akar permasalahan dengan cepat dan tepat.',
                'tone' => 'neutral',
                'category' => 'development_area',
            ],

            // Kemampuan Logika (sub_aspect_id: 4)
            [
                'interpretable_type' => 'sub_aspect',
                'interpretable_name' => 'Kemampuan Logika',
                'rating_value' => 2,
                'template_text' => 'Kemampuan berpikir logis individu masih kurang memadai. Ia terkadang kesulitan dalam menyusun argumentasi yang logis dan sistematis, serta mengalami kendala dalam memecahkan masalah yang memerlukan penalaran deduktif maupun induktif.',
                'tone' => 'neutral',
                'category' => 'development_area',
            ],
            [
                'interpretable_type' => 'sub_aspect',
                'interpretable_name' => 'Kemampuan Logika',
                'rating_value' => 3,
                'template_text' => 'Kemampuan berpikir logis individu tergolong cukup memadai. Ia mampu menyusun pemikiran secara sistematis dan logis dalam menyelesaikan permasalahan, meskipun terkadang masih memerlukan waktu untuk masalah yang sangat kompleks.',
                'tone' => 'neutral',
                'category' => 'development_area',
            ],
            [
                'interpretable_type' => 'sub_aspect',
                'interpretable_name' => 'Kemampuan Logika',
                'rating_value' => 4,
                'template_text' => 'Individu memiliki kemampuan berpikir logis yang baik. Ia mampu menyusun argumentasi yang sistematis, membuat kesimpulan yang tepat berdasarkan data dan fakta, serta menerapkan logika dalam pemecahan masalah secara efektif.',
                'tone' => 'neutral',
                'category' => 'development_area',
            ],
        ];

        foreach ($templates as $template) {
            InterpretationTemplate::create([
                ...$template,
                'version' => 'v2.0',
                'is_active' => true,
            ]);
        }
    }

    protected function seedKepribadian(): void
    {
        $templates = [
            // Kepercayaan Diri (sub_aspect_id: 18)
            [
                'interpretable_type' => 'sub_aspect',
                'interpretable_name' => 'Kepercayaan Diri',
                'rating_value' => 2,
                'template_text' => 'Kepercayaan diri yang dimiliki individu masih perlu ditingkatkan. Ia cenderung ragu-ragu dalam mengambil keputusan dan kurang yakin dengan kemampuannya sendiri. Hal ini dapat mempengaruhi kualitas kinerja dan inisiatif dalam bekerja.',
                'tone' => 'neutral',
                'category' => 'development_area',
            ],
            [
                'interpretable_type' => 'sub_aspect',
                'interpretable_name' => 'Kepercayaan Diri',
                'rating_value' => 3,
                'template_text' => 'Individu merupakan sosok yang cukup mampu tampil percaya diri. Ia cukup memiliki keyakinan akan kemampuannya yang baik dalam melaksanakan pekerjaan, sehingga hal ini mendorongnya untuk dapat menjalankan tugas sebaik-baiknya.',
                'tone' => 'neutral',
                'category' => 'development_area',
            ],
            [
                'interpretable_type' => 'sub_aspect',
                'interpretable_name' => 'Kepercayaan Diri',
                'rating_value' => 4,
                'template_text' => 'Individu memiliki kepercayaan diri yang tinggi. Ia yakin dengan kemampuan dirinya dan tidak ragu dalam mengambil keputusan maupun menghadapi tantangan. Kepercayaan diri ini memberikan kekuatan untuk tampil optimal dalam berbagai situasi.',
                'tone' => 'neutral',
                'category' => 'development_area',
            ],

            // Stabilitas Emosi (sub_aspect_id: 19)
            [
                'interpretable_type' => 'sub_aspect',
                'interpretable_name' => 'Stabilitas Emosi',
                'rating_value' => 2,
                'template_text' => 'Stabilitas emosi individu masih perlu ditingkatkan. Ia cenderung mudah terpengaruh oleh situasi yang kurang nyaman dan sulit mengendalikan reaksi emosionalnya. Hal ini dapat mempengaruhi konsistensi kinerja dalam situasi yang penuh tekanan.',
                'tone' => 'neutral',
                'category' => 'development_area',
            ],
            [
                'interpretable_type' => 'sub_aspect',
                'interpretable_name' => 'Stabilitas Emosi',
                'rating_value' => 3,
                'template_text' => 'Individu cukup mampu mengelola emosinya. Ia dapat menjaga tampilan reaksi emosinya dalam sebagian besar situasi, meskipun terkadang masih terpengaruh dalam situasi yang sangat menekan.',
                'tone' => 'neutral',
                'category' => 'development_area',
            ],
            [
                'interpretable_type' => 'sub_aspect',
                'interpretable_name' => 'Stabilitas Emosi',
                'rating_value' => 4,
                'template_text' => 'Individu mampu mengelola emosinya dengan baik. Ia mampu menjaga tampilan reaksi emosinya meskipun dihadapkan pada situasi yang kurang nyaman baginya. Hal ini membantunya untuk tetap dapat terjaga konsentrasi dan konsistensinya dalam situasi apapun.',
                'tone' => 'neutral',
                'category' => 'development_area',
            ],

            // Daya Tahan Stress (sub_aspect_id: 20)
            [
                'interpretable_type' => 'sub_aspect',
                'interpretable_name' => 'Daya Tahan Stress',
                'rating_value' => 2,
                'template_text' => 'Daya tahan terhadap stress yang dimiliki individu masih perlu ditingkatkan. Ia cenderung mudah merasa tertekan ketika dihadapkan pada beban kerja yang tinggi atau deadline yang ketat, yang dapat mempengaruhi kualitas hasil kerjanya.',
                'tone' => 'neutral',
                'category' => 'development_area',
            ],
            [
                'interpretable_type' => 'sub_aspect',
                'interpretable_name' => 'Daya Tahan Stress',
                'rating_value' => 3,
                'template_text' => 'Dalam menghadapi tugas yang beragam, individu menunjukkan daya tahan yang cukup baik. Ia cukup mampu mengelola tekanan kerja dan tetap dapat menyelesaikan tugas sesuai dengan standar yang ditetapkan.',
                'tone' => 'neutral',
                'category' => 'development_area',
            ],
            [
                'interpretable_type' => 'sub_aspect',
                'interpretable_name' => 'Daya Tahan Stress',
                'rating_value' => 4,
                'template_text' => 'Dalam menghadapi tugas yang beragam yang diberikan nantinya, individu menunjukkan daya tahan yang kuat di mana ia tidak mudah merasa tertekan dan mampu menyelesaikan tugas dengan baik sesuai dengan standar yang ditetapkan oleh instansi.',
                'tone' => 'neutral',
                'category' => 'development_area',
            ],

            // Kepemimpinan (sub_aspect_id: 21)
            [
                'interpretable_type' => 'sub_aspect',
                'interpretable_name' => 'Kepemimpinan',
                'rating_value' => 2,
                'template_text' => 'Potensi kepemimpinan individu masih perlu dikembangkan. Ia cenderung kesulitan dalam mengkoordinasikan tim, memberikan arahan yang jelas, dan mengambil keputusan untuk kepentingan kelompok. Diperlukan pengembangan lebih lanjut untuk meningkatkan kemampuan ini.',
                'tone' => 'neutral',
                'category' => 'development_area',
            ],
            [
                'interpretable_type' => 'sub_aspect',
                'interpretable_name' => 'Kepemimpinan',
                'rating_value' => 3,
                'template_text' => 'Kemampuan individu dalam memimpin tergolong cukup baik, sehingga ia mampu menunjukkan potensinya dalam mengkoordinir dan pembagian tugas kepada anggota yang ada di dalam timnya nanti.',
                'tone' => 'neutral',
                'category' => 'development_area',
            ],
            [
                'interpretable_type' => 'sub_aspect',
                'interpretable_name' => 'Kepemimpinan',
                'rating_value' => 4,
                'template_text' => 'Individu memiliki potensi kepemimpinan yang kuat. Ia mampu mengkoordinasikan tim dengan baik, memberikan arahan yang jelas, mengambil keputusan yang tepat, dan menginspirasi anggota tim untuk mencapai tujuan bersama.',
                'tone' => 'neutral',
                'category' => 'development_area',
            ],
        ];

        foreach ($templates as $template) {
            InterpretationTemplate::create([
                ...$template,
                'version' => 'v2.0',
                'is_active' => true,
            ]);
        }
    }

    protected function seedCaraKerja(): void
    {
        $templates = [
            // Sistematika Kerja (sub_aspect_id: 5)
            [
                'interpretable_type' => 'sub_aspect',
                'interpretable_name' => 'Sistematika Kerja',
                'rating_value' => 2,
                'template_text' => 'Sistematika kerja individu masih perlu ditingkatkan. Ia cenderung kurang terorganisir dalam merencanakan dan melaksanakan pekerjaan, sehingga efisiensi kerjanya belum optimal. Diperlukan bimbingan untuk mengembangkan kebiasaan kerja yang lebih terstruktur.',
                'tone' => 'neutral',
                'category' => 'development_area',
            ],
            [
                'interpretable_type' => 'sub_aspect',
                'interpretable_name' => 'Sistematika Kerja',
                'rating_value' => 3,
                'template_text' => 'Dalam bekerja, individu cukup mampu mengelola pekerjaan yang menjadi tanggung jawabnya sesuai dengan prioritas penyelesaian masalah sehingga dapat selesai sesuai tenggat waktunya.',
                'tone' => 'neutral',
                'category' => 'development_area',
            ],
            [
                'interpretable_type' => 'sub_aspect',
                'interpretable_name' => 'Sistematika Kerja',
                'rating_value' => 4,
                'template_text' => 'Individu memiliki sistematika kerja yang sangat baik. Ia mampu merencanakan, mengorganisir, dan melaksanakan pekerjaan secara terstruktur dan efisien. Kemampuan ini memastikan penyelesaian tugas tepat waktu dengan kualitas yang baik.',
                'tone' => 'neutral',
                'category' => 'development_area',
            ],

            // Ketekunan (sub_aspect_id: 6)
            [
                'interpretable_type' => 'sub_aspect',
                'interpretable_name' => 'Ketekunan',
                'rating_value' => 2,
                'template_text' => 'Ketekunan individu dalam bekerja masih perlu ditingkatkan. Ia cenderung mudah kehilangan fokus ketika menghadapi tugas-tugas yang monoton atau memerlukan waktu lama, sehingga konsistensi kerjanya perlu diperbaiki.',
                'tone' => 'neutral',
                'category' => 'development_area',
            ],
            [
                'interpretable_type' => 'sub_aspect',
                'interpretable_name' => 'Ketekunan',
                'rating_value' => 3,
                'template_text' => 'Mampu menampilkan ketekunan kerja yang cukup memadai, individu cukup mampu menjaga konsentrasinya dalam menjalankan tugas-tugas rutinnya yang monoton.',
                'tone' => 'neutral',
                'category' => 'development_area',
            ],
            [
                'interpretable_type' => 'sub_aspect',
                'interpretable_name' => 'Ketekunan',
                'rating_value' => 4,
                'template_text' => 'Mampu menampilkan ketekunan kerja yang memadai, individu mampu menjaga konsentrasinya dalam menjalankan tugas-tugas rutinnya yang monoton. Ia tidak mudah menyerah dan tetap konsisten dalam menyelesaikan pekerjaan hingga tuntas.',
                'tone' => 'neutral',
                'category' => 'development_area',
            ],

            // Kerjasama (sub_aspect_id: 7)
            [
                'interpretable_type' => 'sub_aspect',
                'interpretable_name' => 'Kerjasama',
                'rating_value' => 2,
                'template_text' => 'Kemampuan bekerja sama dengan orang lain masih perlu ditingkatkan. Individu cenderung lebih fokus pada tugasnya sendiri dan kurang aktif dalam berpartisipasi untuk mencapai tujuan tim. Hal ini dapat mempengaruhi efektivitas kerja tim.',
                'tone' => 'neutral',
                'category' => 'development_area',
            ],
            [
                'interpretable_type' => 'sub_aspect',
                'interpretable_name' => 'Kerjasama',
                'rating_value' => 3,
                'template_text' => 'Kemampuan individu untuk bekerja sama dengan orang lain tergolong cukup baik. Ia menunjukkan kesediaan untuk dapat berpartisipasi secara aktif dalam mencapai tujuan bersama.',
                'tone' => 'neutral',
                'category' => 'development_area',
            ],
            [
                'interpretable_type' => 'sub_aspect',
                'interpretable_name' => 'Kerjasama',
                'rating_value' => 4,
                'template_text' => 'Individu memiliki kemampuan bekerja sama yang sangat baik. Ia aktif berpartisipasi dalam tim, mendukung rekan kerja, dan berkontribusi secara positif untuk mencapai tujuan bersama. Sikap kolaboratifnya sangat mendukung efektivitas tim.',
                'tone' => 'neutral',
                'category' => 'development_area',
            ],

            // Tanggung Jawab (sub_aspect_id: 9)
            [
                'interpretable_type' => 'sub_aspect',
                'interpretable_name' => 'Tanggung Jawab',
                'rating_value' => 2,
                'template_text' => 'Rasa tanggung jawab individu masih perlu ditingkatkan. Ia terkadang kurang konsisten dalam menyelesaikan tugas hingga tuntas dan perlu diingatkan untuk memenuhi komitmennya. Pengembangan sikap bertanggung jawab sangat penting untuk keberhasilan dalam posisi yang dituju.',
                'tone' => 'neutral',
                'category' => 'development_area',
            ],
            [
                'interpretable_type' => 'sub_aspect',
                'interpretable_name' => 'Tanggung Jawab',
                'rating_value' => 3,
                'template_text' => 'Individu menunjukkan rasa tanggung jawab yang cukup baik dalam bekerja. Ia cukup mampu untuk bekerja hingga tuntas sesuai dengan waktu dan standar yang diberikan kepadanya.',
                'tone' => 'neutral',
                'category' => 'development_area',
            ],
            [
                'interpretable_type' => 'sub_aspect',
                'interpretable_name' => 'Tanggung Jawab',
                'rating_value' => 4,
                'template_text' => 'Individu menunjukkan rasa tanggung jawab yang tergolong besar dalam bekerja. Hal ini dapat menunjangnya dalam melaksanakan tugasnya nanti, di mana individu mampu untuk bekerja hingga tuntas sesuai dengan waktu dan standar yang diberikan kepadanya.',
                'tone' => 'neutral',
                'category' => 'development_area',
            ],
        ];

        foreach ($templates as $template) {
            InterpretationTemplate::create([
                ...$template,
                'version' => 'v2.0',
                'is_active' => true,
            ]);
        }
    }

    protected function seedKompetensiIntegritas(): void
    {
        $templates = [
            [
                'interpretable_type' => 'aspect',
                'interpretable_name' => 'Integritas',
                'rating_value' => 2,
                'template_text' => 'Kemampuan menampilkan kompetensi integritas masih perlu ditingkatkan. Individu belum konsisten dalam mengingatkan rekan kerja untuk bertindak sesuai etika dan kode etik. Diperlukan pengembangan lebih lanjut dalam menunjukkan perilaku yang berintegritas dalam pelaksanaan tugas.',
                'tone' => 'neutral',
                'category' => 'development_area',
            ],
            [
                'interpretable_type' => 'aspect',
                'interpretable_name' => 'Integritas',
                'rating_value' => 3,
                'template_text' => 'Individu cukup kompeten menampilkan kompetensi integritas. Individu cukup mampu mengingatkan rekan kerja untuk bertindak sesuai dengan etika dan kode etik dalam pelaksanaan tugas.',
                'tone' => 'neutral',
                'category' => 'neutral',
            ],
            [
                'interpretable_type' => 'aspect',
                'interpretable_name' => 'Integritas',
                'rating_value' => 4,
                'template_text' => 'Individu kompeten menampilkan kompetensi integritas sesuai dengan standar level yang ditetapkan. Secara konsisten mampu mengingatkan dan mengajak rekan kerja untuk bertindak sesuai dengan etika dan kode etik. Hal ini tentunya akan memberikan dukungan terhadap peran tugasnya sesuai dengan formasi yang dituju.',
                'tone' => 'positive',
                'category' => 'strength',
            ],
        ];

        foreach ($templates as $template) {
            InterpretationTemplate::create([
                ...$template,
                'version' => 'v2.0',
                'is_active' => true,
            ]);
        }
    }

    protected function seedKompetensiKerjasama(): void
    {
        $templates = [
            [
                'interpretable_type' => 'aspect',
                'interpretable_name' => 'Kerjasama',
                'rating_value' => 2,
                'template_text' => 'Kemampuan menumbuhkan tim kerja masih perlu dikembangkan. Individu belum konsisten dalam membantu orang lain dan berbagi informasi yang relevan. Diperlukan upaya lebih untuk meningkatkan kolaborasi dalam tim.',
                'tone' => 'neutral',
                'category' => 'development_area',
            ],
            [
                'interpretable_type' => 'aspect',
                'interpretable_name' => 'Kerjasama',
                'rating_value' => 3,
                'template_text' => 'Kemampuan menumbuhkan tim kerja cukup kompeten. Hal ini cukup tergambarkan dengan bukti perilaku individu dalam membantu orang lain dan berbagi informasi yang relevan. Kemampuan ini memudahkannya untuk bekerjasama dalam melakukan tugas dalam memberikan pelayanan sesuai bidang tugas yang dituju.',
                'tone' => 'neutral',
                'category' => 'neutral',
            ],
            [
                'interpretable_type' => 'aspect',
                'interpretable_name' => 'Kerjasama',
                'rating_value' => 4,
                'template_text' => 'Kompeten dalam menampilkan kemampuan menumbuhkan tim kerja. Individu secara konsisten membantu orang lain dan aktif berbagi informasi yang relevan untuk keberhasilan tim. Kemampuan kolaborasi yang kuat ini sangat mendukung dalam pelaksanaan tugas sesuai formasi yang dituju.',
                'tone' => 'positive',
                'category' => 'strength',
            ],
        ];

        foreach ($templates as $template) {
            InterpretationTemplate::create([
                ...$template,
                'version' => 'v2.0',
                'is_active' => true,
            ]);
        }
    }

    protected function seedKompetensiKomunikasi(): void
    {
        $templates = [
            [
                'interpretable_type' => 'aspect',
                'interpretable_name' => 'Komunikasi',
                'rating_value' => 2,
                'template_text' => 'Kemampuan komunikasi masih perlu ditingkatkan. Individu belum optimal dalam menjalankan komunikasi formal dan informal, serta masih kesulitan dalam menyampaikan pesan dengan jelas dan menyusun materi presentasi yang efektif.',
                'tone' => 'neutral',
                'category' => 'development_area',
            ],
            [
                'interpretable_type' => 'aspect',
                'interpretable_name' => 'Komunikasi',
                'rating_value' => 3,
                'template_text' => 'Individu cukup mampu menampilkan kompetensi komunikasi dalam level yang ditetapkan dengan cukup kompeten. Dalam level ini individu cukup aktif menjalankan komunikasi secara formal dan informal; cukup menampilkan kesediaan mendengarkan orang lain, menginterpretasikan pesan dengan respon yang sesuai serta cukup mampu menyusun materi presentasi, pidato, naskah, laporan yang cukup bisa dimanfaatkan dalam pelaksanaan tugas.',
                'tone' => 'neutral',
                'category' => 'neutral',
            ],
            [
                'interpretable_type' => 'aspect',
                'interpretable_name' => 'Komunikasi',
                'rating_value' => 4,
                'template_text' => 'Kompeten dalam menampilkan kompetensi komunikasi. Individu aktif menjalankan komunikasi secara formal dan informal dengan efektif; menampilkan kesediaan mendengarkan orang lain, menginterpretasikan pesan dengan respon yang tepat serta mampu menyusun materi presentasi, pidato, naskah, dan laporan yang berkualitas untuk mendukung pelaksanaan tugas.',
                'tone' => 'positive',
                'category' => 'strength',
            ],
        ];

        foreach ($templates as $template) {
            InterpretationTemplate::create([
                ...$template,
                'version' => 'v2.0',
                'is_active' => true,
            ]);
        }
    }

    protected function seedKompetensiOrientasiHasil(): void
    {
        $templates = [
            [
                'interpretable_type' => 'aspect',
                'interpretable_name' => 'Orientasi Pada Hasil',
                'rating_value' => 2,
                'template_text' => 'Orientasi pada hasil masih perlu ditingkatkan. Individu belum konsisten dalam berupaya meningkatkan hasil kerja melebihi standar dan belum aktif mencari metode alternatif untuk peningkatan kinerja.',
                'tone' => 'neutral',
                'category' => 'development_area',
            ],
            [
                'interpretable_type' => 'aspect',
                'interpretable_name' => 'Orientasi Pada Hasil',
                'rating_value' => 3,
                'template_text' => 'Cukup kompeten dalam menampilkan kompetensi berorientasi pada hasil. Individu cukup mampu berupaya meningkatkan hasil kerja sesuai dengan standar yang ditetapkan dan mulai mencoba metode alternatif untuk peningkatan kinerja dalam pelaksanaan tugasnya.',
                'tone' => 'neutral',
                'category' => 'neutral',
            ],
            [
                'interpretable_type' => 'aspect',
                'interpretable_name' => 'Orientasi Pada Hasil',
                'rating_value' => 4,
                'template_text' => 'Kompeten dalam menampilkan kompetensi berorientasi pada hasil. Dalam level ini individu mampu berupaya meningkatkan hasil kerja pribadi yang lebih tinggi dari standar yang ditetapkan, mencari, mencoba metode alternatif untuk peningkatan kinerja. Kapasitas ini akan sangat mampu dimanfaatkan individu untuk bertanggung jawab atas tugas-tugasnya sesuai formasi yang dituju.',
                'tone' => 'positive',
                'category' => 'strength',
            ],
        ];

        foreach ($templates as $template) {
            InterpretationTemplate::create([
                ...$template,
                'version' => 'v2.0',
                'is_active' => true,
            ]);
        }
    }

    protected function seedKompetensiPelayananPublik(): void
    {
        $templates = [
            [
                'interpretable_type' => 'aspect',
                'interpretable_name' => 'Pelayanan Publik',
                'rating_value' => 2,
                'template_text' => 'Kemampuan pelayanan publik masih perlu ditingkatkan. Individu belum konsisten dalam menunjukkan sikap yakin dalam mengerjakan tugas pelayanan publik dan belum optimal dalam mencari informasi untuk mengenali kebutuhan pemangku kepentingan.',
                'tone' => 'neutral',
                'category' => 'development_area',
            ],
            [
                'interpretable_type' => 'aspect',
                'interpretable_name' => 'Pelayanan Publik',
                'rating_value' => 3,
                'template_text' => 'Cukup kompeten dalam menampilkan kompetensi pelayanan publik sesuai dengan standar level yang ditetapkan. Individu cukup mampu menunjukkan sikap yakin dalam mengerjakan tugas-tugas pelayanan publiknya serta cukup mampu secara aktif mencari informasi untuk mengenali kebutuhan dari pemangku kepentingan.',
                'tone' => 'neutral',
                'category' => 'neutral',
            ],
            [
                'interpretable_type' => 'aspect',
                'interpretable_name' => 'Pelayanan Publik',
                'rating_value' => 4,
                'template_text' => 'Kompeten dalam menampilkan kompetensi pelayanan publik. Individu mampu menunjukkan sikap yakin dan profesional dalam mengerjakan tugas pelayanan publik serta secara aktif mencari informasi untuk mengenali dan memenuhi kebutuhan pemangku kepentingan dengan baik.',
                'tone' => 'positive',
                'category' => 'strength',
            ],
        ];

        foreach ($templates as $template) {
            InterpretationTemplate::create([
                ...$template,
                'version' => 'v2.0',
                'is_active' => true,
            ]);
        }
    }

    protected function seedKompetensiPengembanganDiri(): void
    {
        $templates = [
            [
                'interpretable_type' => 'aspect',
                'interpretable_name' => 'Pengembangan Diri Dan Orang Lain',
                'rating_value' => 2,
                'template_text' => 'Kemampuan pengembangan diri dan orang lain masih perlu ditingkatkan. Individu belum optimal dalam mengembangkan kemampuan diri sendiri maupun membantu pengembangan kemampuan orang lain di lingkungan kerjanya.',
                'tone' => 'neutral',
                'category' => 'development_area',
            ],
            [
                'interpretable_type' => 'aspect',
                'interpretable_name' => 'Pengembangan Diri Dan Orang Lain',
                'rating_value' => 3,
                'template_text' => 'Cukup kompeten dalam menampilkan kompetensi pengembangan diri dan orang lain. Individu cukup mampu meningkatkan kemampuan diri dan cukup aktif dalam membantu orang lain untuk mengembangkan kemampuan mereka dalam menyelesaikan pekerjaan.',
                'tone' => 'neutral',
                'category' => 'neutral',
            ],
            [
                'interpretable_type' => 'aspect',
                'interpretable_name' => 'Pengembangan Diri Dan Orang Lain',
                'rating_value' => 4,
                'template_text' => 'Kompeten dalam menampilkan kompetensi pengembangan diri dan orang lain. Individu memiliki kompetensi dalam mengembangkan kemampuan diri dan orang lain yang memenuhi standar. Ia tidak hanya dapat meningkatkan kemampuan diri, namun juga meningkatkan kemampuan orang lain guna mengajarkan metode lain yang dapat memudahkan pekerjaan mereka.',
                'tone' => 'positive',
                'category' => 'strength',
            ],
        ];

        foreach ($templates as $template) {
            InterpretationTemplate::create([
                ...$template,
                'version' => 'v2.0',
                'is_active' => true,
            ]);
        }
    }

    protected function seedKompetensiMengelolaPerubahan(): void
    {
        $templates = [
            [
                'interpretable_type' => 'aspect',
                'interpretable_name' => 'Mengelola Perubahan',
                'rating_value' => 2,
                'template_text' => 'Kemampuan mengelola perubahan masih perlu ditingkatkan. Individu belum optimal dalam beradaptasi dengan perubahan di lingkungan kerja dan masih memerlukan waktu yang cukup lama untuk menerima perubahan yang terjadi.',
                'tone' => 'neutral',
                'category' => 'development_area',
            ],
            [
                'interpretable_type' => 'aspect',
                'interpretable_name' => 'Mengelola Perubahan',
                'rating_value' => 3,
                'template_text' => 'Cukup kompeten dalam menampilkan kompetensi mengelola perubahan. Individu cukup mampu beradaptasi mengikuti perubahan yang ada di lingkungan kerja dan cukup tanggap dalam menerima perubahan yang terjadi.',
                'tone' => 'neutral',
                'category' => 'neutral',
            ],
            [
                'interpretable_type' => 'aspect',
                'interpretable_name' => 'Mengelola Perubahan',
                'rating_value' => 4,
                'template_text' => 'Kompeten menampilkan kompetensi mengelola perubahan sesuai tuntutan dalam level jabatan fungsional pertama. Individu mampu proaktif dalam beradaptasi mengikuti perubahan yang ada di lingkungan kerja. Ia juga cepat dan tanggap dalam menerima perubahan.',
                'tone' => 'positive',
                'category' => 'strength',
            ],
        ];

        foreach ($templates as $template) {
            InterpretationTemplate::create([
                ...$template,
                'version' => 'v2.0',
                'is_active' => true,
            ]);
        }
    }

    protected function seedKompetensiPengambilanKeputusan(): void
    {
        $templates = [
            [
                'interpretable_type' => 'aspect',
                'interpretable_name' => 'Pengambilan Keputusan',
                'rating_value' => 2,
                'template_text' => 'Kemampuan pengambilan keputusan masih perlu ditingkatkan. Individu belum optimal dalam menganalisa masalah secara mendalam dan membuat keputusan operasional berdasarkan kesimpulan dari berbagai sumber informasi.',
                'tone' => 'neutral',
                'category' => 'development_area',
            ],
            [
                'interpretable_type' => 'aspect',
                'interpretable_name' => 'Pengambilan Keputusan',
                'rating_value' => 3,
                'template_text' => 'Cukup kompeten dalam menampilkan kompetensi pengambilan keputusan. Dalam level ini individu cukup mampu menampilkan perilaku untuk menganalisa suatu masalah secara mendalam serta membuat keputusan operasional berdasarkan kesimpulan dari berbagai sumber yang diterimanya.',
                'tone' => 'neutral',
                'category' => 'neutral',
            ],
            [
                'interpretable_type' => 'aspect',
                'interpretable_name' => 'Pengambilan Keputusan',
                'rating_value' => 4,
                'template_text' => 'Kompeten dalam menampilkan kompetensi pengambilan keputusan. Individu mampu menganalisa masalah secara mendalam dengan baik serta membuat keputusan operasional yang tepat berdasarkan kesimpulan dari berbagai sumber informasi yang komprehensif.',
                'tone' => 'positive',
                'category' => 'strength',
            ],
        ];

        foreach ($templates as $template) {
            InterpretationTemplate::create([
                ...$template,
                'version' => 'v2.0',
                'is_active' => true,
            ]);
        }
    }

    protected function seedKompetensiPerekatBangsa(): void
    {
        $templates = [
            [
                'interpretable_type' => 'aspect',
                'interpretable_name' => 'Perekat Bangsa',
                'rating_value' => 2,
                'template_text' => 'Kemampuan sebagai perekat bangsa masih perlu ditingkatkan. Individu belum konsisten dalam mengembangkan sikap saling menghargai dan menekankan persamaan serta persatuan, dan masih perlu mengembangkan kemampuan dalam menghadapi konflik yang melibatkan perbedaan suku, ras, dan agama.',
                'tone' => 'neutral',
                'category' => 'development_area',
            ],
            [
                'interpretable_type' => 'aspect',
                'interpretable_name' => 'Perekat Bangsa',
                'rating_value' => 3,
                'template_text' => 'Cukup mampu menampilkan kompetensi perekat bangsa sesuai dengan standar yang ditetapkan. Individu cukup aktif mengembangkan sikap saling menghargai dan menekankan persamaan dan persatuan. Kemampuan ini memungkinkannya untuk cukup mampu bersikap tenang apabila menghadapi konflik yang melibatkan perbedaan suku, ras, dan agama.',
                'tone' => 'neutral',
                'category' => 'neutral',
            ],
            [
                'interpretable_type' => 'aspect',
                'interpretable_name' => 'Perekat Bangsa',
                'rating_value' => 4,
                'template_text' => 'Kemampuan dalam mempromosikan sikap toleransi mampu ditampilkan individu sesuai dengan standar level yang ditetapkan. Mampu secara aktif mengembangkan sikap saling menghargai dan menekankan persamaan dan persatuan. Kemampuan ini memungkinkannya untuk mampu bersikap tenang apabila menghadapi konflik yang melibatkan perbedaan suku, ras, dan agama.',
                'tone' => 'positive',
                'category' => 'strength',
            ],
        ];

        foreach ($templates as $template) {
            InterpretationTemplate::create([
                ...$template,
                'version' => 'v2.0',
                'is_active' => true,
            ]);
        }
    }

    protected function seedGenericFallbacks(): void
    {
        // Generic fallback untuk POTENSI sub-aspects (rating 1-5)
        // Placeholder [nama aspek] akan diganti dengan nama sub-aspect yang sebenarnya
        $genericSubAspects = [
            [
                'interpretable_type' => 'sub_aspect',
                'interpretable_name' => null,
                'rating_value' => 1,
                'template_text' => 'Kemampuan dalam [nama aspek] masih sangat terbatas dan memerlukan pengembangan intensif. Diperlukan pelatihan dan bimbingan khusus untuk meningkatkan kapasitas di area ini agar dapat memenuhi tuntutan peran yang diharapkan.',
                'tone' => 'negative',
                'category' => 'development_area',
            ],
            [
                'interpretable_type' => 'sub_aspect',
                'interpretable_name' => null,
                'rating_value' => 2,
                'template_text' => 'Kemampuan dalam [nama aspek] masih perlu pengembangan lebih lanjut. Individu menunjukkan beberapa keterbatasan yang memerlukan upaya konsisten untuk meningkatkan kapasitas sesuai dengan standar yang ditetapkan.',
                'tone' => 'neutral',
                'category' => 'development_area',
            ],
            [
                'interpretable_type' => 'sub_aspect',
                'interpretable_name' => null,
                'rating_value' => 3,
                'template_text' => 'Kemampuan dalam [nama aspek] berada pada tingkat yang cukup memadai. Individu mampu menampilkan kapasitas sesuai dengan standar dasar yang diharapkan, meskipun masih ada ruang untuk peningkatan lebih lanjut.',
                'tone' => 'neutral',
                'category' => 'neutral',
            ],
            [
                'interpretable_type' => 'sub_aspect',
                'interpretable_name' => null,
                'rating_value' => 4,
                'template_text' => 'Kemampuan dalam [nama aspek] tergolong baik dan dapat diandalkan. Individu secara konsisten menampilkan kapasitas yang memenuhi bahkan kadang melampaui standar yang ditetapkan, sehingga dapat menjadi kekuatan dalam pelaksanaan tugas.',
                'tone' => 'positive',
                'category' => 'strength',
            ],
            [
                'interpretable_type' => 'sub_aspect',
                'interpretable_name' => null,
                'rating_value' => 5,
                'template_text' => 'Kemampuan dalam [nama aspek] sangat menonjol dan menjadi kekuatan utama individu. Kapasitas yang ditampilkan secara konsisten jauh melampaui standar yang ditetapkan dan dapat menjadi role model bagi orang lain dalam organisasi.',
                'tone' => 'positive',
                'category' => 'strength',
            ],
        ];

        // Generic fallback untuk KOMPETENSI aspects (rating 1-5)
        // Placeholder [nama aspek] akan diganti dengan nama aspect yang sebenarnya
        $genericAspects = [
            [
                'interpretable_type' => 'aspect',
                'interpretable_name' => null,
                'rating_value' => 1,
                'template_text' => 'Kemampuan menampilkan kompetensi [nama aspek] masih sangat terbatas dan memerlukan pengembangan yang serius. Individu belum mampu menampilkan perilaku kerja yang sesuai dengan standar yang ditetapkan. Diperlukan program pengembangan intensif untuk dapat memenuhi ekspektasi peran.',
                'tone' => 'negative',
                'category' => 'development_area',
            ],
            [
                'interpretable_type' => 'aspect',
                'interpretable_name' => null,
                'rating_value' => 2,
                'template_text' => 'Kemampuan menampilkan kompetensi [nama aspek] masih perlu ditingkatkan. Individu menunjukkan beberapa keterbatasan dalam menampilkan perilaku kerja yang diharapkan. Diperlukan upaya pengembangan yang konsisten untuk dapat memenuhi standar kompetensi yang ditetapkan.',
                'tone' => 'neutral',
                'category' => 'development_area',
            ],
            [
                'interpretable_type' => 'aspect',
                'interpretable_name' => null,
                'rating_value' => 3,
                'template_text' => 'Kemampuan dalam [nama aspek] cukup memadai dan sesuai dengan ekspektasi umum. Individu mampu menampilkan perilaku kerja sesuai dengan standar dasar yang ditetapkan, meskipun masih ada peluang untuk pengembangan lebih lanjut.',
                'tone' => 'neutral',
                'category' => 'neutral',
            ],
            [
                'interpretable_type' => 'aspect',
                'interpretable_name' => null,
                'rating_value' => 4,
                'template_text' => 'Kemampuan dalam [nama aspek] tergolong baik dan dapat diandalkan. Individu secara konsisten menampilkan perilaku kerja yang memenuhi bahkan kadang melampaui standar yang ditetapkan, sehingga dapat memberikan kontribusi positif terhadap pencapaian tujuan organisasi.',
                'tone' => 'positive',
                'category' => 'strength',
            ],
            [
                'interpretable_type' => 'aspect',
                'interpretable_name' => null,
                'rating_value' => 5,
                'template_text' => 'Kemampuan dalam [nama aspek] sangat menonjol dan menjadi keunggulan utama individu. Perilaku kerja yang ditampilkan secara konsisten jauh melampaui standar yang ditetapkan dan dapat menjadi contoh terbaik bagi rekan kerja lainnya.',
                'tone' => 'positive',
                'category' => 'strength',
            ],
        ];

        // Insert sub_aspect fallbacks
        foreach ($genericSubAspects as $template) {
            InterpretationTemplate::create([
                ...$template,
                'version' => 'v2.0',
                'is_active' => true,
            ]);
        }

        // Insert aspect fallbacks
        foreach ($genericAspects as $template) {
            InterpretationTemplate::create([
                ...$template,
                'version' => 'v2.0',
                'is_active' => true,
            ]);
        }
    }
}
