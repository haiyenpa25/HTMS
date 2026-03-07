<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Make block_type nullable and add scope column for 3-scope feature assignment.
     * 
     * IMPORTANT: MySQL requires dropping foreign keys before dropping index.
     * SQLite (local) doesn't have this restriction, so the migration ran fine locally.
     * 
     * 3-scope model:
     *  - Global:   block_type IS NULL, department_id IS NULL → shown in ALL portals
     *  - Block:    block_type = 'activities', department_id IS NULL → shown for all depts in block
     *  - Specific: block_type = 'activities', department_id = X → shown only for that dept
     */
    public function up(): void
    {
        $driver = DB::getDriverName();

        Schema::table('feature_department', function (Blueprint $table) use ($driver) {
            // 1. Drop the unique constraint (and FK constraints first on MySQL)
            if ($driver === 'mysql') {
                $table->dropForeign(['feature_id']);
                $table->dropForeign(['department_id']);
            }

            $table->dropUnique(['feature_id', 'block_type', 'department_id']);

            // 2. Make block_type nullable (null = global scope)
            $table->string('block_type')->nullable()->change();

            // 3. Add scope column if not already there
            if (!Schema::hasColumn('feature_department', 'scope')) {
                $table->string('scope')->nullable()->after('block_type'); // global|block|specific
            }

            // 4. Re-add FK constraints
            if ($driver === 'mysql') {
                $table->foreign('feature_id')->references('id')->on('features')->cascadeOnDelete();
                $table->foreign('department_id')->references('id')->on('departments')->cascadeOnDelete();
            }

            // 5. Re-add unique index (now handles nullable block_type)
            $table->unique(['feature_id', 'block_type', 'department_id']);
        });
    }

    public function down(): void
    {
        $driver = DB::getDriverName();

        Schema::table('feature_department', function (Blueprint $table) use ($driver) {
            if ($driver === 'mysql') {
                $table->dropForeign(['feature_id']);
                $table->dropForeign(['department_id']);
            }

            $table->dropUnique(['feature_id', 'block_type', 'department_id']);
            $table->string('block_type')->nullable(false)->change();
            
            if (Schema::hasColumn('feature_department', 'scope')) {
                $table->dropColumn('scope');
            }

            if ($driver === 'mysql') {
                $table->foreign('feature_id')->references('id')->on('features')->cascadeOnDelete();
                $table->foreign('department_id')->references('id')->on('departments')->cascadeOnDelete();
            }

            $table->unique(['feature_id', 'block_type', 'department_id']);
        });
    }
};
