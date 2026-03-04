<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('edu_sessions', function (Blueprint $table) {
            // Giáo viên phụ trách buổi học (có thể khác giáo viên cố định của lớp)
            $table->foreignId('teacher_id')
                  ->nullable()
                  ->after('notes')
                  ->constrained('members')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('edu_sessions', function (Blueprint $table) {
            $table->dropForeign(['teacher_id']);
            $table->dropColumn('teacher_id');
        });
    }
};
