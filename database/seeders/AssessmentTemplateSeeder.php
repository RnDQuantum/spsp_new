<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\AssessmentTemplate;

class AssessmentTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $templates = [
            [
                'code' => 'SPSP2024',
                'name' => 'Standard SPSP 2024',
                'description' => 'Template standar untuk assessment SPSP tahun 2024',
            ],
            [
                'code' => 'SPSP2025',
                'name' => 'Standard SPSP 2025',
                'description' => 'Template standar untuk assessment SPSP tahun 2025',
            ],
        ];

        foreach ($templates as $template) {
            AssessmentTemplate::create($template);
        }
    }
}
