<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('social_platform_stats', function (Blueprint $table) {
            $table->id();
            // 'youtube', 'facebook', 'zalo', 'tiktok', 'instagram'
            $table->string('platform', 50);
            // 'subscribers', 'followers', 'members', 'views', 'likes'
            $table->string('metric', 50);
            $table->unsignedInteger('count')->default(0);
            $table->date('recorded_date');  // Ngày ghi nhận (thường 1 lần/tuần CN)
            $table->unsignedBigInteger('recorded_by')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['platform', 'recorded_date'], 'idx_platform_date');

            // Không trùng platform+metric trong cùng 1 ngày
            $table->unique(['platform', 'metric', 'recorded_date'], 'uniq_platform_metric_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('social_platform_stats');
    }
};
