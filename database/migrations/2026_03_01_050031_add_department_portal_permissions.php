<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $permissions = [
            'access_department_portal',
        ];

        foreach ($permissions as $permission) {
            \Spatie\Permission\Models\Permission::firstOrCreate(['name' => $permission]);
        }

        $rolesToAssign = ['Pastor', 'Super_Admin', 'Department_Lead', 'Team_Lead', 'Member'];

        foreach ($rolesToAssign as $roleName) {
            $role = \Spatie\Permission\Models\Role::where('name', $roleName)->first();
            if ($role) {
                $role->givePermissionTo($permissions);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $permissions = [
            'access_department_portal',
        ];

        foreach ($permissions as $permission) {
            $perm = \Spatie\Permission\Models\Permission::where('name', $permission)->first();
            if ($perm) {
                $perm->delete();
            }
        }
    }
};
