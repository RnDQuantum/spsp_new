<?php

namespace Database\Factories;

use App\Models\AssessmentTemplate;
use App\Models\CategoryType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Aspect>
 */
class AspectFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $template = AssessmentTemplate::factory()->create();
        $category = CategoryType::factory()->create(['template_id' => $template->id]);

        return [
            'template_id' => $template->id,
            'category_type_id' => $category->id,
            'code' => strtolower(fake()->unique()->bothify('asp_####')),
            'name' => fake()->words(3, true),
            'description' => fake()->sentence(),
            'weight_percentage' => fake()->numberBetween(10, 100),
            'standard_rating' => fake()->randomFloat(1, 1, 5),
            'order' => fake()->numberBetween(1, 10),
        ];
    }
}
