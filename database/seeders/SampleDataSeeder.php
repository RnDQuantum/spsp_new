<?php

namespace Database\Seeders;

use App\Models\Aspect;
use App\Models\AspectAssessment;
use App\Models\AssessmentEvent;
use App\Models\AssessmentTemplate;
use App\Models\Batch;
use App\Models\CategoryAssessment;
use App\Models\CategoryType;
use App\Models\FinalAssessment;
use App\Models\Institution;
use App\Models\Interpretation;
use App\Models\Participant;
use App\Models\PositionFormation;
use App\Models\PsychologicalTest;
use App\Models\SubAspect;
use App\Models\SubAspectAssessment;
use Illuminate\Database\Seeder;

class SampleDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // ==========================================
        // 1. GET MASTER DATA
        // ==========================================

        // Institution: Kejaksaan Agung RI (from PDF sample)
        $kejaksaan = Institution::where('code', 'kejaksaan')->first();

        // Template: P3K Standard 2025
        $templateP3K = AssessmentTemplate::where('code', 'p3k_standard_2025')->first();

        // Categories: Potensi & Kompetensi
        $categoryPotensi = CategoryType::where('code', 'potensi')->first();
        $categoryKompetensi = CategoryType::where('code', 'kompetensi')->first();

        // ==========================================
        // 2. CREATE EVENT
        // ==========================================

        $event = AssessmentEvent::create([
            'institution_id' => $kejaksaan->id,
            'template_id' => $templateP3K->id,
            'code' => 'P3K-KEJAKSAAN-2025',
            'name' => 'Asesmen P3K Kejaksaan Agung RI 2025',
            'description' => 'Pelaksanaan asesmen kompetensi untuk calon pegawai P3K Kejaksaan Agung RI tahun 2025. Asesmen dilakukan di 3 lokasi berbeda dengan total 150 peserta dari berbagai formasi jabatan.',
            'year' => 2025,
            'start_date' => '2025-09-01',
            'end_date' => '2025-12-31',
            'status' => 'completed',
        ]);

        // ==========================================
        // 3. CREATE BATCHES (Multiple Gelombang/Lokasi)
        // ==========================================

        $batches = [
            [
                'code' => 'BATCH-1-MOJOKERTO',
                'name' => 'Gelombang 1 - Mojokerto',
                'location' => 'Mojokerto',
                'batch_number' => 1,
                'start_date' => '2025-09-27',
                'end_date' => '2025-09-28',
            ],
            [
                'code' => 'BATCH-2-SURABAYA',
                'name' => 'Gelombang 2 - Surabaya',
                'location' => 'Surabaya',
                'batch_number' => 2,
                'start_date' => '2025-10-15',
                'end_date' => '2025-10-16',
            ],
            [
                'code' => 'BATCH-3-JAKARTA',
                'name' => 'Gelombang 3 - Jakarta',
                'location' => 'Jakarta Pusat',
                'batch_number' => 3,
                'start_date' => '2025-11-05',
                'end_date' => '2025-11-06',
            ],
        ];

        $createdBatches = [];
        foreach ($batches as $batchData) {
            $createdBatches[] = Batch::create([
                'event_id' => $event->id,
                ...$batchData,
            ]);
        }
        $batch = $createdBatches[0]; // Default batch for first participant

        // ==========================================
        // 4. CREATE POSITION FORMATIONS (Multiple Formasi Jabatan)
        // ==========================================

        $positions = [
            [
                'code' => 'fisikawan_medis',
                'name' => 'Fisikawan Medis Ahli Pertama',
                'quota' => 10,
            ],
            [
                'code' => 'analis_kebijakan',
                'name' => 'Analis Kebijakan Ahli Pertama',
                'quota' => 15,
            ],
            [
                'code' => 'auditor',
                'name' => 'Auditor Ahli Pertama',
                'quota' => 8,
            ],
            [
                'code' => 'pranata_komputer',
                'name' => 'Pranata Komputer Ahli Pertama',
                'quota' => 12,
            ],
            [
                'code' => 'pengelola_pengadaan',
                'name' => 'Pengelola Pengadaan Barang dan Jasa',
                'quota' => 6,
            ],
        ];

        $createdPositions = [];
        foreach ($positions as $positionData) {
            $createdPositions[] = PositionFormation::create([
                'event_id' => $event->id,
                ...$positionData,
            ]);
        }
        $position = $createdPositions[0]; // Default position for first participant

        // ==========================================
        // 4. PARTICIPANT #1: EKA FEBRIYANI (from PDF)
        // ==========================================

        $participant = Participant::create([
            'event_id' => $event->id,
            'batch_id' => $batch->id,
            'position_formation_id' => $position->id,
            'test_number' => '03-5-2-18-001',
            'skb_number' => '24400240120012571',
            'name' => 'EKA FEBRIYANI, s.si',
            'email' => 'eka.febriyani@example.com',
            'phone' => '081234567890',
            'gender' => 'P',
            'photo_path' => null,
            'assessment_date' => '2025-09-27',
        ]);

        // --- 4.1. Category Assessment: POTENSI ---
        $catPotensi = CategoryAssessment::create([
            'participant_id' => $participant->id,
            'event_id' => $participant->event_id,
            'batch_id' => $participant->batch_id,
            'position_formation_id' => $participant->position_formation_id,
            'category_type_id' => $categoryPotensi->id,
            'total_standard_rating' => 11.94,
            'total_standard_score' => 300.21,
            'total_individual_rating' => 11.83,
            'total_individual_score' => 294.25,
            'gap_rating' => -0.11,
            'gap_score' => -5.97,
            'conclusion_code' => 'DBS',
            'conclusion_text' => 'DI BAWAH STANDARD',
        ]);

        // --- 4.2. Aspect Assessment: Kecerdasan ---
        $aspectKecerdasan = Aspect::where('code', 'kecerdasan')->first();
        $aspKecerdasan = AspectAssessment::create([
            'category_assessment_id' => $catPotensi->id,
            'participant_id' => $participant->id,
            'event_id' => $participant->event_id,
            'batch_id' => $participant->batch_id,
            'position_formation_id' => $participant->position_formation_id,
            'aspect_id' => $aspectKecerdasan->id,
            'standard_rating' => 3.15,
            'standard_score' => 94.50,
            'individual_rating' => 2.58,
            'individual_score' => 77.29,
            'gap_rating' => -0.57,
            'gap_score' => -17.21,
            'percentage_score' => round((2.58 / 5) * 100),
            'conclusion_text' => 'Kurang Memenuhi Standard',
            'description_text' => null,
        ]);

        // --- 4.3. Sub-Aspect Assessments for Kecerdasan ---
        // Generate sub-aspect assessments using helper method
        $this->generateSubAspectAssessments($aspKecerdasan, 0.96); // performance multiplier 96%

        // --- 4.4. Category Assessment: KOMPETENSI ---
        $catKompetensi = CategoryAssessment::create([
            'participant_id' => $participant->id,
            'event_id' => $participant->event_id,
            'batch_id' => $participant->batch_id,
            'position_formation_id' => $participant->position_formation_id,
            'category_type_id' => $categoryKompetensi->id,
            'total_standard_rating' => 24.30,
            'total_standard_score' => 270.00,
            'total_individual_rating' => 27.48,
            'total_individual_score' => 305.36,
            'gap_rating' => 3.18,
            'gap_score' => 35.36,
            'conclusion_code' => 'SK',
            'conclusion_text' => 'SANGAT KOMPETEN',
        ]);

        // --- 4.5. Aspect Assessment: Integritas (manual example) ---
        $aspectIntegritas = Aspect::where('code', 'integritas')->first();

        // For Kompetensi, individual_rating should be INTEGER 1-5
        $integritasIndividualRating = 3; // Integer rating

        AspectAssessment::create([
            'category_assessment_id' => $catKompetensi->id,
            'participant_id' => $participant->id,
            'event_id' => $participant->event_id,
            'batch_id' => $participant->batch_id,
            'position_formation_id' => $participant->position_formation_id,
            'aspect_id' => $aspectIntegritas->id,
            'standard_rating' => 2.70,
            'standard_score' => 32.40,
            'individual_rating' => $integritasIndividualRating,
            'individual_score' => $integritasIndividualRating * ($aspectIntegritas->weight_percentage / 100),
            'gap_rating' => $integritasIndividualRating - 2.70,
            'gap_score' => ($integritasIndividualRating * ($aspectIntegritas->weight_percentage / 100)) - 32.40,
            'percentage_score' => round(($integritasIndividualRating / 5) * 100),
            'conclusion_text' => 'Sangat Memenuhi Standard',
            'description_text' => 'Individu kompeten menampilkan kompetensi integritas sesuai dengan standar level yang di tetapkan.',
        ]);

        // --- 4.5b. Generate remaining aspect assessments for participant 1 ---
        // For Potensi: generate aspects OTHER than Kecerdasan (already created manually)
        $potensiAspects = Aspect::where('category_type_id', $categoryPotensi->id)
            ->where('code', '!=', 'kecerdasan')
            ->orderBy('order')
            ->get();

        foreach ($potensiAspects as $aspect) {
            $performanceMultiplier = 0.96; // Based on achievement 96.01%
            $individualRating = max(1.0, min(5.0, $aspect->standard_rating * $performanceMultiplier));
            $standardScore = $aspect->standard_rating * ($aspect->weight_percentage / 100);
            $individualScore = $individualRating * ($aspect->weight_percentage / 100);
            $gapRating = $individualRating - $aspect->standard_rating;
            $gapScore = $individualScore - $standardScore;

            $conclusionText = $gapRating < -0.5 ? 'Kurang Memenuhi Standard' : ($gapRating < 0.5 ? 'Memenuhi Standard' : 'Sangat Memenuhi Standard');

            $aspectAssessment = AspectAssessment::create([
                'category_assessment_id' => $catPotensi->id,
                'participant_id' => $participant->id,
                'event_id' => $participant->event_id,
                'batch_id' => $participant->batch_id,
                'position_formation_id' => $participant->position_formation_id,
                'aspect_id' => $aspect->id,
                'standard_rating' => $aspect->standard_rating,
                'standard_score' => round($standardScore, 2),
                'individual_rating' => round($individualRating, 2),
                'individual_score' => round($individualScore, 2),
                'gap_rating' => round($gapRating, 2),
                'gap_score' => round($gapScore, 2),
                'percentage_score' => round(($individualRating / 5) * 100),
                'conclusion_code' => null,
                'conclusion_text' => $conclusionText,
                'description_text' => $this->getAspectDescription($aspect->code, $conclusionText),
            ]);

            // Generate sub-aspect assessments (Sikap Kerja, Hubungan Sosial, Kepribadian)
            $this->generateSubAspectAssessments($aspectAssessment, $performanceMultiplier);
        }

        // For Kompetensi: generate aspects OTHER than Integritas (already created manually)
        $kompetensiAspects = Aspect::where('category_type_id', $categoryKompetensi->id)
            ->where('code', '!=', 'integritas')
            ->orderBy('order')
            ->get();

        foreach ($kompetensiAspects as $aspect) {
            $performanceMultiplier = 1.02; // Slightly above standard for Kompetensi

            // For Kompetensi, individual_rating should be INTEGER 1-5
            $baseRating = $aspect->standard_rating * $performanceMultiplier;
            $individualRating = (int) max(1, min(5, round($baseRating)));

            $standardScore = $aspect->standard_rating * ($aspect->weight_percentage / 100);
            $individualScore = $individualRating * ($aspect->weight_percentage / 100);
            $gapRating = $individualRating - $aspect->standard_rating;
            $gapScore = $individualScore - $standardScore;

            $conclusionText = $gapRating < -0.5 ? 'Kurang Memenuhi Standard' : ($gapRating < 0.5 ? 'Memenuhi Standard' : 'Sangat Memenuhi Standard');

            AspectAssessment::create([
                'category_assessment_id' => $catKompetensi->id,
                'participant_id' => $participant->id,
                'event_id' => $participant->event_id,
                'batch_id' => $participant->batch_id,
                'position_formation_id' => $participant->position_formation_id,
                'aspect_id' => $aspect->id,
                'standard_rating' => $aspect->standard_rating,
                'standard_score' => round($standardScore, 2),
                'individual_rating' => $individualRating,
                'individual_score' => round($individualScore, 2),
                'gap_rating' => round($gapRating, 2),
                'gap_score' => round($gapScore, 2),
                'percentage_score' => round(($individualRating / 5) * 100),
                'conclusion_code' => null,
                'conclusion_text' => $conclusionText,
                'description_text' => $this->getAspectDescription($aspect->code, $conclusionText),
            ]);
        }

        // --- 4.6. Final Assessment ---
        FinalAssessment::create([
            'participant_id' => $participant->id,
            'event_id' => $participant->event_id,
            'batch_id' => $participant->batch_id,
            'position_formation_id' => $participant->position_formation_id,
            'potensi_weight' => 40,
            'potensi_standard_score' => 133.43,
            'potensi_individual_score' => 117.70,
            'kompetensi_weight' => 60,
            'kompetensi_standard_score' => 180.00,
            'kompetensi_individual_score' => 183.22,
            'total_standard_score' => 313.43,
            'total_individual_score' => 300.91,
            'achievement_percentage' => 96.01,
            'conclusion_code' => 'MMS',
            'conclusion_text' => 'MASIH MEMENUHI SYARAT (MMS)',
        ]);

        // --- 4.7. Psychological Test ---
        PsychologicalTest::create([
            'participant_id' => $participant->id,
            'event_id' => $participant->event_id,
            'raw_score' => 40.00,
            'iq_score' => 97,
            'validity_status' => 'Hasil tes ini konsisten, tetapi kurang akurat dan kurang dapat dipercaya',
            'internal_status' => 'Kurang terbuka',
            'interpersonal_status' => 'Kurang terbuka',
            'work_capacity_status' => 'Kurang terbuka',
            'clinical_status' => 'Kurang terbuka',
            'conclusion_code' => 'TMS',
            'conclusion_text' => 'TIDAK MEMENUHI SYARAT (TMS)',
            'notes' => 'Mungkin terdapat psikopatologi (gejala kejiwaan) yang disembunyikan.',
        ]);

        // --- 4.8. Interpretations ---
        Interpretation::create([
            'participant_id' => $participant->id,
            'event_id' => $participant->event_id,
            'category_type_id' => $categoryPotensi->id,
            'interpretation_text' => 'Memiliki kepekaan yang cukup memadai dalam memahami kebutuhan orang-orang yang ada di sekitarnya.',
        ]);

        Interpretation::create([
            'participant_id' => $participant->id,
            'event_id' => $participant->event_id,
            'category_type_id' => $categoryKompetensi->id,
            'interpretation_text' => 'Dalam bekerja, individu cukup mampu mengelola pekerjaan yang menjadi tanggung jawabnya sesuai dengan prioritas penyelesaian masalah.',
        ]);

        // ==========================================
        // 5. ADDITIONAL SAMPLE PARTICIPANTS (15 more - distributed across batches & positions)
        // ==========================================

        $this->createAdditionalParticipants($event, $createdBatches, $createdPositions, $categoryPotensi, $categoryKompetensi);
    }

    private function createAdditionalParticipants($event, $batches, $positions, $categoryPotensi, $categoryKompetensi): void
    {
        $sampleParticipants = [
            // Batch 1 - Mojokerto
            [
                'test_number' => '03-5-2-18-002',
                'skb_number' => '24400240120012572',
                'name' => 'BUDI SANTOSO, S.T',
                'email' => 'budi.santoso@example.com',
                'gender' => 'L',
                'batch_index' => 0,
                'position_index' => 0,
                'assessment_date' => '2025-09-27',
                'achievement' => 88.50,
            ],
            [
                'test_number' => '03-5-2-18-003',
                'skb_number' => '24400240120012573',
                'name' => 'CITRA DEWI, S.Kom',
                'email' => 'citra.dewi@example.com',
                'gender' => 'P',
                'batch_index' => 0,
                'position_index' => 1,
                'assessment_date' => '2025-09-27',
                'achievement' => 105.20,
            ],
            [
                'test_number' => '03-5-2-18-004',
                'skb_number' => '24400240120012574',
                'name' => 'DARMAWAN PUTRA, S.E',
                'email' => 'darmawan.putra@example.com',
                'gender' => 'L',
                'batch_index' => 0,
                'position_index' => 2,
                'assessment_date' => '2025-09-27',
                'achievement' => 92.15,
            ],
            [
                'test_number' => '03-5-2-18-005',
                'skb_number' => '24400240120012575',
                'name' => 'ERNI WULANDARI, S.Psi',
                'email' => 'erni.wulandari@example.com',
                'gender' => 'P',
                'batch_index' => 0,
                'position_index' => 3,
                'assessment_date' => '2025-09-28',
                'achievement' => 78.90,
            ],

            // Batch 2 - Surabaya
            [
                'test_number' => '03-5-2-19-001',
                'skb_number' => '24400240120012576',
                'name' => 'FAISAL RAHMAN, S.H',
                'email' => 'faisal.rahman@example.com',
                'gender' => 'L',
                'batch_index' => 1,
                'position_index' => 0,
                'assessment_date' => '2025-10-15',
                'achievement' => 110.50,
            ],
            [
                'test_number' => '03-5-2-19-002',
                'skb_number' => '24400240120012577',
                'name' => 'GITA PUSPITA, S.Ak',
                'email' => 'gita.puspita@example.com',
                'gender' => 'P',
                'batch_index' => 1,
                'position_index' => 1,
                'assessment_date' => '2025-10-15',
                'achievement' => 95.75,
            ],
            [
                'test_number' => '03-5-2-19-003',
                'skb_number' => '24400240120012578',
                'name' => 'HENDRA GUNAWAN, S.T',
                'email' => 'hendra.gunawan@example.com',
                'gender' => 'L',
                'batch_index' => 1,
                'position_index' => 2,
                'assessment_date' => '2025-10-15',
                'achievement' => 102.30,
            ],
            [
                'test_number' => '03-5-2-19-004',
                'skb_number' => '24400240120012579',
                'name' => 'INDAH SARI, S.Kom',
                'email' => 'indah.sari@example.com',
                'gender' => 'P',
                'batch_index' => 1,
                'position_index' => 3,
                'assessment_date' => '2025-10-16',
                'achievement' => 87.45,
            ],
            [
                'test_number' => '03-5-2-19-005',
                'skb_number' => '24400240120012580',
                'name' => 'JOKO WIDODO, S.E',
                'email' => 'joko.widodo@example.com',
                'gender' => 'L',
                'batch_index' => 1,
                'position_index' => 4,
                'assessment_date' => '2025-10-16',
                'achievement' => 98.60,
            ],

            // Batch 3 - Jakarta
            [
                'test_number' => '03-5-2-20-001',
                'skb_number' => '24400240120012581',
                'name' => 'KARTIKA PUTRI, S.Pd',
                'email' => 'kartika.putri@example.com',
                'gender' => 'P',
                'batch_index' => 2,
                'position_index' => 0,
                'assessment_date' => '2025-11-05',
                'achievement' => 83.20,
            ],
            [
                'test_number' => '03-5-2-20-002',
                'skb_number' => '24400240120012582',
                'name' => 'LUKMAN HAKIM, S.Si',
                'email' => 'lukman.hakim@example.com',
                'gender' => 'L',
                'batch_index' => 2,
                'position_index' => 1,
                'assessment_date' => '2025-11-05',
                'achievement' => 107.80,
            ],
            [
                'test_number' => '03-5-2-20-003',
                'skb_number' => '24400240120012583',
                'name' => 'MAYA ANGGRAINI, S.H',
                'email' => 'maya.anggraini@example.com',
                'gender' => 'P',
                'batch_index' => 2,
                'position_index' => 2,
                'assessment_date' => '2025-11-05',
                'achievement' => 91.40,
            ],
            [
                'test_number' => '03-5-2-20-004',
                'skb_number' => '24400240120012584',
                'name' => 'NANDA PRATAMA, S.Kom',
                'email' => 'nanda.pratama@example.com',
                'gender' => 'L',
                'batch_index' => 2,
                'position_index' => 3,
                'assessment_date' => '2025-11-06',
                'achievement' => 99.15,
            ],
            [
                'test_number' => '03-5-2-20-005',
                'skb_number' => '24400240120012585',
                'name' => 'OKTAVIA LESTARI, S.E',
                'email' => 'oktavia.lestari@example.com',
                'gender' => 'P',
                'batch_index' => 2,
                'position_index' => 4,
                'assessment_date' => '2025-11-06',
                'achievement' => 104.70,
            ],
            [
                'test_number' => '03-5-2-20-006',
                'skb_number' => '24400240120012586',
                'name' => 'PUTRA ANDIKA, S.T',
                'email' => 'putra.andika@example.com',
                'gender' => 'L',
                'batch_index' => 2,
                'position_index' => 0,
                'assessment_date' => '2025-11-06',
                'achievement' => 75.30,
            ],
        ];

        foreach ($sampleParticipants as $data) {
            $batch = $batches[$data['batch_index']];
            $position = $positions[$data['position_index']];

            $participant = Participant::create([
                'event_id' => $event->id,
                'batch_id' => $batch->id,
                'position_formation_id' => $position->id,
                'test_number' => $data['test_number'],
                'skb_number' => $data['skb_number'],
                'name' => $data['name'],
                'email' => $data['email'],
                'gender' => $data['gender'],
                'phone' => '081234567890',
                'photo_path' => null,
                'assessment_date' => $data['assessment_date'],
            ]);

            // Create basic assessments for additional participants
            $achievement = $data['achievement'];
            $isHigh = $achievement >= 100;
            $isMedium = $achievement >= 85 && $achievement < 100;
            $isLow = $achievement < 85;

            // Determine performance multiplier based on achievement
            $performanceMultiplier = $achievement / 100;

            // Create Potensi Category Assessment
            $catPotensi = CategoryAssessment::create([
                'participant_id' => $participant->id,
                'event_id' => $participant->event_id,
                'batch_id' => $participant->batch_id,
                'position_formation_id' => $participant->position_formation_id,
                'category_type_id' => $categoryPotensi->id,
                'total_standard_rating' => 12.50,
                'total_standard_score' => 310.00,
                'total_individual_rating' => $isHigh ? 13.50 : ($isMedium ? 12.80 : 11.20),
                'total_individual_score' => $isHigh ? 335.00 : ($isMedium ? 318.00 : 278.00),
                'gap_rating' => $isHigh ? 1.00 : ($isMedium ? 0.30 : -1.30),
                'gap_score' => $isHigh ? 25.00 : ($isMedium ? 8.00 : -32.00),
                'conclusion_code' => $isHigh ? 'SK' : ($isMedium ? 'MS' : 'DBS'),
                'conclusion_text' => $isHigh ? 'SANGAT KOMPETEN' : ($isMedium ? 'MEMENUHI STANDARD' : 'DI BAWAH STANDARD'),
            ]);

            // Generate all aspect assessments for Potensi
            $this->generateAspectAssessments($catPotensi, $categoryPotensi->id, $performanceMultiplier);

            // Create Kompetensi Category Assessment
            $catKompetensi = CategoryAssessment::create([
                'participant_id' => $participant->id,
                'event_id' => $participant->event_id,
                'batch_id' => $participant->batch_id,
                'position_formation_id' => $participant->position_formation_id,
                'category_type_id' => $categoryKompetensi->id,
                'total_standard_rating' => 25.00,
                'total_standard_score' => 280.00,
                'total_individual_rating' => $isHigh ? 27.50 : ($isMedium ? 26.00 : 23.50),
                'total_individual_score' => $isHigh ? 308.00 : ($isMedium ? 291.00 : 263.00),
                'gap_rating' => $isHigh ? 2.50 : ($isMedium ? 1.00 : -1.50),
                'gap_score' => $isHigh ? 28.00 : ($isMedium ? 11.00 : -17.00),
                'conclusion_code' => $isHigh ? 'SK' : ($isMedium ? 'K' : 'DBS'),
                'conclusion_text' => $isHigh ? 'SANGAT KOMPETEN' : ($isMedium ? 'KOMPETEN' : 'DI BAWAH STANDARD'),
            ]);

            // Generate all aspect assessments for Kompetensi
            $this->generateAspectAssessments($catKompetensi, $categoryKompetensi->id, $performanceMultiplier);

            $totalStandardScore = 313.00;
            $totalIndividualScore = ($achievement / 100) * $totalStandardScore;

            FinalAssessment::create([
                'participant_id' => $participant->id,
                'event_id' => $participant->event_id,
                'batch_id' => $participant->batch_id,
                'position_formation_id' => $participant->position_formation_id,
                'potensi_weight' => 40,
                'potensi_standard_score' => 124.00,
                'potensi_individual_score' => $isHigh ? 134.00 : ($isMedium ? 127.20 : 111.20),
                'kompetensi_weight' => 60,
                'kompetensi_standard_score' => 189.00,
                'kompetensi_individual_score' => $isHigh ? 184.80 : ($isMedium ? 174.60 : 158.40),
                'total_standard_score' => $totalStandardScore,
                'total_individual_score' => $totalIndividualScore,
                'achievement_percentage' => $achievement,
                'conclusion_code' => $isHigh ? 'MS' : ($isMedium ? 'MMS' : 'TMS'),
                'conclusion_text' => $isHigh ? 'MEMENUHI SYARAT (MS)' : ($isMedium ? 'MASIH MEMENUHI SYARAT (MMS)' : 'TIDAK MEMENUHI SYARAT (TMS)'),
            ]);

            PsychologicalTest::create([
                'participant_id' => $participant->id,
                'event_id' => $participant->event_id,
                'raw_score' => $isHigh ? 48.00 : ($isMedium ? 42.00 : 38.00),
                'iq_score' => $isHigh ? 110 : ($isMedium ? 100 : 90),
                'validity_status' => $isHigh ? 'Hasil tes ini konsisten dan dapat dipercaya' : ($isMedium ? 'Hasil tes ini cukup konsisten' : 'Hasil tes ini kurang konsisten'),
                'internal_status' => $isHigh ? 'Terbuka' : ($isMedium ? 'Cukup terbuka' : 'Kurang terbuka'),
                'interpersonal_status' => $isHigh ? 'Terbuka' : ($isMedium ? 'Cukup terbuka' : 'Kurang terbuka'),
                'work_capacity_status' => $isHigh ? 'Terbuka' : ($isMedium ? 'Cukup terbuka' : 'Kurang terbuka'),
                'clinical_status' => $isHigh ? 'Terbuka' : ($isMedium ? 'Cukup terbuka' : 'Kurang terbuka'),
                'conclusion_code' => $isHigh ? 'MS' : ($isMedium ? 'MS' : 'TMS'),
                'conclusion_text' => $isHigh ? 'MEMENUHI SYARAT (MS)' : ($isMedium ? 'MEMENUHI SYARAT (MS)' : 'TIDAK MEMENUHI SYARAT (TMS)'),
                'notes' => $isLow ? 'Perlu perhatian khusus pada aspek kejiwaan' : null,
            ]);

            // Interpretations - setiap peserta punya 2: Potensi & Kompetensi
            $interpretationPotensi = $isHigh
                ? 'Individu memiliki potensi yang sangat baik dengan kemampuan kognitif dan sikap kerja yang menonjol. Mampu beradaptasi dengan baik dalam berbagai situasi kerja.'
                : ($isMedium
                    ? 'Memiliki kepekaan yang cukup memadai dalam memahami kebutuhan orang-orang yang ada di sekitarnya. Menunjukkan potensi yang memadai untuk menjalankan tugas.'
                    : 'Memerlukan pengembangan lebih lanjut pada aspek potensi, terutama dalam hal kemampuan analisis dan pemecahan masalah.');

            $interpretationKompetensi = $isHigh
                ? 'Menunjukkan kompetensi yang sangat baik dalam semua aspek pekerjaan. Konsisten dalam menampilkan perilaku kerja yang sesuai dengan standar organisasi dan mampu menjadi role model bagi rekan kerja.'
                : ($isMedium
                    ? 'Dalam bekerja, individu cukup mampu mengelola pekerjaan yang menjadi tanggung jawabnya sesuai dengan prioritas penyelesaian masalah. Menampilkan kompetensi yang memadai sesuai standar yang ditetapkan.'
                    : 'Perlu meningkatkan kompetensi pada beberapa aspek pekerjaan, terutama dalam hal kerjasama dan orientasi pada hasil.');

            Interpretation::create([
                'participant_id' => $participant->id,
                'event_id' => $participant->event_id,
                'category_type_id' => $categoryPotensi->id,
                'interpretation_text' => $interpretationPotensi,
            ]);

            Interpretation::create([
                'participant_id' => $participant->id,
                'event_id' => $participant->event_id,
                'category_type_id' => $categoryKompetensi->id,
                'interpretation_text' => $interpretationKompetensi,
            ]);
        }
    }

    /**
     * Generate complete aspect assessments for a category
     */
    private function generateAspectAssessments(
        CategoryAssessment $categoryAssessment,
        int $categoryTypeId,
        float $performanceMultiplier = 1.0
    ): void {
        // Get all aspects for this category
        $aspects = Aspect::where('category_type_id', $categoryTypeId)
            ->orderBy('order')
            ->get();

        foreach ($aspects as $aspect) {
            // Check if this is Potensi or Kompetensi
            $isPotensi = $categoryTypeId == 1;

            // Calculate individual rating based on performance multiplier
            $baseIndividualRating = $aspect->standard_rating * $performanceMultiplier;

            // For Kompetensi, rating must be INTEGER 1-5
            // For Potensi, will be recalculated after sub-aspects are generated
            if ($isPotensi) {
                $individualRating = max(1.0, min(5.0, $baseIndividualRating)); // Temporary value
            } else {
                $individualRating = (int) max(1, min(5, round($baseIndividualRating))); // Integer for Kompetensi
            }

            // Calculate scores
            $standardScore = $aspect->standard_rating * ($aspect->weight_percentage / 100);
            $individualScore = $individualRating * ($aspect->weight_percentage / 100);

            // Calculate gaps
            $gapRating = $individualRating - $aspect->standard_rating;
            $gapScore = $individualScore - $standardScore;

            // Determine conclusion
            $conclusionCode = null;
            if ($gapRating < -0.5) {
                $conclusionText = 'Kurang Memenuhi Standard';
            } elseif ($gapRating < 0.5) {
                $conclusionText = 'Memenuhi Standard';
            } else {
                $conclusionText = 'Sangat Memenuhi Standard';
            }

            // Get description text based on aspect
            $descriptionText = $this->getAspectDescription($aspect->code, $conclusionText);

            $aspectAssessment = AspectAssessment::create([
                'category_assessment_id' => $categoryAssessment->id,
                'participant_id' => $categoryAssessment->participant_id,
                'event_id' => $categoryAssessment->event_id,
                'batch_id' => $categoryAssessment->batch_id,
                'position_formation_id' => $categoryAssessment->position_formation_id,
                'aspect_id' => $aspect->id,
                'standard_rating' => $aspect->standard_rating,
                'standard_score' => round($standardScore, 2),
                'individual_rating' => $isPotensi ? round($individualRating, 2) : $individualRating,
                'individual_score' => round($individualScore, 2),
                'gap_rating' => round($gapRating, 2),
                'gap_score' => round($gapScore, 2),
                'percentage_score' => round(($individualRating / 5) * 100),
                'conclusion_code' => $conclusionCode,
                'conclusion_text' => $conclusionText,
                'description_text' => $descriptionText,
            ]);

            // Generate sub-aspect assessments (for Potensi aspects only)
            // This will recalculate individual_rating and percentage_score for Potensi
            $this->generateSubAspectAssessments($aspectAssessment, $performanceMultiplier);
        }
    }

    /**
     * Get aspect description based on aspect code and conclusion
     */
    private function getAspectDescription(string $aspectCode, string $conclusion): ?string
    {
        $descriptions = [
            'kecerdasan' => 'Individu menampilkan kemampuan intelektual dan daya tangkap yang '.($conclusion === 'Sangat Memenuhi Standard' ? 'sangat baik' : ($conclusion === 'Memenuhi Standard' ? 'memadai' : 'perlu ditingkatkan')).' dalam memahami informasi dan memecahkan masalah.',
            'sikap_kerja' => 'Menunjukkan sikap kerja yang '.($conclusion === 'Sangat Memenuhi Standard' ? 'sangat positif dan profesional' : ($conclusion === 'Memenuhi Standard' ? 'cukup baik' : 'perlu pengembangan')).' dalam menjalankan tugas dan tanggung jawab.',
            'hubungan_sosial' => 'Kemampuan berinteraksi dan membangun hubungan dengan orang lain '.($conclusion === 'Sangat Memenuhi Standard' ? 'sangat baik' : ($conclusion === 'Memenuhi Standard' ? 'cukup memadai' : 'memerlukan perhatian')),
            'kepribadian' => 'Karakteristik kepribadian yang ditampilkan '.($conclusion === 'Sangat Memenuhi Standard' ? 'sangat sesuai' : ($conclusion === 'Memenuhi Standard' ? 'sesuai' : 'kurang sesuai')).' dengan profil jabatan yang diharapkan.',
            'integritas' => 'Individu '.($conclusion === 'Sangat Memenuhi Standard' ? 'sangat kompeten' : ($conclusion === 'Memenuhi Standard' ? 'kompeten' : 'cukup kompeten')).' menampilkan kompetensi integritas sesuai dengan standar level yang ditetapkan.',
            'kerjasama' => 'Kemampuan bekerja sama dalam tim '.($conclusion === 'Sangat Memenuhi Standard' ? 'sangat menonjol' : ($conclusion === 'Memenuhi Standard' ? 'memadai' : 'perlu ditingkatkan')),
            'komunikasi' => 'Keterampilan komunikasi yang ditampilkan '.($conclusion === 'Sangat Memenuhi Standard' ? 'sangat efektif' : ($conclusion === 'Memenuhi Standard' ? 'cukup efektif' : 'perlu pengembangan')),
            'orientasi_pada_hasil' => 'Fokus pada pencapaian hasil kerja '.($conclusion === 'Sangat Memenuhi Standard' ? 'sangat kuat' : ($conclusion === 'Memenuhi Standard' ? 'cukup baik' : 'memerlukan peningkatan')),
            'pelayanan_publik' => 'Orientasi pada pelayanan kepada publik '.($conclusion === 'Sangat Memenuhi Standard' ? 'sangat baik' : ($conclusion === 'Memenuhi Standard' ? 'memadai' : 'perlu perhatian')),
            'pengembangan_diri_dan_orang_lain' => 'Komitmen terhadap pengembangan diri dan orang lain '.($conclusion === 'Sangat Memenuhi Standard' ? 'sangat tinggi' : ($conclusion === 'Memenuhi Standard' ? 'cukup baik' : 'perlu ditingkatkan')),
            'mengelola_perubahan' => 'Kemampuan mengelola dan beradaptasi dengan perubahan '.($conclusion === 'Sangat Memenuhi Standard' ? 'sangat baik' : ($conclusion === 'Memenuhi Standard' ? 'memadai' : 'memerlukan pengembangan')),
            'pengambilan_keputusan' => 'Kualitas dalam pengambilan keputusan '.($conclusion === 'Sangat Memenuhi Standard' ? 'sangat baik' : ($conclusion === 'Memenuhi Standard' ? 'cukup baik' : 'perlu peningkatan')),
            'perekat_bangsa' => 'Komitmen sebagai perekat bangsa dan pemersatu '.($conclusion === 'Sangat Memenuhi Standard' ? 'sangat kuat' : ($conclusion === 'Memenuhi Standard' ? 'memadai' : 'perlu penguatan')),
        ];

        return $descriptions[$aspectCode] ?? null;
    }

    /**
     * Generate sub-aspect assessments for a given aspect assessment (POTENSI only)
     * Kompetensi aspects don't have sub-aspects
     */
    private function generateSubAspectAssessments(
        AspectAssessment $aspectAssessment,
        float $performanceMultiplier = 1.0
    ): void {
        // Get the aspect to determine if it has sub-aspects
        $aspect = Aspect::find($aspectAssessment->aspect_id);

        // Only Potensi category (category_type_id = 1) has sub-aspects
        if ($aspect->category_type_id != 1) {
            return; // Kompetensi doesn't have sub-aspects
        }

        // Get all sub-aspects for this aspect
        $subAspects = SubAspect::where('aspect_id', $aspect->id)
            ->orderBy('order')
            ->get();

        // If no sub-aspects found, skip
        if ($subAspects->isEmpty()) {
            return;
        }

        foreach ($subAspects as $subAspect) {
            // Calculate individual rating based on performance multiplier
            $baseIndividualRating = $subAspect->standard_rating * $performanceMultiplier;
            $individualRating = (int) max(1, min(5, round($baseIndividualRating))); // Cast to integer for match

            // Determine rating label
            $ratingLabel = match ($individualRating) {
                1 => 'Sangat Kurang',
                2 => 'Kurang',
                3 => 'Cukup',
                4 => 'Baik',
                5 => 'Sangat Baik',
                default => 'Cukup',
            };

            SubAspectAssessment::create([
                'aspect_assessment_id' => $aspectAssessment->id,
                'participant_id' => $aspectAssessment->participant_id,
                'event_id' => $aspectAssessment->event_id,
                'sub_aspect_id' => $subAspect->id,
                'standard_rating' => $subAspect->standard_rating,
                'individual_rating' => $individualRating,
                'rating_label' => $ratingLabel,
            ]);
        }

        // After generating all sub-aspects, recalculate aspect's individual_rating and percentage_score
        $avgIndividualRating = SubAspectAssessment::where('aspect_assessment_id', $aspectAssessment->id)
            ->avg('individual_rating');

        $aspectAssessment->update([
            'individual_rating' => round($avgIndividualRating, 2),
            'percentage_score' => round(($avgIndividualRating / 5) * 100),
        ]);
    }
}
