<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('edu_sessions', function (Blueprint $table) {
            // Chế độ điểm danh:
            // 'quick'   = Giáo viên chỉ nhập số tổng (không cần danh sách từng người)
            // 'checkin' = Điểm danh từng học viên (lưu vào edu_session_records)
            $table->enum('attendance_mode', ['quick', 'checkin'])->default('checkin')->after('notes');

            // Số học viên có mặt khi dùng quick mode (nhập tổng)
            $table->unsignedSmallInteger('total_present')->nullable()->after('attendance_mode');

            // Số học viên vắng khi dùng quick mode
            $table->unsignedSmallInteger('total_absent')->nullable()->after('total_present');
        });
    }

    public function down(): void
    {
        Schema::table('edu_sessions', function (Blueprint $table) {
            $table->dropColumn(['attendance_mode', 'total_present', 'total_absent']);
        });
    }
};
