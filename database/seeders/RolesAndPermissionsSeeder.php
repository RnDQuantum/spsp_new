<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        $permissions = [
            'view all institutions',  // Only for admin
            'manage users',            // Only for admin
            'manage institutions',     // Only for admin
            'analyze standards',       // For dynamic standard analysis
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Create roles and assign permissions

        // 1. Admin (global access to all institutions)
        $admin = Role::create(['name' => 'admin']);
        $admin->givePermissionTo(Permission::all());

        // 2. Client (access only their own institution data)
        $client = Role::create(['name' => 'client']);
        $client->givePermissionTo([
            'analyze standards',
        ]);
    }
}
