<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\CategoryType;
use App\Models\Interpretation;
use App\Models\Participant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Interpretation>
 */
class InterpretationFactory extends Factory
{
    protected $model = Interpretation::class;

    private static array $potensiTexts = [
        'high' => [
            'Individu memiliki potensi yang sangat baik dengan kemampuan kognitif dan sikap kerja yang menonjol. Mampu beradaptasi dengan baik dalam berbagai situasi kerja dan menunjukkan inisiatif yang tinggi.',
            'Menunjukkan potensi luar biasa dalam aspek kecerdasan dan kepribadian. Memiliki kapasitas yang sangat baik untuk pembelajaran dan pengembangan diri secara berkelanjutan.',
            'Potensi individu sangat menonjol, terutama dalam kemampuan berpikir analitis dan problem solving. Menampilkan sikap kerja yang profesional dan konsisten dalam berbagai kondisi.',
        ],
        'medium' => [
            'Memiliki kepekaan yang cukup memadai dalam memahami kebutuhan orang-orang yang ada di sekitarnya. Menunjukkan potensi yang memadai untuk menjalankan tugas dengan efektif.',
            'Individu memiliki potensi yang sesuai dengan tuntutan pekerjaan. Menampilkan kemampuan kognitif dan sikap kerja yang cukup baik untuk mendukung pelaksanaan tugas.',
            'Potensi yang ditampilkan berada pada level yang memadai. Mampu memahami dan menyelesaikan tugas-tugas rutin dengan baik.',
        ],
        'low' => [
            'Memerlukan pengembangan lebih lanjut pada aspek potensi, terutama dalam hal kemampuan analisis dan pemecahan masalah. Perlu pendampingan untuk meningkatkan efektivitas kerja.',
            'Potensi individu perlu dikembangkan lebih lanjut, khususnya dalam aspek kecerdasan dan sikap kerja. Membutuhkan pelatihan dan bimbingan untuk mencapai standar yang diharapkan.',
            'Individu memerlukan perhatian khusus dalam pengembangan potensi kerja. Diperlukan program pengembangan yang terstruktur untuk meningkatkan kapasitas.',
        ],
    ];

    private static array $kompetensiTexts = [
        'high' => [
            'Menunjukkan kompetensi yang sangat baik dalam semua aspek pekerjaan. Konsisten dalam menampilkan perilaku kerja yang sesuai dengan standar organisasi dan mampu menjadi role model bagi rekan kerja.',
            'Kompetensi yang ditampilkan sangat menonjol dan melampaui ekspektasi. Individu mampu mengintegrasikan berbagai kompetensi dengan baik dalam pelaksanaan tugas.',
            'Individu menampilkan kompetensi yang sangat kompeten dalam seluruh aspek. Mampu bekerja secara mandiri dan berkontribusi positif terhadap tim.',
        ],
        'medium' => [
            'Dalam bekerja, individu cukup mampu mengelola pekerjaan yang menjadi tanggung jawabnya sesuai dengan prioritas penyelesaian masalah. Menampilkan kompetensi yang memadai sesuai standar yang ditetapkan.',
            'Kompetensi yang ditampilkan berada pada level yang sesuai dengan tuntutan jabatan. Individu mampu menjalankan tugas-tugas dengan baik dan berkontribusi pada pencapaian tim.',
            'Menunjukkan kompetensi yang memadai dalam aspek-aspek kunci pekerjaan. Mampu beradaptasi dengan tuntutan pekerjaan yang bervariasi.',
        ],
        'low' => [
            'Perlu meningkatkan kompetensi pada beberapa aspek pekerjaan, terutama dalam hal kerjasama dan orientasi pada hasil. Membutuhkan pendampingan untuk mencapai standar kompetensi yang diharapkan.',
            'Kompetensi yang ditampilkan masih perlu pengembangan lebih lanjut. Diperlukan pelatihan khusus untuk meningkatkan keterampilan dalam aspek-aspek kunci.',
            'Individu memerlukan program pengembangan kompetensi yang terstruktur. Perlu bimbingan intensif untuk meningkatkan performa kerja.',
        ],
    ];

    public function definition(): array
    {
        return [
            'interpretation_text' => fake()->paragraph(3),
        ];
    }

    /**
     * Set for specific participant
     */
    public function forParticipant(Participant $participant): static
    {
        return $this->state(fn (array $attributes) => [
            'participant_id' => $participant->id,
            'event_id' => $participant->event_id,
        ]);
    }

    /**
     * Set for specific category type
     */
    public function forCategoryType(CategoryType $categoryType): static
    {
        return $this->state(fn (array $attributes) => [
            'category_type_id' => $categoryType->id,
        ]);
    }

    /**
     * For Potensi category
     */
    public function potensi(string $performanceLevel = 'medium'): static
    {
        return $this->state(function (array $attributes) use ($performanceLevel) {
            $texts = self::$potensiTexts[$performanceLevel] ?? self::$potensiTexts['medium'];

            return [
                'interpretation_text' => fake()->randomElement($texts),
            ];
        });
    }

    /**
     * For Kompetensi category
     */
    public function kompetensi(string $performanceLevel = 'medium'): static
    {
        return $this->state(function (array $attributes) use ($performanceLevel) {
            $texts = self::$kompetensiTexts[$performanceLevel] ?? self::$kompetensiTexts['medium'];

            return [
                'interpretation_text' => fake()->randomElement($texts),
            ];
        });
    }
}
