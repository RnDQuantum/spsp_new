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

        // 1. Seed roles and permissions first
        $this->command->info('🔐 Seeding roles and permissions...');
        $this->call([
            RolesAndPermissionsSeeder::class,
        ]);

        // 2. Create default admin user and assign role
        $this->command->info('👤 Creating admin user...');
        $adminUser = User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'is_active' => true,
        ]);
        $adminUser->assignRole('admin');
        $this->command->info('✅ Admin user created with admin role');

        // 3. Seed master data (institutions, templates, aspects, sub-aspects)
        $this->command->info('📋 Seeding master data...');
        $this->call([
            InstitutionSeeder::class,
            ClientSeeder::class,
            AssessmentTemplateSeeder::class,
            MasterDataSeeder::class,
        ]);

        // 4. Seed participants with dynamic assessment seeder
        $this->command->info('🎯 Seeding participants with assessments...');
        $this->call([
            DynamicAssessmentSeeder::class,
        ]);

        // 5. Seed Interpretation Templates
        $this->command->info('📋 Seeding interpretation templates...');
        $this->call([
            DetailedInterpretationTemplateSeeder::class,
        ]);

        $this->command->info('✅ Database seeding completed successfully!');
    }
}
