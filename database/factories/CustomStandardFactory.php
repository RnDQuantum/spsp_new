<?php

namespace Database\Factories;

use App\Models\CustomStandard;
use App\Models\Institution;
use App\Models\AssessmentTemplate;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CustomStandard>
 */
class CustomStandardFactory extends Factory
{
    protected $model = CustomStandard::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'institution_id' => Institution::factory(),
            'template_id' => AssessmentTemplate::factory(),
            'code' => strtoupper($this->faker->unique()->lexify('CUSTOM-???-????')),
            'name' => $this->faker->sentence(4),
            'description' => $this->faker->optional()->sentence(),
            'category_weights' => [
                'potensi' => 25,
                'kompetensi' => 75,
            ],
            'aspect_configs' => [],
            'sub_aspect_configs' => [],
            'is_active' => true,
            'created_by' => null,
        ];
    }
}
