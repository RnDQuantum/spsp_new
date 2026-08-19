<?php

declare(strict_types=1);

namespace Database\Seeders\Support;

use App\Models\AssessmentTemplate;
use App\Models\CategoryType;
use App\Models\Participant;

/**
 * AssessmentRecordGenerator - Engine Kalkulasi & Generator Penilaian Asesmen In-Memory
 *
 * Mengelola kalkulasi Nine-Box distribution, rating potensi & kompetensi (1-5),
 * sub-aspects, aspects, category assessments, final assessments, MMPI, dan interpretasi.
 */
class AssessmentRecordGenerator
{
    /**
     * Tentukan kategori Nine Box berdasarkan distribusi persentase.
     */
    public static function determineNineBoxCategory(array $distribution): string
    {
        $random = fake()->numberBetween(1, 100);
        $cumulative = 0;

        foreach ($distribution as $box => $percentage) {
            $cumulative += $percentage;
            if ($random <= $cumulative) {
                return $box;
            }
        }

        return 'K-5';
    }

    /**
     * Generate data penilaian mentah untuk matriks Nine-Box.
     */
    public static function generateAssessmentsDataForNineBox(
        AssessmentTemplate $template,
        CategoryType $potensiCategory,
        CategoryType $kompetensiCategory,
        string $boxCategory,
        $aspectsCache
    ): array {
        $boxMapping = [
            'K-1' => ['kompetensi' => 'low', 'potensi' => 'low'],
            'K-2' => ['kompetensi' => 'medium', 'potensi' => 'low'],
            'K-3' => ['kompetensi' => 'low', 'potensi' => 'medium'],
            'K-4' => ['kompetensi' => 'high', 'potensi' => 'low'],
            'K-5' => ['kompetensi' => 'medium', 'potensi' => 'medium'],
            'K-6' => ['kompetensi' => 'low', 'potensi' => 'high'],
            'K-7' => ['kompetensi' => 'high', 'potensi' => 'medium'],
            'K-8' => ['kompetensi' => 'medium', 'potensi' => 'high'],
            'K-9' => ['kompetensi' => 'high', 'potensi' => 'high'],
        ];

        $levels = $boxMapping[$boxCategory] ?? ['kompetensi' => 'medium', 'potensi' => 'medium'];

        $kompetensiMultiplier = self::getPerformanceMultiplier($levels['kompetensi']);
        $potensiMultiplier = self::getPerformanceMultiplier($levels['potensi']);

        $assessmentsData = [
            'potensi' => [],
            'kompetensi' => [],
        ];

        // 1. Potensi: Ambil aspek dari cache & aplikasikan pengali
        $potensiAspects = $aspectsCache->get($potensiCategory->id)?->sortBy('order') ?? collect();

        foreach ($potensiAspects as $aspect) {
            $subAspectsData = [];

            foreach ($aspect->subAspects as $subAspect) {
                $variance = fake()->randomFloat(2, -0.3, 0.3);
                $performanceMultiplier = fake()->randomFloat(2, $potensiMultiplier[0], $potensiMultiplier[1]);

                $baseRating = $subAspect->standard_rating * $performanceMultiplier + $variance;
                $individualRating = (int) max(1, min(5, round($baseRating)));

                $subAspectsData[] = [
                    'sub_aspect_code' => $subAspect->code,
                    'individual_rating' => $individualRating,
                ];
            }

            $assessmentsData['potensi'][] = [
                'aspect_code' => $aspect->code,
                'sub_aspects' => $subAspectsData,
            ];
        }

        // 2. Kompetensi: Ambil aspek dari cache & aplikasikan pengali
        $kompetensiAspects = $aspectsCache->get($kompetensiCategory->id)?->sortBy('order') ?? collect();

        foreach ($kompetensiAspects as $aspect) {
            $variance = fake()->randomFloat(2, -0.3, 0.3);
            $performanceMultiplier = fake()->randomFloat(2, $kompetensiMultiplier[0], $kompetensiMultiplier[1]);

            $baseRating = $aspect->standard_rating * $performanceMultiplier + $variance;
            $individualRating = (int) max(1, min(5, round($baseRating)));

            $assessmentsData['kompetensi'][] = [
                'aspect_code' => $aspect->code,
                'individual_rating' => $individualRating,
            ];
        }

        return $assessmentsData;
    }

    /**
     * Range pengali performa berdasarkan level.
     */
    public static function getPerformanceMultiplier(string $level): array
    {
        return match ($level) {
            'high' => [1.05, 1.25],   // Exceed standard (rating 3.15-5.00)
            'medium' => [0.85, 1.1],  // Around standard (rating 2.55-4.40)
            'low' => [0.40, 0.75],    // Below standard (rating 1.20-3.00)
            default => [0.85, 1.1],
        };
    }

    /**
     * Generate seluruh records assessment secara in-memory untuk bulk insert.
     */
    public static function generateAssessmentRecords(
        Participant $participant,
        AssessmentTemplate $template,
        array $assessmentsData,
        $categoriesCache,
        $aspectsCache,
        int &$categoryIdCounter,
        int &$aspectIdCounter,
        int &$subAspectIdCounter,
        array &$categoryAssessmentsData,
        array &$aspectAssessmentsData,
        array &$subAspectAssessmentsData,
        array &$finalAssessmentsData
    ): void {
        $categoryResults = [];
        $now = now();

        foreach ($assessmentsData as $categoryCode => $categoryData) {
            $categoryIdCounter++;
            $categoryId = $categoryIdCounter;

            $category = $categoriesCache->get($template->id)->firstWhere('code', $categoryCode);

            $categoryTotalStandardRating = 0.0;
            $categoryTotalStandardScore = 0.0;
            $categoryTotalIndividualRating = 0.0;
            $categoryTotalIndividualScore = 0.0;

            foreach ($categoryData as $aspectData) {
                $aspectIdCounter++;
                $aspectId = $aspectIdCounter;

                $aspect = $aspectsCache->get($category->id)?->firstWhere('code', $aspectData['aspect_code']);
                if (! $aspect) {
                    continue;
                }

                $hasSubAspects = isset($aspectData['sub_aspects']) && ! empty($aspectData['sub_aspects']);

                if ($hasSubAspects) {
                    $subAspectRatings = [];
                    $standardRating = (float) collect($aspect->subAspects)->avg('standard_rating');

                    foreach ($aspectData['sub_aspects'] as $subAspectData) {
                        $subAspectIdCounter++;

                        $subAspect = $aspect->subAspects->firstWhere('code', $subAspectData['sub_aspect_code']);
                        if (! $subAspect) {
                            continue;
                        }

                        $rating = (int) $subAspectData['individual_rating'];
                        $subAspectRatings[] = $rating;

                        $subAspectAssessmentsData[] = [
                            'id' => $subAspectIdCounter,
                            'aspect_assessment_id' => $aspectId,
                            'participant_id' => $participant->id,
                            'event_id' => $participant->event_id,
                            'sub_aspect_id' => $subAspect->id,
                            'standard_rating' => (int) $subAspect->standard_rating,
                            'individual_rating' => $rating,
                            'rating_label' => self::getRatingLabel($rating),
                            'created_at' => $now,
                            'updated_at' => $now,
                        ];
                    }

                    $individualRating = (float) collect($subAspectRatings)->avg();
                } else {
                    $standardRating = (float) $aspect->standard_rating;
                    $individualRating = (float) $aspectData['individual_rating'];
                }

                $weight = (float) $aspect->weight_percentage;
                $standardScore = $standardRating * $weight;
                $individualScore = $individualRating * $weight;
                $gapRating = $individualRating - $standardRating;
                $gapScore = $individualScore - $standardScore;
                $percentageScore = (int) round(($individualRating / 5) * 100);

                $conclusionCode = self::determineAspectConclusion($gapRating);
                $conclusionText = self::getAspectConclusionText($conclusionCode);

                $categoryTotalStandardRating += $standardRating;
                $categoryTotalStandardScore += $standardScore;
                $categoryTotalIndividualRating += $individualRating;
                $categoryTotalIndividualScore += $individualScore;

                $aspectAssessmentsData[] = [
                    'id' => $aspectId,
                    'category_assessment_id' => $categoryId,
                    'participant_id' => $participant->id,
                    'event_id' => $participant->event_id,
                    'batch_id' => $participant->batch_id,
                    'position_formation_id' => $participant->position_formation_id,
                    'aspect_id' => $aspect->id,
                    'standard_rating' => round($standardRating, 2),
                    'standard_score' => round($standardScore, 2),
                    'individual_rating' => round($individualRating, 2),
                    'individual_score' => round($individualScore, 2),
                    'gap_rating' => round($gapRating, 2),
                    'gap_score' => round($gapScore, 2),
                    'percentage_score' => $percentageScore,
                    'conclusion_code' => $conclusionCode,
                    'conclusion_text' => $conclusionText,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            $categoryGapRating = $categoryTotalIndividualRating - $categoryTotalStandardRating;
            $categoryGapScore = $categoryTotalIndividualScore - $categoryTotalStandardScore;
            $categoryConclusionCode = self::determineCategoryConclusion($categoryGapScore);
            $categoryConclusionText = self::getCategoryConclusionText($categoryConclusionCode);

            $categoryAssessmentsData[] = [
                'id' => $categoryId,
                'participant_id' => $participant->id,
                'event_id' => $participant->event_id,
                'batch_id' => $participant->batch_id,
                'position_formation_id' => $participant->position_formation_id,
                'category_type_id' => $category->id,
                'total_standard_rating' => round($categoryTotalStandardRating, 2),
                'total_standard_score' => round($categoryTotalStandardScore, 2),
                'total_individual_rating' => round($categoryTotalIndividualRating, 2),
                'total_individual_score' => round($categoryTotalIndividualScore, 2),
                'gap_rating' => round($categoryGapRating, 2),
                'gap_score' => round($categoryGapScore, 2),
                'conclusion_code' => $categoryConclusionCode,
                'conclusion_text' => $categoryConclusionText,
                'created_at' => $now,
                'updated_at' => $now,
            ];

            $categoryResults[$categoryCode] = [
                'weight' => $category->weight_percentage,
                'score' => $categoryTotalIndividualScore,
            ];
        }

        $potensiData = $categoryResults['potensi'] ?? null;
        $kompetensiData = $categoryResults['kompetensi'] ?? null;

        if ($potensiData && $kompetensiData) {
            $potensiCategoryAssessment = collect($categoryAssessmentsData)->firstWhere('category_type_id', $categoriesCache->get($template->id)->firstWhere('code', 'potensi')->id);
            $kompetensiCategoryAssessment = collect($categoryAssessmentsData)->firstWhere('category_type_id', $categoriesCache->get($template->id)->firstWhere('code', 'kompetensi')->id);

            $totalStandardScore = ($potensiCategoryAssessment['total_standard_score'] ?? 0) + ($kompetensiCategoryAssessment['total_standard_score'] ?? 0);
            $totalIndividualScore = ($potensiCategoryAssessment['total_individual_score'] ?? 0) + ($kompetensiCategoryAssessment['total_individual_score'] ?? 0);

            $achievementPercentage = $totalStandardScore > 0
                ? round(($totalIndividualScore / $totalStandardScore) * 100, 2)
                : 0.0;

            $conclusionCode = self::determineFinalConclusion($achievementPercentage);
            $conclusionText = self::getFinalConclusionText($conclusionCode);

            $finalAssessmentsData[] = [
                'participant_id' => $participant->id,
                'event_id' => $participant->event_id,
                'batch_id' => $participant->batch_id,
                'position_formation_id' => $participant->position_formation_id,
                'potensi_weight' => $potensiData['weight'],
                'potensi_standard_score' => round($potensiCategoryAssessment['total_standard_score'] ?? 0, 2),
                'potensi_individual_score' => round($potensiCategoryAssessment['total_individual_score'] ?? 0, 2),
                'kompetensi_weight' => $kompetensiData['weight'],
                'kompetensi_standard_score' => round($kompetensiCategoryAssessment['total_standard_score'] ?? 0, 2),
                'kompetensi_individual_score' => round($kompetensiCategoryAssessment['total_individual_score'] ?? 0, 2),
                'total_standard_score' => round($totalStandardScore, 2),
                'total_individual_score' => round($totalIndividualScore, 2),
                'achievement_percentage' => $achievementPercentage,
                'conclusion_code' => $conclusionCode,
                'conclusion_text' => $conclusionText,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }
    }

    /**
     * Generate data MMPI / Tes Kejiwaan.
     */
    public static function generateMmpiData(Participant $participant, string $boxCategory): array
    {
        $baseData = [
            'participant_id' => $participant->id,
            'event_id' => $participant->event_id,
            'no_test' => $participant->test_number,
            'username' => $participant->username,
            'created_at' => now(),
            'updated_at' => now(),
        ];

        $performanceLevel = match ($boxCategory) {
            'K-7', 'K-8', 'K-9' => 'high',
            'K-1', 'K-3' => 'low',
            default => 'medium',
        };

        $psikogramHigh = [
            'tipe' => 'MMPI-2',
            'skala_validitas' => [
                'L' => fake()->numberBetween(42, 50),
                'F' => fake()->numberBetween(40, 52),
                'K' => fake()->numberBetween(55, 62),
                'VRIN' => fake()->numberBetween(44, 52),
                'TRIN' => fake()->numberBetween(46, 54),
            ],
            'skala_klinis' => [
                'Hs' => fake()->numberBetween(42, 50),
                'D' => fake()->numberBetween(40, 50),
                'Hy' => fake()->numberBetween(44, 52),
                'Pd' => fake()->numberBetween(46, 54),
                'Mf' => fake()->numberBetween(48, 55),
                'Pa' => fake()->numberBetween(42, 50),
                'Pt' => fake()->numberBetween(40, 48),
                'Sc' => fake()->numberBetween(42, 50),
                'Ma' => fake()->numberBetween(50, 58),
                'Si' => fake()->numberBetween(42, 50),
            ],
            'skala_suplementer' => [
                'Es' => fake()->numberBetween(60, 68),
                'A' => fake()->numberBetween(38, 48),
                'WRK' => fake()->numberBetween(40, 48),
                'TPA' => fake()->numberBetween(45, 55),
            ],
            'elevated_scales' => [],
        ];

        $psikogramMedium = [
            'tipe' => 'MMPI-2',
            'skala_validitas' => [
                'L' => fake()->numberBetween(46, 56),
                'F' => fake()->numberBetween(45, 58),
                'K' => fake()->numberBetween(48, 58),
                'VRIN' => fake()->numberBetween(46, 55),
                'TRIN' => fake()->numberBetween(48, 56),
            ],
            'skala_klinis' => [
                'Hs' => fake()->numberBetween(48, 56),
                'D' => fake()->numberBetween(46, 56),
                'Hy' => fake()->numberBetween(48, 58),
                'Pd' => fake()->numberBetween(50, 58),
                'Mf' => fake()->numberBetween(48, 56),
                'Pa' => fake()->numberBetween(46, 56),
                'Pt' => fake()->numberBetween(46, 56),
                'Sc' => fake()->numberBetween(46, 56),
                'Ma' => fake()->numberBetween(50, 60),
                'Si' => fake()->numberBetween(46, 56),
            ],
            'skala_suplementer' => [
                'Es' => fake()->numberBetween(50, 58),
                'A' => fake()->numberBetween(46, 56),
                'WRK' => fake()->numberBetween(46, 56),
                'TPA' => fake()->numberBetween(50, 60),
            ],
            'elevated_scales' => [],
        ];

        $psikogramLow = [
            'tipe' => 'MMPI-2',
            'skala_validitas' => [
                'L' => fake()->numberBetween(52, 66),
                'F' => fake()->numberBetween(55, 68),
                'K' => fake()->numberBetween(40, 50),
                'VRIN' => fake()->numberBetween(50, 62),
                'TRIN' => fake()->numberBetween(50, 62),
            ],
            'skala_klinis' => [
                'Hs' => fake()->numberBetween(55, 66),
                'D' => fake()->numberBetween(58, 68),
                'Hy' => fake()->numberBetween(54, 65),
                'Pd' => fake()->numberBetween(56, 68),
                'Mf' => fake()->numberBetween(48, 58),
                'Pa' => fake()->numberBetween(52, 66),
                'Pt' => fake()->numberBetween(58, 68),
                'Sc' => fake()->numberBetween(52, 65),
                'Ma' => fake()->numberBetween(50, 64),
                'Si' => fake()->numberBetween(54, 66),
            ],
            'skala_suplementer' => [
                'Es' => fake()->numberBetween(38, 48),
                'A' => fake()->numberBetween(60, 70),
                'WRK' => fake()->numberBetween(60, 68),
                'TPA' => fake()->numberBetween(55, 68),
            ],
            'elevated_scales' => ['D', 'Pt', 'A', 'WRK'],
        ];

        return match ($performanceLevel) {
            'high' => array_merge($baseData, [
                'validitas' => 'Valid - Protokol tes konsisten dan dapat dipercaya penuh',
                'internal' => 'Stabilitas emosi sangat matang, regulasi diri prima, dan memiliki mekanisme koping stres yang sangat konstruktif.',
                'interpersonal' => 'Keterampilan interpersonal matang, komunikasi asertif, dan mampu membangun kemitraan kerja yang solid.',
                'kap_kerja' => 'Kapasitas dan ketahanan kerja tinggi, efisien dalam menyelesaikan beban kerja kompleks di bawah target tinggi.',
                'klinik' => 'Tidak terdeteksi adanya indikasi klinis bermakna, kondisi psikologis sehat dan adaptif.',
                'kesimpulan' => 'TIDAK MENUNJUKKAN GEJALA GANGGUAN JIWA BERAT - Prima & Adaptif',
                'psikogram' => json_encode($psikogramHigh),
                'nilai_pq' => fake()->randomFloat(2, 85, 95),
                'tingkat_stres' => 'Rendah',
            ]),
            'medium' => array_merge($baseData, [
                'validitas' => 'Valid - Protokol tes dapat diandalkan',
                'internal' => 'Kematangan emosional memadai dengan daya lentur psikologis yang cukup stabil dalam menghadapi rutinitas kerja.',
                'interpersonal' => 'Mampu berinteraksi secara kooperatif dan menjalin kerja sama tim dengan baik.',
                'kap_kerja' => 'Kapasitas kerja memadai dan sesuai standar operasional yang dipersyaratkan.',
                'klinik' => 'Tidak ada indikasi klinis yang mengganggu kapasitas kerja fungsional.',
                'kesimpulan' => 'TIDAK MENUNJUKKAN GEJALA GANGGUAN JIWA BERAT - Memenuhi Syarat',
                'psikogram' => json_encode($psikogramMedium),
                'nilai_pq' => fake()->randomFloat(2, 70, 84),
                'tingkat_stres' => 'Normal',
            ]),
            'low' => array_merge($baseData, [
                'validitas' => 'Valid dengan catatan - Terdapat indikasi defensif ringan saat pengisian tes',
                'internal' => 'Terdapat indikasi ketegangan emosional dan kecemasan situasional yang membutuhkan manajemen stres aktif.',
                'interpersonal' => 'Memerlukan penguatan dalam komunikasi interpersonal dan adaptasi dinamika kerja tim.',
                'kap_kerja' => 'Kapasitas kerja rentan mengalami penurunan di bawah situasi multi-tasking atau tekanan tenggat waktu ketat.',
                'klinik' => 'Terdapat elevasi pada skala kecemasan/distres kerja (Pt, A, WRK); disarankan pendampingan suportif.',
                'kesimpulan' => 'TIDAK MENUNJUKKAN GEJALA GANGGUAN JIWA BERAT - Perlu Pembinaan Terarah',
                'psikogram' => json_encode($psikogramLow),
                'nilai_pq' => fake()->randomFloat(2, 50, 69),
                'tingkat_stres' => fake()->randomElement(['Sedang', 'Tinggi']),
            ]),
        };
    }

    /**
     * Generate data interpretasi kategori (Potensi & Kompetensi).
     */
    public static function generateInterpretationData(
        Participant $participant,
        CategoryType $category,
        string $boxCategory,
        string $categoryCode
    ): array {
        $boxMapping = [
            'K-1' => ['kompetensi' => 'low', 'potensi' => 'low'],
            'K-2' => ['kompetensi' => 'medium', 'potensi' => 'low'],
            'K-3' => ['kompetensi' => 'low', 'potensi' => 'medium'],
            'K-4' => ['kompetensi' => 'high', 'potensi' => 'low'],
            'K-5' => ['kompetensi' => 'medium', 'potensi' => 'medium'],
            'K-6' => ['kompetensi' => 'low', 'potensi' => 'high'],
            'K-7' => ['kompetensi' => 'high', 'potensi' => 'medium'],
            'K-8' => ['kompetensi' => 'medium', 'potensi' => 'high'],
            'K-9' => ['kompetensi' => 'high', 'potensi' => 'high'],
        ];

        $levels = $boxMapping[$boxCategory] ?? ['kompetensi' => 'medium', 'potensi' => 'medium'];
        $performanceLevel = $levels[$categoryCode] ?? 'medium';

        $interpretations = [
            'potensi' => [
                'high' => 'Memiliki potensi yang sangat baik dengan kemampuan di atas rata-rata dalam berbagai aspek. Kandidat menunjukkan kecenderungan untuk berkembang pesat dan mampu mengatasi tantangan kompleks.',
                'medium' => 'Menunjukkan potensi yang memadai dengan ruang untuk pengembangan lebih lanjut. Dengan bimbingan dan kesempatan yang tepat, kandidat dapat meningkatkan performa secara signifikan.',
                'low' => 'Memerlukan pengembangan intensif untuk meningkatkan potensi di berbagai aspek. Diperlukan program pelatihan terstruktur dan mentoring berkelanjutan untuk mencapai standar yang diharapkan.',
            ],
            'kompetensi' => [
                'high' => 'Menguasai kompetensi dengan sangat baik dan konsisten di atas standar yang ditetapkan. Kandidat mampu menerapkan kompetensi secara efektif dalam berbagai situasi kerja.',
                'medium' => 'Memiliki kompetensi yang cukup memadai sesuai dengan standar posisi. Beberapa area masih memerlukan pengembangan untuk mencapai tingkat optimal.',
                'low' => 'Perlu peningkatan signifikan dalam beberapa area kompetensi kunci. Diperlukan upaya intensif untuk mengembangkan kompetensi yang dipersyaratkan oleh posisi ini.',
            ],
        ];

        return [
            'participant_id' => $participant->id,
            'category_type_id' => $category->id,
            'event_id' => $participant->event_id,
            'interpretation_text' => $interpretations[$categoryCode][$performanceLevel] ?? 'Data interpretasi sedang diproses.',
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    public static function getRatingLabel(int $rating): string
    {
        return match ($rating) {
            1 => 'Sangat Kurang',
            2 => 'Kurang',
            3 => 'Cukup',
            4 => 'Baik',
            5 => 'Sangat Baik',
            default => 'Cukup',
        };
    }

    public static function determineAspectConclusion(float $gapRating): string
    {
        if ($gapRating < -0.5) {
            return 'below_standard';
        } elseif ($gapRating < 0.5) {
            return 'meets_standard';
        } else {
            return 'exceeds_standard';
        }
    }

    public static function getAspectConclusionText(string $code): string
    {
        return match ($code) {
            'below_standard' => 'Kurang Memenuhi Standard',
            'meets_standard' => 'Memenuhi Standard',
            'exceeds_standard' => 'Melebihi Standard',
            default => 'Memenuhi Standard',
        };
    }

    public static function determineCategoryConclusion(float $gapScore): string
    {
        if ($gapScore < -10) {
            return 'DBS';
        } elseif ($gapScore < 0) {
            return 'MS';
        } elseif ($gapScore < 20) {
            return 'K';
        } else {
            return 'SK';
        }
    }

    public static function getCategoryConclusionText(string $code): string
    {
        return match ($code) {
            'DBS' => 'DI BAWAH STANDARD',
            'MS' => 'MEMENUHI STANDARD',
            'K' => 'KOMPETEN',
            'SK' => 'SANGAT KOMPETEN',
            default => 'MEMENUHI STANDARD',
        };
    }

    public static function determineFinalConclusion(float $percentage): string
    {
        if ($percentage >= 120) {
            return 'SK';
        } elseif ($percentage >= 100) {
            return 'K';
        } elseif ($percentage >= 80) {
            return 'MS';
        } else {
            return 'DBS';
        }
    }

    public static function getFinalConclusionText(string $code): string
    {
        return match ($code) {
            'SK' => 'SANGAT KOMPETEN',
            'K' => 'KOMPETEN',
            'MS' => 'MEMENUHI STANDARD',
            'DBS' => 'DI BAWAH STANDARD',
            default => 'MEMENUHI STANDARD',
        };
    }
}
