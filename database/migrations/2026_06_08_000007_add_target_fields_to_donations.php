<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('donations', function (Blueprint $table) {
            // Loại nguồn dâng
            $table->string('source', 50)->default('personal_giving')->after('notes')
                ->comment('sunday_offering, personal_giving, special_giving');
            // Target: dâng cho HT hay ban ngành cụ thể
            $table->string('target_type', 30)->default('church')->after('source')
                ->comment('church, department, edu_class');
            $table->unsignedBigInteger('target_id')->nullable()->after('target_type')
                ->comment('ID của department hoặc edu_class nếu target_type != church');
        });
    }

    public function down(): void
    {
        Schema::table('donations', function (Blueprint $table) {
            $table->dropColumn(['source', 'target_type', 'target_id']);
        });
    }
};
