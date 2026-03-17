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
        Schema::table('feature_department', function (Blueprint $table) {
            if (!Schema::hasColumn('feature_department', 'scope')) {
                $table->enum('scope', ['global', 'dept', 'group', 'self'])->default('dept')->after('department_id');
            }
        });

        Schema::table('user_department_features', function (Blueprint $table) {
            if (!Schema::hasColumn('user_department_features', 'scope')) {
                $table->enum('scope', ['global', 'dept', 'group', 'self'])->default('dept')->after('feature_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('feature_department', function (Blueprint $table) {
            $table->dropColumn('scope');
        });
        Schema::table('user_department_features', function (Blueprint $table) {
            $table->dropColumn('scope');
        });
    }
};
