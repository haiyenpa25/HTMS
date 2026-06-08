<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('deacon_attendance_records', function (Blueprint $table) {
            // Phân tách giới tính trong tổng điểm danh
            $table->unsignedSmallInteger('total_male')->default(0)->after('total_present');
            $table->unsignedSmallInteger('total_female')->default(0)->after('total_male');
            // Khách (không thuộc ban nào)
            $table->unsignedSmallInteger('guests_count')->default(0)->after('total_children');
            // Điểm danh từng ban (ban trưởng báo) — JSON: {"dept_id": count, ...}
            $table->json('dept_breakdown')->nullable()->after('guests_count');
            // Thời điểm ghi nhận chính xác
            $table->timestamp('recorded_at')->nullable()->after('dept_breakdown');
        });
    }

    public function down(): void
    {
        Schema::table('deacon_attendance_records', function (Blueprint $table) {
            $table->dropColumn(['total_male', 'total_female', 'guests_count', 'dept_breakdown', 'recorded_at']);
        });
    }
};
