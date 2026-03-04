<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('edu_classes', function (Blueprint $table) {
            // Loại lớp — ảnh hướng tính năng hiển thị trong Session.vue
            // sunday_school: Lớp Trường Chúa Nhật (điểm danh + tiền dâng)
            // gospel:        Lớp Giáo Lý / Phước Âm (điểm danh only)
            // bible_quiz:    Trắc Nghiệm Kinh Thánh (điểm danh + quiz score + bảng điểm)
            $table->enum('class_type', ['sunday_school', 'gospel', 'bible_quiz'])
                  ->default('sunday_school')
                  ->after('is_active');
        });
    }

    public function down(): void
    {
        Schema::table('edu_classes', function (Blueprint $table) {
            $table->dropColumn('class_type');
        });
    }
};
