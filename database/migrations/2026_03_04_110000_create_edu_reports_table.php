<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('edu_reports', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('report_month');
            $table->unsignedSmallInteger('report_year');
            $table->string('reporter_name')->nullable();    // Người báo cáo
            $table->enum('status', ['draft', 'submitted', 'approved'])->default('draft');
            $table->text('evaluation')->nullable();         // Nhận xét chung
            $table->text('highlights')->nullable();         // Điểm nổi bật / thành tựu
            $table->text('challenges')->nullable();         // Khó khăn / thách thức
            $table->text('request')->nullable();            // Yêu cầu lên ban quản nhiệm
            $table->text('proposals')->nullable();          // Đề nghị / Kế hoạch tháng tới
            $table->text('activities_notes')->nullable();   // Ghi chú hoạt động khác
            $table->unique(['report_month', 'report_year']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('edu_reports');
    }
};
