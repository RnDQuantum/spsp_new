<?php

namespace Database\Seeders;

use App\Models\InstitutionCategory;
use Illuminate\Database\Seeder;

class InstitutionCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'code' => 'kementerian',
                'name' => 'Kementerian',
                'description' => 'Kementerian dan Lembaga Pemerintah',
                'icon' => 'fa-building-flag',
                'color' => '#3B82F6',
                'order' => 1,
                'is_active' => true,
            ],
            [
                'code' => 'bumn',
                'name' => 'BUMN',
                'description' => 'Badan Usaha Milik Negara',
                'icon' => 'fa-briefcase',
                'color' => '#10B981',
                'order' => 2,
                'is_active' => true,
            ],
            [
                'code' => 'swasta',
                'name' => 'Swasta',
                'description' => 'Perusahaan Swasta',
                'icon' => 'fa-building',
                'color' => '#F59E0B',
                'order' => 3,
                'is_active' => true,
            ],
            [
                'code' => 'pendidikan',
                'name' => 'Pendidikan',
                'description' => 'Institusi Pendidikan',
                'icon' => 'fa-graduation-cap',
                'color' => '#8B5CF6',
                'order' => 4,
                'is_active' => true,
            ],
            [
                'code' => 'kesehatan',
                'name' => 'Kesehatan',
                'description' => 'Rumah Sakit dan Fasilitas Kesehatan',
                'icon' => 'fa-hospital',
                'color' => '#EF4444',
                'order' => 5,
                'is_active' => true,
            ],
            [
                'code' => 'teknologi',
                'name' => 'Teknologi',
                'description' => 'Perusahaan Teknologi dan IT',
                'icon' => 'fa-laptop-code',
                'color' => '#06B6D4',
                'order' => 6,
                'is_active' => true,
            ],
            [
                'code' => 'keuangan',
                'name' => 'Keuangan',
                'description' => 'Bank dan Lembaga Keuangan',
                'icon' => 'fa-landmark',
                'color' => '#14B8A6',
                'order' => 7,
                'is_active' => true,
            ],
        ];

        foreach ($categories as $category) {
            InstitutionCategory::create($category);
        }
    }
}
