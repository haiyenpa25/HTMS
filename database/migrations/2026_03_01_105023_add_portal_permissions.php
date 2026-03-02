<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Don't create permissions here to avoid 'Role does not exist' issues during fresh migration.
        // All permissions are seeded in OrgStructureSeeder
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Handled in seeder / fresh migration
    }
};
