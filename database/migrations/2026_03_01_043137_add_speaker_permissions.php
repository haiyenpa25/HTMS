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
            'view_speakers',
            'manage_speakers',
        ];

        foreach ($permissions as $permission) {
            \Spatie\Permission\Models\Permission::firstOrCreate(['name' => $permission]);
        }

        $superAdmin = \Spatie\Permission\Models\Role::where('name', 'Super_Admin')->first();
        if ($superAdmin) {
            $superAdmin->givePermissionTo($permissions);
        }

        $pastor = \Spatie\Permission\Models\Role::where('name', 'Pastor')->first();
        if ($pastor) {
            $pastor->givePermissionTo($permissions);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $permissions = [
            'view_speakers',
            'manage_speakers',
        ];

        foreach ($permissions as $permission) {
            $perm = \Spatie\Permission\Models\Permission::where('name', $permission)->first();
            if ($perm) {
                $perm->delete();
            }
        }
    }
};
