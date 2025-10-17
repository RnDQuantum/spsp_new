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
            // LEVEL: Staff / Entry Level (Fresh Graduate, Junior)
            [
                'code' => 'staff_standard_v1',
                'name' => 'Standar Asesmen Staff',
                'description' => 'Template untuk posisi staff/staf (entry level). Fokus balanced antara potensi dan kompetensi dasar. Potensi 50%, Kompetensi 50%.',
            ],

            // LEVEL: Supervisor / Team Leader (Koordinator, Penyelia)
            [
                'code' => 'supervisor_standard_v1',
                'name' => 'Standar Asesmen Supervisor',
                'description' => 'Template untuk posisi supervisor/penyelia/team leader. Lebih menekankan kompetensi manajerial dan kepemimpinan. Potensi 30%, Kompetensi 70%.',
            ],

            // LEVEL: Manager / Administrator (Middle Management)
            [
                'code' => 'manager_standard_v1',
                'name' => 'Standar Asesmen Manager',
                'description' => 'Template untuk posisi manager/administrator (middle management). Standar balanced dengan penekanan pada kompetensi strategis. Potensi 40%, Kompetensi 60%.',
            ],

            // LEVEL: Professional / Specialist (Ahli, Analis Senior)
            [
                'code' => 'professional_standard_v1',
                'name' => 'Standar Asesmen Professional',
                'description' => 'Template untuk posisi professional/specialist/expert. Fokus pada keahlian teknis dan kompetensi profesional. Potensi 45%, Kompetensi 55%.',
            ],

            // LEGACY: Keep existing P3K Standard
            [
                'code' => 'p3k_standard_2025',
                'name' => 'Standar Asesmen P3K 2025',
                'description' => 'Template standar untuk asesmen P3K (Pegawai Pemerintah dengan Perjanjian Kerja) tahun 2025. Template legacy dengan bobot standar Potensi 40%, Kompetensi 60%.',
            ],
        ];

        foreach ($templates as $template) {
            AssessmentTemplate::create($template);
        }
    }
}
