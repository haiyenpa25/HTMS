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
            $table->enum('data_scope', ['global', 'dept', 'group', 'self'])->default('dept')->after('scope');
        });

        Schema::table('user_department_features', function (Blueprint $table) {
            $table->enum('data_scope', ['global', 'dept', 'group', 'self'])->default('dept')->after('scope');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('feature_department', function (Blueprint $table) {
            $table->dropColumn('data_scope');
        });
        Schema::table('user_department_features', function (Blueprint $table) {
            $table->dropColumn('data_scope');
        });
    }
};
