<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('meetings', function (Blueprint $table) {
            // Đánh dấu buổi nghỉ/hủy → không tính vào TB tham dự / TB câu gốc
            $table->boolean('is_cancelled')->default(false)->after('attendance_marked');
            $table->string('cancelled_note')->nullable()->after('is_cancelled');
        });
    }

    public function down(): void
    {
        Schema::table('meetings', function (Blueprint $table) {
            $table->dropColumn(['is_cancelled', 'cancelled_note']);
        });
    }
};
