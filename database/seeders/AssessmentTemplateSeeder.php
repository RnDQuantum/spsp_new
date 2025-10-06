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
                'code' => 'p3k_standard_2025',
                'name' => 'Standar Asesmen P3K 2025',
                'description' => 'Template standar untuk asesmen P3K (Pegawai Pemerintah dengan Perjanjian Kerja) tahun 2025. Menggunakan 2 kategori utama: Potensi (40%) dan Kompetensi (60%).',
            ],
            [
                'code' => 'cpns_jpt_pratama',
                'name' => 'Standar Asesmen CPNS JPT Pratama',
                'description' => 'Template standar untuk asesmen CPNS Jabatan Pimpinan Tinggi Pratama (eselon II).',
            ],
            [
                'code' => 'cpns_administrator',
                'name' => 'Standar Asesmen CPNS Administrator',
                'description' => 'Template standar untuk asesmen CPNS Jabatan Administrator (eselon III).',
            ],
        ];

        foreach ($templates as $template) {
            AssessmentTemplate::create($template);
        }
    }
}
