<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\{
    Institution,
    AssessmentTemplate,
    AssessmentEvent,
    Batch,
    PositionFormation,
    Participant,
    CategoryType,
    CategoryAssessment,
    Aspect,
    AspectAssessment,
    SubAspect,
    SubAspectAssessment,
    FinalAssessment,
    PsychologicalTest,
    Interpretation
};

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
            'year' => 2025,
            'start_date' => '2025-09-01',
            'end_date' => '2025-12-31',
            'status' => 'completed',
        ]);

        // ==========================================
        // 3. CREATE BATCH & POSITION
        // ==========================================

        $batch = Batch::create([
            'event_id' => $event->id,
            'code' => 'BATCH-1-MOJOKERTO',
            'name' => 'Gelombang 1 - Mojokerto',
            'location' => 'Mojokerto',
            'batch_number' => 1,
            'start_date' => '2025-09-27',
            'end_date' => '2025-09-28',
        ]);

        $position = PositionFormation::create([
            'event_id' => $event->id,
            'code' => 'fisikawan_medis',
            'name' => 'Fisikawan Medis Ahli Pertama',
            'quota' => 10,
        ]);

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
            'photo_path' => null,
            'assessment_date' => '2025-09-27',
        ]);

        // --- 4.1. Category Assessment: POTENSI ---
        $catPotensi = CategoryAssessment::create([
            'participant_id' => $participant->id,
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
            'aspect_id' => $aspectKecerdasan->id,
            'standard_rating' => 3.15,
            'standard_score' => 94.50,
            'individual_rating' => 2.58,
            'individual_score' => 77.29,
            'gap_rating' => -0.57,
            'gap_score' => -17.21,
            'percentage_score' => 78,
            'conclusion_text' => 'Kurang Memenuhi Standard',
            'description_text' => null,
        ]);

        // --- 4.3. Sub-Aspect Assessments for Kecerdasan ---
        $kecerdasanSubAspects = [
            ['code' => 'kecerdasan_umum', 'standard' => 3, 'individual' => 3, 'label' => 'Cukup'],
            ['code' => 'daya_tangkap', 'standard' => 4, 'individual' => 4, 'label' => 'Baik'],
            ['code' => 'kemampuan_analisa', 'standard' => 4, 'individual' => 4, 'label' => 'Baik'],
            ['code' => 'berpikir_konseptual', 'standard' => 3, 'individual' => 3, 'label' => 'Cukup'],
            ['code' => 'logika_berpikir', 'standard' => 4, 'individual' => 4, 'label' => 'Baik'],
            ['code' => 'kemampuan_numerik', 'standard' => 3, 'individual' => 3, 'label' => 'Cukup'],
        ];

        foreach ($kecerdasanSubAspects as $subAspectData) {
            $subAspect = SubAspect::where('code', $subAspectData['code'])->first();
            SubAspectAssessment::create([
                'aspect_assessment_id' => $aspKecerdasan->id,
                'sub_aspect_id' => $subAspect->id,
                'standard_rating' => $subAspectData['standard'],
                'individual_rating' => $subAspectData['individual'],
                'rating_label' => $subAspectData['label'],
            ]);
        }

        // --- 4.4. Category Assessment: KOMPETENSI ---
        $catKompetensi = CategoryAssessment::create([
            'participant_id' => $participant->id,
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

        // --- 4.5. Aspect Assessment: Integritas ---
        $aspectIntegritas = Aspect::where('code', 'integritas')->first();
        AspectAssessment::create([
            'category_assessment_id' => $catKompetensi->id,
            'aspect_id' => $aspectIntegritas->id,
            'standard_rating' => 2.70,
            'standard_score' => 32.40,
            'individual_rating' => 3.08,
            'individual_score' => 36.96,
            'gap_rating' => 0.38,
            'gap_score' => 4.56,
            'percentage_score' => null,
            'conclusion_text' => 'Sangat Memenuhi Standard',
            'description_text' => 'Individu kompeten menampilkan kompetensi integritas sesuai dengan standar level yang di tetapkan.',
        ]);

        // --- 4.6. Final Assessment ---
        FinalAssessment::create([
            'participant_id' => $participant->id,
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
            'category_type_id' => $categoryPotensi->id,
            'interpretation_text' => 'Memiliki kepekaan yang cukup memadai dalam memahami kebutuhan orang-orang yang ada di sekitarnya.',
        ]);

        Interpretation::create([
            'participant_id' => $participant->id,
            'category_type_id' => $categoryKompetensi->id,
            'interpretation_text' => 'Dalam bekerja, individu cukup mampu mengelola pekerjaan yang menjadi tanggung jawabnya sesuai dengan prioritas penyelesaian masalah.',
        ]);

        // ==========================================
        // 5. ADDITIONAL SAMPLE PARTICIPANTS (3 more)
        // ==========================================

        $this->createAdditionalParticipants($event, $batch, $position, $categoryPotensi, $categoryKompetensi);
    }

    private function createAdditionalParticipants($event, $batch, $position, $categoryPotensi, $categoryKompetensi): void
    {
        $sampleParticipants = [
            [
                'test_number' => '03-5-2-18-002',
                'skb_number' => '24400240120012572',
                'name' => 'BUDI SANTOSO, S.T',
                'email' => 'budi.santoso@example.com',
            ],
            [
                'test_number' => '03-5-2-18-003',
                'skb_number' => '24400240120012573',
                'name' => 'CITRA DEWI, S.Kom',
                'email' => 'citra.dewi@example.com',
            ],
            [
                'test_number' => '03-5-2-18-004',
                'skb_number' => '24400240120012574',
                'name' => 'DARMAWAN PUTRA, S.E',
                'email' => 'darmawan.putra@example.com',
            ],
        ];

        foreach ($sampleParticipants as $data) {
            $participant = Participant::create([
                'event_id' => $event->id,
                'batch_id' => $batch->id,
                'position_formation_id' => $position->id,
                'test_number' => $data['test_number'],
                'skb_number' => $data['skb_number'],
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => '081234567890',
                'photo_path' => null,
                'assessment_date' => '2025-09-27',
            ]);

            // Create basic assessments for additional participants
            CategoryAssessment::create([
                'participant_id' => $participant->id,
                'category_type_id' => $categoryPotensi->id,
                'total_standard_rating' => 12.50,
                'total_standard_score' => 310.00,
                'total_individual_rating' => 13.20,
                'total_individual_score' => 325.00,
                'gap_rating' => 0.70,
                'gap_score' => 15.00,
                'conclusion_code' => 'MS',
                'conclusion_text' => 'MEMENUHI STANDARD',
            ]);

            CategoryAssessment::create([
                'participant_id' => $participant->id,
                'category_type_id' => $categoryKompetensi->id,
                'total_standard_rating' => 25.00,
                'total_standard_score' => 280.00,
                'total_individual_rating' => 26.50,
                'total_individual_score' => 295.00,
                'gap_rating' => 1.50,
                'gap_score' => 15.00,
                'conclusion_code' => 'K',
                'conclusion_text' => 'KOMPETEN',
            ]);

            FinalAssessment::create([
                'participant_id' => $participant->id,
                'potensi_weight' => 40,
                'potensi_standard_score' => 124.00,
                'potensi_individual_score' => 130.00,
                'kompetensi_weight' => 60,
                'kompetensi_standard_score' => 168.00,
                'kompetensi_individual_score' => 177.00,
                'total_standard_score' => 292.00,
                'total_individual_score' => 307.00,
                'achievement_percentage' => 105.14,
                'conclusion_code' => 'MS',
                'conclusion_text' => 'MEMENUHI SYARAT (MS)',
            ]);

            PsychologicalTest::create([
                'participant_id' => $participant->id,
                'raw_score' => 45.00,
                'iq_score' => 105,
                'validity_status' => 'Hasil tes ini konsisten dan dapat dipercaya',
                'internal_status' => 'Terbuka',
                'interpersonal_status' => 'Terbuka',
                'work_capacity_status' => 'Terbuka',
                'clinical_status' => 'Terbuka',
                'conclusion_code' => 'MS',
                'conclusion_text' => 'MEMENUHI SYARAT (MS)',
                'notes' => null,
            ]);
        }
    }
}

