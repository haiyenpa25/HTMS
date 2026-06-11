<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('secretary_monthly_notes', function (Blueprint $table) {
            $table->id();
            $table->tinyInteger('month')->unsigned();   // 1-12
            $table->smallInteger('year')->unsigned();   // 2024-2099
            $table->text('announcements')->nullable()   ->comment('Thông báo / Đề nghị / Góp ý tháng');
            $table->text('next_plan')->nullable()       ->comment('Kế hoạch sự kiện tháng tới');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['month', 'year'], 'secretary_notes_month_year_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('secretary_monthly_notes');
    }
};
