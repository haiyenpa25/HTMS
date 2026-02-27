<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            // User Management
            'view users',
            'create users',
            'edit users',
            'delete users',

            // Role Management
            'view roles',
            'edit roles',

            // Member (Tín hữu) Management
            'view members',
            'create members',
            'edit members',
            'delete members',

            // Department (Ban ngành) Management
            'view departments',
            'create departments',
            'edit departments',
            'delete departments',

            // Sensitive / Pastoral info
            'view sensitive_info',
            'edit sensitive_info',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }
    }
}
