<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create default user
        User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
        ]);

        // Seed master data and sample data
        $this->call([
            InstitutionSeeder::class,
            AssessmentTemplateSeeder::class,
            MasterDataSeeder::class,
            SampleDataSeeder::class,
        ]);
    }
}
