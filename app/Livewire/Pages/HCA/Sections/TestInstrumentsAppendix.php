<?php

declare(strict_types=1);

namespace App\Livewire\Pages\HCA\Sections;

use App\Models\Participant;
use App\Services\TestReportService;
use Illuminate\View\View;
use Livewire\Attributes\Reactive;
use Livewire\Component;

class TestInstrumentsAppendix extends Component
{
    #[Reactive]
    public ?int $participantId = null;

    /**
     * Selected category for interactive tab filtering
     */
    public string $selectedCategory = 'all';

    /**
     * Map test codes to thematic categories
     */
    public array $categoryMap = [
        'A.1' => 'cognitive',
        'A.2' => 'cognitive',
        'A.5' => 'cognitive',
        'B.1' => 'personality',
        'B.2' => 'personality',
        'D.1' => 'personality',
        'G.1' => 'personality',
        'D.2' => 'work_attitude',
        'E.1' => 'clinical',
        'E.2' => 'clinical',
        'F.1' => 'emotional_interest',
        'H.1' => 'emotional_interest',
    ];

    /**
     * Category labels and icons
     */
    public array $categories = [
        'all' => ['label' => 'Semua Alat Tes', 'icon' => 'fa-layer-group'],
        'cognitive' => ['label' => 'Kognitif & Inteligensi', 'icon' => 'fa-brain'],
        'personality' => ['label' => 'Kepribadian & Karakter', 'icon' => 'fa-masks-theater'],
        'work_attitude' => ['label' => 'Sikap Kerja & Ketahanan', 'icon' => 'fa-bolt'],
        'clinical' => ['label' => 'Kesehatan Jiwa & Klinis', 'icon' => 'fa-heart-pulse'],
        'emotional_interest' => ['label' => 'EQ & Minat Kerja', 'icon' => 'fa-compass'],
    ];

    /**
     * Switch category filter
     */
    public function setCategory(string $category): void
    {
        if (array_key_exists($category, $this->categories)) {
            $this->selectedCategory = $category;
        }
    }

    /**
     * Get active participant model
     */
    public function getParticipantProperty(): ?Participant
    {
        if (! $this->participantId) {
            return Participant::with([
                'assessmentEvent.institution',
                'assessmentEvent.project',
                'positionFormation',
                'batch',
                'mmpi',
            ])->first();
        }

        return Participant::with([
            'assessmentEvent.institution',
            'assessmentEvent.project',
            'positionFormation',
            'batch',
            'mmpi',
        ])->find($this->participantId);
    }

    /**
     * Get all test reports for active participant
     */
    public function getTestReports(TestReportService $testReportService): array
    {
        $participant = $this->participant;
        if (! $participant || ! $participant->event_id) {
            return [];
        }

        return $testReportService->getParticipantAllTestReports($participant->id, $participant->event_id);
    }

    /**
     * Render component
     */
    public function render(TestReportService $testReportService): View
    {
        $participant = $this->participant;
        $allReports = $this->getTestReports($testReportService);

        // Calculate counts per category
        $categoryCounts = [
            'all' => count($allReports) + ($participant?->mmpi ? 1 : 0),
            'cognitive' => 0,
            'personality' => 0,
            'work_attitude' => 0,
            'clinical' => $participant?->mmpi ? 1 : 0,
            'emotional_interest' => 0,
        ];

        foreach ($allReports as $code => $report) {
            $cat = $this->categoryMap[$code] ?? 'personality';
            if (isset($categoryCounts[$cat])) {
                $categoryCounts[$cat]++;
            }
        }

        // Filter reports based on selected category
        $filteredReports = [];
        if ($this->selectedCategory === 'all') {
            $filteredReports = $allReports;
        } else {
            foreach ($allReports as $code => $report) {
                $cat = $this->categoryMap[$code] ?? 'personality';
                if ($cat === $this->selectedCategory) {
                    $filteredReports[$code] = $report;
                }
            }
        }

        $showMmpi = ($this->selectedCategory === 'all' || $this->selectedCategory === 'clinical') && $participant?->mmpi;

        return view('livewire.pages.h-c-a.sections.test-instruments-appendix', [
            'participant' => $participant,
            'testReports' => $filteredReports,
            'allReports' => $allReports,
            'categoryCounts' => $categoryCounts,
            'showMmpi' => $showMmpi,
            'mmpiMetadata' => $this->getMmpiScaleMetadata(),
        ]);
    }

    /**
     * Glosarium deskriptif skala MMPI untuk mempermudah pemahaman pengguna non-psikolog
     *
     * @return array<string, array{name: string, label: string, desc: string}>
     */
    public function getMmpiScaleMetadata(): array
    {
        return [
            // 1. Skala Validitas MMPI-2 Standar
            'VRIN' => ['name' => 'Variable Response Inconsistency', 'label' => 'Konsistensi Respon Acak', 'desc' => 'Mengukur konsistensi jawaban peserta untuk mendeteksi pengisian acak atau asal-asalan.'],
            'TRIN' => ['name' => 'True Response Inconsistency', 'label' => 'Pola Jawaban Searah (Ya/Tidak)', 'desc' => 'Mendeteksi pola menjawab "Benar" atau "Salah" terus-menerus tanpa membaca soal.'],
            'F' => ['name' => 'Infrequency (F)', 'label' => 'Ketidaklaziman Keluhan (Faking Bad)', 'desc' => 'Mendeteksi jawaban yang sangat jarang dipilih orang normal (indikasi melebih-lebihkan masalah).'],
            'Fb' => ['name' => 'Back F (Fb)', 'label' => 'Konsistensi Paruh Kedua Tes', 'desc' => 'Versi skala F untuk paruh akhir tes guna mengecek konsistensi stamina menjawab.'],
            'Fp' => ['name' => 'Infrequency-Psychopathology (Fp)', 'label' => 'Ketidaklaziman Gejala Ekstrem', 'desc' => 'Keluhan ekstrem yang jarang dilaporkan pasien klinis untuk deteksi kepura-puraan akurat.'],
            'Fs' => ['name' => 'Infrequency-Somatic (Fs)', 'label' => 'Ketidaklaziman Somatik', 'desc' => 'Keluhan fisik berlebihan yang jarang dilaporkan pada pasien medis umum.'],
            'FBS' => ['name' => 'Fake Bad Scale / Symptom Validity', 'label' => 'Validitas Gejala Fisik/Kognitif', 'desc' => 'Mendeteksi potensi kecenderungan melebih-lebihkan hambatan fungsional kerja.'],
            'L' => ['name' => 'Lie Scale (L)', 'label' => 'Skala Kejujuran / Citra Diri Positif', 'desc' => 'Kecenderungan menutupi kekurangan diri secara naif agar terlihat sempurna di mata penilai.'],
            'K' => ['name' => 'Correction Scale (K)', 'label' => 'Sikap Defensif Halus', 'desc' => 'Sikap defensif atau menutup diri secara intelektual/halus dalam mengakui kelemahan.'],
            'S' => ['name' => 'Superlative Presentation (S)', 'label' => 'Presentasi Diri Berlebihan', 'desc' => 'Klaim penyesuaian moral dan ketenangan diri yang terlalu sempurna.'],
            'RBS' => ['name' => 'Response Bias Scale', 'label' => 'Bias Pelaporan Keluhan', 'desc' => 'Mendeteksi bias pelaporan keluhan kognitif/memori yang tidak wajar.'],
            'DS' => ['name' => 'Dissimulation', 'label' => 'Kecenderungan Kepura-puraan', 'desc' => 'Indikasi penyamaran respon atau melebih-lebihkan distres psikologis.'],
            'Wsd' => ['name' => 'Wiggins Social Desirability', 'label' => 'Keinginan Sosial Positif', 'desc' => 'Kecenderungan menjawab sesuai apa yang dianggap patut oleh lingkungan sosial.'],
            'Od' => ['name' => 'Other Deception', 'label' => 'Penyamaran Sikap Diri', 'desc' => 'Indikasi upaya menyamarkan profil perilaku sebenarnya.'],
            'Mp' => ['name' => 'Positive Malingering', 'label' => 'Klaim Kebajikan Berlebih', 'desc' => 'Kecenderungan menampilkan diri sangat ideal dan bebas masalah.'],

            // 2. Skala Validitas Gaya MMPI-2-RF
            'VRIN-r' => ['name' => 'VRIN-Revised', 'label' => 'Konsistensi Respon Acak', 'desc' => 'Mengukur konsistensi jawaban peserta untuk mendeteksi pengisian acak.'],
            'TRIN-r' => ['name' => 'TRIN-Revised', 'label' => 'Konsistensi Pola Jawaban', 'desc' => 'Mendeteksi kecenderungan menjawab searah tanpa pertimbangan isi butir soal.'],
            'F-r' => ['name' => 'Infrequency-Revised', 'label' => 'Respon Tidak Lazim', 'desc' => 'Mendeteksi pola keluhan yang sangat jarang terjadi pada populasi umum.'],
            'Fp-r' => ['name' => 'Fp-Revised', 'label' => 'Keluhan Klinis Tidak Lazim', 'desc' => 'Deteksi tajam terhadap upaya melebih-lebihkan gejala psikopatologi berat.'],
            'FBS-r' => ['name' => 'Symptom Validity-r', 'label' => 'Validitas Gejala Kerja', 'desc' => 'Memverifikasi keaslian keluhan fisik dan kognitif terkait kapasitas kerja.'],
            'L-r' => ['name' => 'Uncommon Virtues', 'label' => 'Klaim Kebajikan Langka', 'desc' => 'Kecenderungan mengklaim sifat moral sempurna untuk menutupi kekurangan diri.'],
            'K-r' => ['name' => 'Adjustment Validity', 'label' => 'Klaim Penyesuaian Berlebih', 'desc' => 'Kecenderungan defensif dalam mengakui kendala adaptasi psikologis.'],

            // 3. Skala Klinis Inti (1–10)
            'Hs' => ['name' => 'Hs (1) - Hypochondriasis', 'label' => 'Fokus Keluhan Fisik & Somatisasi', 'desc' => 'Preokupasi terhadap kondisi fisik tubuh dan kecenderungan mengeluh sakit saat menghadapi beban stres.'],
            'D' => ['name' => 'D (2) - Depression', 'label' => 'Kestabilan Suasana Hati & Optimisme', 'desc' => 'Mengukur tingkat vitalitas, optimisme kerja, dan daya lentur psikologis saat menghadapi kegagalan.'],
            'Hy' => ['name' => 'Hy (3) - Hysteria', 'label' => 'Kesadaran Kerentanan & Respon Tekanan', 'desc' => 'Kapasitas mengatasi tekanan sosial tanpa beralih ke keluhan somatik atau defensif emosional.'],
            'Pd' => ['name' => 'Pd (4) - Psychopathic Deviate', 'label' => 'Kepatuhan Aturan & Sikap Otoritas', 'desc' => 'Ketaatan terhadap regulasi/norma kerja, loyalitas organisasi, dan manajemen konflik dengan pimpinan.'],
            'Mf' => ['name' => 'Mf (5) - Masculinity-Femininity', 'label' => 'Fleksibilitas Minat & Gaya Interaksi', 'desc' => 'Keseimbangan pola minat, sensitivitas interpersonal, dan fleksibilitas gaya kerja dalam tim.'],
            'Pa' => ['name' => 'Pa (6) - Paranoia', 'label' => 'Kepercayaan Interpersonal & Kewaspadaan', 'desc' => 'Tingkat keterbukaan dan rasa percaya kepada rekan kerja vs kecurigaan atau sikap terlalu sensitif.'],
            'Pt' => ['name' => 'Pt (7) - Psychasthenia', 'label' => 'Ketahanan Stres, Cemas & Perfeksionisme', 'desc' => 'Kecenderungan khawatir, keraguan dalam mengambil keputusan, dan perfeksionisme saat tenggat waktu ketat.'],
            'Sc' => ['name' => 'Sc (8) - Schizophrenia', 'label' => 'Pola Pikir Realistis & Keterikatan Sosial', 'desc' => 'Pola pikir logis-praktis vs persepsi tidak biasa, serta kemampuan menjalin rasa keterikatan sosial di tim.'],
            'Ma' => ['name' => 'Ma (9) - Hypomania', 'label' => 'Energi Kerja, Inisiatif & Dorongan', 'desc' => 'Tingkat energi, inisiatif proaktif, dan antusiasme kerja vs impulsivitas dalam bertindak.'],
            'Si' => ['name' => 'Si (0) - Social Introversion', 'label' => 'Orientasi Kolaborasi Sosial / Mandiri', 'desc' => 'Preferensi kenyamanan dalam berinteraksi kolaboratif vs fokus bekerja secara mandiri.'],

            // 4. Skala Klinis RF (EID, THD, BXD, RC1..RC9)
            'EID' => ['name' => 'Emotional Dysfunction', 'label' => 'Disfungsi Emosional Internal', 'desc' => 'Tingkat distres suasana hati, kecemasan, dan ketidakstabilan afek kerja.'],
            'THD' => ['name' => 'Thought Dysfunction', 'label' => 'Disfungsi Pola Pikir', 'desc' => 'Kejernihan dan realisme alur berpikir dalam memecahkan masalah.'],
            'BXD' => ['name' => 'Behavioral Dysfunction', 'label' => 'Disfungsi Kontrol Perilaku', 'desc' => 'Kapasitas kendali diri terhadap dorongan impulsif dan kepatuhan norma organisasi.'],
            'RC1' => ['name' => 'RC1 - Somatic Complaints', 'label' => 'Keluhan Somatik Fisik', 'desc' => 'Fokus terhadap keluhan tubuh dan kelelahan fisik terkait stres kerja.'],
            'RC2' => ['name' => 'RC2 - Low Positive Emotions', 'label' => 'Rendahnya Afek Positif', 'desc' => 'Indikator berkurangnya antusiasme dan kegairahan dalam menjalankan tugas operasional.'],
            'RC3' => ['name' => 'RC3 - Cynicism', 'label' => 'Sinisme Interpersonal', 'desc' => 'Pandangan negatif atau kecurigaan terhadap niat rekan sejawat dan atasan.'],
            'RC4' => ['name' => 'RC4 - Antisocial Behavior', 'label' => 'Perilaku Non-Konformis', 'desc' => 'Kecenderungan menentang prosedur operasional baku atau instruksi kedinasan.'],
            'RC6' => ['name' => 'RC6 - Ideas of Persecution', 'label' => 'Sensitivitas Perlakuan Tidak Adil', 'desc' => 'Kecenderungan merasa disudutkan atau diperlakukan tidak adil oleh lingkungan kerja.'],
            'RC7' => ['name' => 'RC7 - Negative Emotions', 'label' => 'Kecemasan & Afek Negatif', 'desc' => 'Kerentanan terhadap rasa cemas, rasa bersalah, dan ketakutan membuat kekeliruan tugas.'],
            'RC8' => ['name' => 'RC8 - Aberrant Experiences', 'label' => 'Persepsi Tidak Biasa', 'desc' => 'Pola interpretasi situasi yang tidak konvensional atau tidak selaras dengan fakta.'],
            'RC9' => ['name' => 'RC9 - Hypomanic Activation', 'label' => 'Aktivasi Energi & Impuls', 'desc' => 'Tingkat gairah inisiatif kerja dan dorongan untuk bergerak cepat.'],

            // 5. Skala Konten Kerja & Karakter (ANX...WRK)
            'ANX' => ['name' => 'Anxiety (ANX)', 'label' => 'Kecemasan Kerja Umum', 'desc' => 'Tingkat ketegangan dan kekhawatiran umum saat menghadapi tantangan tugas baru.'],
            'FRS' => ['name' => 'Fears (FRS)', 'label' => 'Ketakutan Situasional', 'desc' => 'Kekhawatiran terhadap situasi tertentu yang dapat membatasi mobilitas tugas.'],
            'OBS' => ['name' => 'Obsessiveness (OBS)', 'label' => 'Keraguan & Kehati-hatian Berlebih', 'desc' => 'Kecenderungan over-thinking dan sulit mengambil keputusan cepat karena takut salah.'],
            'DEP' => ['name' => 'Depression (DEP)', 'label' => 'Pikiran Depresif & Keputusasaan', 'desc' => 'Tingkat penurunan semangat, pesimisme, atau rasa tidak berdaya saat tertekan.'],
            'HEA' => ['name' => 'Health Concerns (HEA)', 'label' => 'Kekhawatiran Kesehatan', 'desc' => 'Fokus berlebih pada kendala stamina dan kesehatan tubuh.'],
            'BIZ' => ['name' => 'Bizarre Mentation (BIZ)', 'label' => 'Keunikan Pola Pikir', 'desc' => 'Tingkat keanehan alur logika berpikir dalam memahami instruksi kerja.'],
            'ANG' => ['name' => 'Anger (ANG)', 'label' => 'Pengelolaan Kemarahan & Frustrasi', 'desc' => 'Toleransi terhadap frustrasi dan pengendalian emosi saat menghadapi situasi menjengkelkan.'],
            'CYN' => ['name' => 'Cynicism (CYN)', 'label' => 'Sikap Sinis & Kepercayaan Tim', 'desc' => 'Tingkat kepercayaan terhadap ketulusan motif orang lain dalam kerja sama tim.'],
            'ASP' => ['name' => 'Antisocial Practices (ASP)', 'label' => 'Integritas & Ketaatan Aturan', 'desc' => 'Komitmen mematuhi etika profesi dan keengganan mengambil jalan pintas ilegal.'],
            'TPA' => ['name' => 'Type A (TPA)', 'label' => 'Gaya Kerja Tipe-A (Kompetitif)', 'desc' => 'Dorongan kuat untuk mencapai target, orientasi kecepatan, dan ambisi berprestasi.'],
            'LSE' => ['name' => 'Low Self-Esteem (LSE)', 'label' => 'Kepercayaan Diri Rendah', 'desc' => 'Keraguan terhadap kapabilitas diri dan ketergantungan pada pengakuan orang lain.'],
            'SOD' => ['name' => 'Social Discomfort (SOD)', 'label' => 'Kecanggungan Sosial', 'desc' => 'Ketidaknyamanan atau keengganan berinteraksi dalam kelompok sosial baru.'],
            'FAM' => ['name' => 'Family Problems (FAM)', 'label' => 'Harmonisasi Domestik/Keluarga', 'desc' => 'Tingkat kestabilan dan dukungan lingkungan keluarga terhadap kelancaran penugasan.'],
            'WRK' => ['name' => 'Work Interference (WRK)', 'label' => 'Hambatan Produktivitas Kerja', 'desc' => 'Indikator adanya sikap negatif, kelelahan mental, atau hambatan psikologis yang mengganggu kinerja.'],
            'TRT' => ['name' => 'Treatment Indicators (TRT)', 'label' => 'Keterbukaan Bimbingan & Coaching', 'desc' => 'Kesiapan kandidat menerima masukan, coaching, atau bantuan pengembangan diri.'],

            // 6. Skala Suplementer & Daya Tahan (A, R, Es, Do, Re, PK, MAC-R)
            'A' => ['name' => 'Welsh Anxiety (A)', 'label' => 'Tingkat Distres Situasional', 'desc' => 'Indeks ketegangan emosional dan stres kerja yang sedang dirasakan saat asesmen.'],
            'R' => ['name' => 'Repression (R)', 'label' => 'Pengendalian & Penahanan Diri', 'desc' => 'Kecenderungan menahan emosi secara terkendali dan menghindari friksi terbuka.'],
            'Es' => ['name' => 'Ego Strength (Es)', 'label' => 'Kekuatan Mental (Resilience)', 'desc' => 'Kapasitas daya tahan psikologis (*grit*), kestabilan diri, dan efektivitas adaptasi di bawah tekanan.'],
            'Do' => ['name' => 'Dominance (Do)', 'label' => 'Kepemimpinan & Ketegasan Diri', 'desc' => 'Keberanian memimpin, percaya diri mengambil inisiatif, dan asertivitas dalam tim.'],
            'Re' => ['name' => 'Social Responsibility (Re)', 'label' => 'Tanggung Jawab Sosial & Integritas', 'desc' => 'Tingkat akuntabilitas moral, dedikasi pada tugas kedinasan, dan nilai-nilai etika luhur.'],
            'Mt' => ['name' => 'Maladjustment (Mt)', 'label' => 'Penyesuaian Diri Lingkungan', 'desc' => 'Tingkat kesulitan dalam menyesuaikan diri dengan tuntutan lingkungan baru.'],
            'PK' => ['name' => 'Post-Traumatic Stress (PK)', 'label' => 'Indikator Beban Trauma / Distres Berat', 'desc' => 'Tingkat kerentanan terhadap tekanan emosional mendalam akibat pengalaman berat masa lalu.'],
            'MDS' => ['name' => 'Marital Distress (MDS)', 'label' => 'Stabilitas Hubungan Personal', 'desc' => 'Tingkat keharmonisan relasi personal yang mempengaruhi fokus kerja.'],
            'Ho' => ['name' => 'Hostility (Ho)', 'label' => 'Resentmen & Rasa Permusuhan', 'desc' => 'Tingkat kejengkelan terpendam yang berisiko memicu konflik pasif-agresif.'],
            'OH' => ['name' => 'Overcontrolled Hostility (OH)', 'label' => 'Kemarahan Terpendam', 'desc' => 'Kecenderungan menekan kemarahan secara berlebih hingga berisiko meledak situasional.'],
            'MAC-R' => ['name' => 'MacAndrew Alcoholism-R', 'label' => 'Kerentanan Impulsivitas & Adiksi', 'desc' => 'Kecenderungan mencari sensasi berisiko (*sensation seeking*) dan kontrol impuls.'],
            'AAS' => ['name' => 'Addiction Admission (AAS)', 'label' => 'Pengakuan Kerentanan Kebiasaan', 'desc' => 'Keterbukaan terhadap kendala kontrol kebiasaan adiktif.'],
            'APS' => ['name' => 'Addiction Potential (APS)', 'label' => 'Potensi Kerentanan Ketergantungan', 'desc' => 'Faktor kepribadian yang berkorelasi dengan kerentanan gaya hidup tidak sehat.'],
            'GM' => ['name' => 'Masculine Role (GM)', 'label' => 'Peran Maskulin', 'desc' => 'Orientasi peran kerja berbasis ketegasan tugas.'],
            'GF' => ['name' => 'Feminine Role (GF)', 'label' => 'Peran Feminin', 'desc' => 'Orientasi peran kerja berbasis empati dan kepedulian.'],
        ];
    }
}
