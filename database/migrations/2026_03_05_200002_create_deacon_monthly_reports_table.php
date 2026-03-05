<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('deacon_monthly_reports', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('report_month');
            $table->unsignedSmallInteger('report_year');
            // YouTube stats
            $table->unsignedInteger('yt_subscribers')->default(0)->comment('Tổng đăng ký hiện tại');
            $table->unsignedInteger('yt_new_subscribers')->default(0)->comment('Đăng ký mới trong tháng');
            $table->unsignedInteger('yt_views')->default(0)->comment('Số lượt xem');
            $table->unsignedInteger('yt_watch_hours')->default(0)->comment('Số giờ xem');
            // Thông báo / đề nghị / kế hoạch
            $table->text('announcements')->nullable()->comment('Thông báo - Đề nghị và kế hoạch khác');
            // Tổng kết
            $table->text('summary_notes')->nullable();
            $table->enum('status', ['draft', 'submitted', 'approved'])->default('draft');
            $table->foreignId('submitted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['report_month', 'report_year']); // 1 report per month
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deacon_monthly_reports');
    }
};
