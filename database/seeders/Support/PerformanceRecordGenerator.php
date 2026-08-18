<?php

declare(strict_types=1);

namespace Database\Seeders\Support;

use App\Models\Participant;

/**
 * PerformanceRecordGenerator - Generator Catatan Kinerja Historis Tahunan Realistis
 *
 * Bertanggung jawab menghasilkan data time-series KPI 5 tahun (2022-2026)
 * dan breakdown metrik evaluasi kinerja per peserta.
 */
class PerformanceRecordGenerator
{
    /**
     * Generate 5-year performance records for a participant.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function generateForParticipant(Participant $participant): array
    {
        $baseAchievement = (float) ($participant->finalAssessment->achievement_percentage ?? 94.00);
        $records = [];
        $years = [2022, 2023, 2024, 2025, 2026];

        // Seeded variation for realism
        $seedFactor = ($participant->id % 7) * 0.4;

        foreach ($years as $idx => $year) {
            // Trend progression: gradual upward slope
            $yearBonus = ($idx - 2) * 1.35 + $seedFactor;
            $kpiScore = round(min(108.00, max(82.00, $baseAchievement + $yearBonus)), 2);
            $targetScore = 100.00;
            $benchmarkScore = 90.00;

            $rating = match (true) {
                $kpiScore >= 100.00 => 'Istimewa (Exceeded)',
                $kpiScore >= 90.00 => 'Sangat Baik (Above Standard)',
                $kpiScore >= 80.00 => 'Baik (Meets Standard)',
                default => 'Cukup (Developing)',
            };

            $kpiBreakdown = [
                [
                    'metric' => 'Revenue & Budget Efficiency',
                    'weight' => '30%',
                    'target' => '100.00%',
                    'actual' => number_format(round($kpiScore * 1.02, 2), 2).'%',
                    'status' => $kpiScore >= 98 ? 'Exceeded' : 'Achieved',
                    'statusClass' => 'bg-emerald-50 text-forest-green border-emerald-100',
                ],
                [
                    'metric' => 'Strategic Program Delivery',
                    'weight' => '25%',
                    'target' => '90.00%',
                    'actual' => number_format(round($kpiScore * 0.96, 2), 2).'%',
                    'status' => $kpiScore >= 92 ? 'Exceeded' : 'Achieved',
                    'statusClass' => 'bg-emerald-50 text-forest-green border-emerald-100',
                ],
                [
                    'metric' => 'Digital Transformation & Automation Index',
                    'weight' => '20%',
                    'target' => '85.00%',
                    'actual' => number_format(round($kpiScore * 0.94, 2), 2).'%',
                    'status' => 'Achieved',
                    'statusClass' => 'bg-emerald-50 text-forest-green border-emerald-100',
                ],
                [
                    'metric' => 'Stakeholder Satisfaction & Retention',
                    'weight' => '15%',
                    'target' => '90.00%',
                    'actual' => number_format(round(min(99.0, $kpiScore * 0.99), 2), 2).'%',
                    'status' => 'Exceeded',
                    'statusClass' => 'bg-emerald-50 text-forest-green border-emerald-100',
                ],
                [
                    'metric' => 'Unit Cost Efficiency & Risk Compliance',
                    'weight' => '10%',
                    'target' => '10.00%',
                    'actual' => number_format(round(10.0 + ($kpiScore - 90) * 0.15, 2), 2).'%',
                    'status' => 'Exceeded',
                    'statusClass' => 'bg-emerald-50 text-forest-green border-emerald-100',
                ],
            ];

            $achievements = [
                "Mencapai realisasi KPI unit kerja sebesar {$kpiScore}% terhadap target tahunan.",
                'Mempertahankan tata kelola kepatuhan dan mitigasi risiko tanpa temuan audit.',
            ];

            $records[] = [
                'participant_id' => $participant->id,
                'year' => $year,
                'kpi_score' => $kpiScore,
                'target_score' => $targetScore,
                'benchmark_score' => $benchmarkScore,
                'performance_rating' => $rating,
                'kpi_breakdown' => json_encode($kpiBreakdown),
                'achievements' => json_encode($achievements),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        return $records;
    }
}
