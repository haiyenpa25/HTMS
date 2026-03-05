<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('deacon_attendance_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('meeting_id')->constrained()->cascadeOnDelete();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            // Số liệu điểm danh
            $table->integer('total_present')->default(0)->comment('Tổng số người hiện diện (Thư Ký nhập)');
            $table->integer('total_online')->default(0)->comment('Xem qua kênh Youtube');
            $table->integer('total_children')->default(0)->comment('Trẻ em');
            $table->integer('total_visitors')->default(0)->comment('Khách');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique('meeting_id'); // 1 record per meeting
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deacon_attendance_records');
    }
};
