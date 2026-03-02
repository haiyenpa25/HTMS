<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $permissions = [
            'view_finance',
            'create_finance',
            'manage_finance',
            'approve_finance'
        ];

        foreach ($permissions as $p) {
            Permission::firstOrCreate(['name' => $p, 'guard_name' => 'web']);
        }

        // Auto-assign to Super_Admin & Pastor
        $roles_to_grant_all = ['Super_Admin', 'Pastor'];
        foreach ($roles_to_grant_all as $roleName) {
            $role = Role::where('name', $roleName)->where('guard_name', 'web')->first();
            if ($role) {
                $role->givePermissionTo($permissions);
            }
        }

        // Auto-assign view/create to Dept/Team leads
        $roles_to_grant_basic = ['Department_Lead', 'Team_Lead'];
        foreach ($roles_to_grant_basic as $roleName) {
            $role = Role::where('name', $roleName)->where('guard_name', 'web')->first();
            if ($role) {
                $role->givePermissionTo(['view_finance', 'create_finance']);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $permissions = [
            'view_finance',
            'create_finance',
            'manage_finance',
            'approve_finance'
        ];

        // Revoke from all roles first
        $roles = Role::all();
        foreach ($roles as $role) {
            foreach ($permissions as $p) {
                if ($role->hasPermissionTo($p)) {
                    $role->revokePermissionTo($p);
                }
            }
        }

        // Delete permissions
        foreach ($permissions as $p) {
            Permission::where('name', $p)->where('guard_name', 'web')->delete();
        }
    }
};
