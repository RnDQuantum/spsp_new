<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Participant;
use App\Models\PsychologicalTest;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PsychologicalTest>
 */
class PsychologicalTestFactory extends Factory
{
    protected $model = PsychologicalTest::class;

    public function definition(): array
    {
        $nilaiPq = fake()->randomFloat(2, 35, 85);

        // Determine performance level based on nilai_pq
        $isExcellent = $nilaiPq >= 80;
        $isGood = $nilaiPq >= 65 && $nilaiPq < 80;
        $isMedium = $nilaiPq >= 50 && $nilaiPq < 65;
        $isPoor = $nilaiPq >= 40 && $nilaiPq < 50;

        // Determine stress level
        $stressLevels = ['normal', 'ringan', 'sedang', 'berat', 'sangat berat'];
        $tingkatStres = $isExcellent ? 'normal' : ($isGood ? fake()->randomElement(['normal', 'ringan']) : ($isMedium ? fake()->randomElement(['ringan', 'sedang']) : ($isPoor ? fake()->randomElement(['sedang', 'berat']) :
            fake()->randomElement(['berat', 'sangat berat']))));

        return [
            'no_test' => fake()->numerify('##-#-#-##-###'),
            'username' => fake()->bothify('???##-###'),
            'validitas' => $this->generateValiditas($nilaiPq),
            'internal' => $this->generateInternal($nilaiPq),
            'interpersonal' => $this->generateInterpersonal($nilaiPq),
            'kap_kerja' => $this->generateKapKerja($nilaiPq),
            'klinik' => $this->generateKlinik($tingkatStres),
            'kesimpulan' => $this->generateKesimpulan($nilaiPq, $tingkatStres),
            'psikogram' => $this->generatePsikogram($nilaiPq),
            'nilai_pq' => $nilaiPq,
            'tingkat_stres' => $tingkatStres,
        ];
    }

    /**
     * Set for specific participant
     */
    public function forParticipant(Participant $participant): static
    {
        return $this->state(fn(array $attributes) => [
            'participant_id' => $participant->id,
            'event_id' => $participant->event_id,
        ]);
    }

    /**
     * State: High performance (PQ >= 80)
     */
    public function highPerformance(): static
    {
        return $this->state(function (array $attributes) {
            $nilaiPq = fake()->randomFloat(2, 80, 90);
            $tingkatStres = 'normal';

            return [
                'validitas' => 'Hasil tes sangat konsisten dan dapat diandalkan.',
                'internal' => $this->generateInternal($nilaiPq),
                'interpersonal' => $this->generateInterpersonal($nilaiPq),
                'kap_kerja' => $this->generateKapKerja($nilaiPq),
                'klinik' => 'Klien memiliki tingkat stres normal.',
                'kesimpulan' => $this->generateKesimpulan($nilaiPq, $tingkatStres),
                'psikogram' => $this->generatePsikogram($nilaiPq),
                'nilai_pq' => $nilaiPq,
                'tingkat_stres' => $tingkatStres,
            ];
        });
    }

    /**
     * State: Good performance (PQ 65-79)
     */
    public function goodPerformance(): static
    {
        return $this->state(function (array $attributes) {
            $nilaiPq = fake()->randomFloat(2, 65, 79);
            $tingkatStres = fake()->randomElement(['normal', 'ringan']);

            return [
                'validitas' => 'Hasil tes ini akurat dan valid untuk dijadikan dasar penilaian.',
                'internal' => $this->generateInternal($nilaiPq),
                'interpersonal' => $this->generateInterpersonal($nilaiPq),
                'kap_kerja' => $this->generateKapKerja($nilaiPq),
                'klinik' => $tingkatStres === 'normal' ? 'Klien memiliki tingkat stres normal.' : 'Klien memiliki tingkat stres ringan.',
                'kesimpulan' => $this->generateKesimpulan($nilaiPq, $tingkatStres),
                'psikogram' => $this->generatePsikogram($nilaiPq),
                'nilai_pq' => $nilaiPq,
                'tingkat_stres' => $tingkatStres,
            ];
        });
    }

    /**
     * State: Medium performance (PQ 50-64)
     */
    public function mediumPerformance(): static
    {
        return $this->state(function (array $attributes) {
            $nilaiPq = fake()->randomFloat(2, 50, 64);
            $tingkatStres = fake()->randomElement(['ringan', 'sedang']);

            return [
                'validitas' => 'Hasil tes cukup konsisten dan dapat dijadikan rujukan penilaian.',
                'internal' => $this->generateInternal($nilaiPq),
                'interpersonal' => $this->generateInterpersonal($nilaiPq),
                'kap_kerja' => $this->generateKapKerja($nilaiPq),
                'klinik' => $tingkatStres === 'ringan' ? 'Klien memiliki tingkat stres ringan.' : 'Klien mengalami stres sedang.',
                'kesimpulan' => $this->generateKesimpulan($nilaiPq, $tingkatStres),
                'psikogram' => $this->generatePsikogram($nilaiPq),
                'nilai_pq' => $nilaiPq,
                'tingkat_stres' => $tingkatStres,
            ];
        });
    }

    /**
     * State: Low performance (PQ < 50)
     */
    public function lowPerformance(): static
    {
        return $this->state(function (array $attributes) {
            $nilaiPq = fake()->randomFloat(2, 35, 49);
            $tingkatStres = fake()->randomElement(['berat', 'sangat berat']);

            return [
                'validitas' => 'Hasil tes cukup valid namun terdapat beberapa inkonsistensi minor.',
                'internal' => $this->generateInternal($nilaiPq),
                'interpersonal' => $this->generateInterpersonal($nilaiPq),
                'kap_kerja' => $this->generateKapKerja($nilaiPq),
                'klinik' => $tingkatStres === 'berat' ? 'Klien mungkin mengalami stres berat.' : 'Klien mengalami stres sangat berat.',
                'kesimpulan' => $this->generateKesimpulan($nilaiPq, $tingkatStres),
                'psikogram' => $this->generatePsikogram($nilaiPq),
                'nilai_pq' => $nilaiPq,
                'tingkat_stres' => $tingkatStres,
            ];
        });
    }

    private function generateValiditas(float $nilaiPq): string
    {
        if ($nilaiPq >= 80) {
            return 'Hasil tes sangat konsisten dan dapat diandalkan.';
        } elseif ($nilaiPq >= 65) {
            return 'Hasil tes ini akurat dan valid untuk dijadikan dasar penilaian.';
        } elseif ($nilaiPq >= 50) {
            return 'Hasil tes cukup konsisten dan dapat dijadikan rujukan penilaian.';
        } elseif ($nilaiPq >= 40) {
            return 'Hasil tes ini konsisten, akurat dan dapat dipercaya.';
        } else {
            return 'Hasil tes cukup valid namun terdapat beberapa inkonsistensi minor.';
        }
    }

    private function generateInternal(float $nilaiPq): string
    {
        $qualities = $this->getQualityLevel($nilaiPq);

        return "1. Kejujuran: {$qualities['high']}\n" .
            "2. Tanggung jawab: {$qualities['high']}\n" .
            "3. Ketaatan pada peraturan: {$qualities['medium']}\n" .
            "4. Percaya diri: {$qualities['medium']}\n" .
            "5. Kemampuan beradaptasi: {$qualities['medium']}\n" .
            "6. Kemampuan Mengendalikan emosi: {$qualities['medium']}\n" .
            "7. Kemandirian: {$qualities['low']}";
    }

    private function generateInterpersonal(float $nilaiPq): string
    {
        $qualities = $this->getQualityLevel($nilaiPq);
        $socialization = ucfirst($qualities['high']);

        return "1. Sosialisasi: {$socialization}\n" .
            "2. Hubungan dalam keluarga: {$qualities['medium']}\n" .
            "3. Kemampuan membina hubungan akrab: {$qualities['high']}\n" .
            "4. Kemampuan mempercayai orang lain: {$qualities['medium']}";
    }

    private function generateKapKerja(float $nilaiPq): string
    {
        $qualities = $this->getQualityLevel($nilaiPq);

        return "1. Kemampuan mengatasi kendala sikap (bekerja): {$qualities['high']}\n" .
            "2. Kemampuan mengatasi permasalahan: {$qualities['low']}\n" .
            "3. Kemampuan mengambil keputusan: {$qualities['medium']}\n" .
            "4. Motivasi: {$qualities['medium']}";
    }

    private function generateKlinik(string $tingkatStres): string
    {
        return match ($tingkatStres) {
            'normal' => 'Klien memiliki tingkat stres normal.',
            'ringan' => 'Klien memiliki tingkat stres ringan.',
            'sedang' => 'Klien mengalami stres sedang.',
            'berat' => 'Klien mungkin mengalami stres berat.',
            'sangat berat' => 'Klien mengalami stres sangat berat.',
            default => 'Klien memiliki tingkat stres normal.',
        };
    }

    private function generateKesimpulan(float $nilaiPq, string $tingkatStres): string
    {
        $overallFunction = $this->getOverallFunction($nilaiPq);
        $kapasitasKerja = $this->getKapasitasKerja($nilaiPq);
        $hubInterpersonal = $this->getHubInterpersonal($nilaiPq);
        $kemampuanMengembangkan = $this->getKemampuanMengembangkan($nilaiPq);

        $stresText = match ($tingkatStres) {
            'normal' => 'tingkat stres normal',
            'ringan' => 'stres ringan',
            'sedang' => 'stres sedang',
            'berat' => 'stres berat',
            'sangat berat' => 'stres sangat berat',
            default => 'tingkat stres normal',
        };

        return "1. Klien memiliki Fungsi Psikologik Menyeluruh (Overall Psychological Function) yang {$overallFunction}.(PQ={$nilaiPq})\n" .
            "2. Saat ini klien mengalami {$stresText}.\n" .
            "3. Klien memiliki kapasitas kerja yang {$kapasitasKerja}.\n" .
            "4. Hubungan interpersonal klien: {$hubInterpersonal}.\n" .
            "5. Kemampuan klien mengembangkan/merubah potensi diri: {$kemampuanMengembangkan}.";
    }

    private function generatePsikogram(float $nilaiPq): string
    {
        $baseScore = (int) $nilaiPq;
        $variance = fake()->numberBetween(-10, 10);

        $scores = [
            'K.Mengatasi Masalah' => max(10, min(90, $baseScore + $variance - 20)),
            'Kepemimpinan' => max(10, min(90, $baseScore + $variance - 15)),
            'Integritas' => max(50, min(90, $baseScore + $variance)),
            'Disiplin' => max(40, min(85, $baseScore + $variance - 5)),
            'Percaya Diri' => max(25, min(80, $baseScore + $variance - 10)),
            'Motivasi' => max(30, min(90, $baseScore + $variance)),
            'Kapasitas Kerja' => max(35, min(90, $baseScore + $variance - 10)),
            'Hub. Interpersonal' => max(25, min(85, $baseScore + $variance - 15)),
            'Membina Hubungn Akrab' => max(20, min(85, $baseScore + $variance - 5)),
            'Kemampuan Beradaptasi' => max(30, min(85, $baseScore + $variance - 10)),
            'K.Mengendalikan Emosi' => max(15, min(80, $baseScore + $variance - 15)),
            'K.Mengembangkan Diri' => max(20, min(75, $baseScore + $variance - 20)),
        ];

        $result = [];
        $index = 1;
        foreach ($scores as $key => $score) {
            $result[] = "{$index}. {$key} {$score}";
            $index++;
        }

        return implode("\n", $result);
    }

    private function getQualityLevel(float $nilaiPq): array
    {
        if ($nilaiPq >= 80) {
            return ['high' => 'sangat bagus', 'medium' => 'bagus', 'low' => 'bagus'];
        } elseif ($nilaiPq >= 65) {
            return ['high' => 'bagus', 'medium' => 'bagus', 'low' => 'cukup'];
        } elseif ($nilaiPq >= 50) {
            return ['high' => 'cukup', 'medium' => 'cukup', 'low' => 'kurang'];
        } elseif ($nilaiPq >= 40) {
            return ['high' => 'cukup', 'medium' => 'cukup', 'low' => 'sangat kurang'];
        } else {
            return ['high' => 'kurang', 'medium' => 'kurang', 'low' => 'sangat kurang'];
        }
    }

    private function getOverallFunction(float $nilaiPq): string
    {
        if ($nilaiPq >= 80) return 'sangat bagus';
        if ($nilaiPq >= 65) return 'bagus';
        if ($nilaiPq >= 50) return 'sedang';
        if ($nilaiPq >= 40) return 'sedang';
        return 'sangat kurang';
    }

    private function getKapasitasKerja(float $nilaiPq): string
    {
        if ($nilaiPq >= 80) return 'sangat baik';
        if ($nilaiPq >= 65) return 'baik';
        if ($nilaiPq >= 50) return 'sedang';
        if ($nilaiPq >= 40) return 'sedang';
        return 'kurang';
    }

    private function getHubInterpersonal(float $nilaiPq): string
    {
        if ($nilaiPq >= 80) return 'sangat bagus';
        if ($nilaiPq >= 65) return 'bagus';
        if ($nilaiPq >= 50) return 'cukup';
        if ($nilaiPq >= 40) return 'cukup';
        return 'sangat kurang';
    }

    private function getKemampuanMengembangkan(float $nilaiPq): string
    {
        if ($nilaiPq >= 80) return 'bagus';
        if ($nilaiPq >= 65) return 'cukup';
        if ($nilaiPq >= 50) return 'kurang';
        if ($nilaiPq >= 40) return 'kurang';
        return 'sangat kurang';
    }
}
