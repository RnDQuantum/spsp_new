<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * Usage:
     *   php artisan migrate:fresh --seed
     *
     * This will:
     *   1. Drop all tables and re-run migrations
     *   2. Seed master data (institutions, templates, aspects, sub-aspects)
     *   3. Seed 100 participants with calculated assessments
     */
    public function run(): void
    {
        $this->command->info('🌱 Starting database seeding...');

        // 1. Create default admin user
        $this->command->info('👤 Creating admin user...');
        User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
        ]);

        // 2. Seed master data (institutions, templates, aspects, sub-aspects)
        $this->command->info('📋 Seeding master data...');
        $this->call([
            InstitutionSeeder::class,
            AssessmentTemplateSeeder::class,
            MasterDataSeeder::class,
        ]);

        // 3. Seed participants with dynamic assessment seeder
        $this->command->info('🎯 Seeding participants with assessments...');
        $this->call([
            DynamicAssessmentSeeder::class,
        ]);

        // 4. Seed Interpretation Templates
        $this->command->info('📋 Seeding interpretation templates...');
        $this->call([
            DetailedInterpretationTemplateSeeder::class,
        ]);

        $this->command->info('✅ Database seeding completed successfully!');
    }
}
