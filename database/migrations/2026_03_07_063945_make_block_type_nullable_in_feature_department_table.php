<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Make block_type nullable to support global scope
     * (scope = 'global': block_type = null AND department_id = null)
     *
     * 3-scope model:
     *  - Global:   block_type IS NULL, department_id IS NULL → feature shown in ALL portals
     *  - Block:    block_type = 'activities', department_id IS NULL → shown for all depts in block
     *  - Specific: block_type = 'activities', department_id = X → shown only for that dept
     */
    public function up(): void
    {
        Schema::table('feature_department', function (Blueprint $table) {
            // Drop the old unique constraint first
            $table->dropUnique(['feature_id', 'block_type', 'department_id']);
            
            // Make block_type nullable
            $table->string('block_type')->nullable()->change();
            
            // Re-add scope column for clarity
            $table->string('scope')->nullable()->after('block_type'); // 'global', 'block', 'specific'
            
            // Recreate unique with nullable-safe approach
            $table->unique(['feature_id', 'block_type', 'department_id']);
        });
    }

    public function down(): void
    {
        Schema::table('feature_department', function (Blueprint $table) {
            $table->dropUnique(['feature_id', 'block_type', 'department_id']);
            $table->string('block_type')->nullable(false)->change();
            $table->dropColumn('scope');
            $table->unique(['feature_id', 'block_type', 'department_id']);
        });
    }
};
