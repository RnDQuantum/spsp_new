<?php

namespace Database\Seeders;

use App\Models\Institution;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ClientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $institutions = Institution::all();

        foreach ($institutions as $institution) {
            User::create([
                'name' => 'Client ' . $institution->name,
                'email' => $institution->code . '@example.com',
                'password' => 'password',
                'institution_id' => $institution->id,
                'is_active' => true,
            ]);
        }
    }
}
