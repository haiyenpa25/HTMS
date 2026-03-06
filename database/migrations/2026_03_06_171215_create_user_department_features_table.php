<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_department_features', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('department_id')->constrained()->cascadeOnDelete();
            $table->foreignId('feature_id')->constrained()->cascadeOnDelete();
            $table->string('dept_type')->default('activities'); // activities|ministry|leadership
            $table->boolean('is_enabled')->default(false);
            $table->enum('access_level', ['view', 'manage'])->default('view');
            $table->timestamps();

            // Đảm bảo 1 user chỉ có 1 row per (dept, feature)
            $table->unique(['user_id', 'department_id', 'feature_id'], 'udf_unique');

            // Index tối ưu cho middleware check
            $table->index(['user_id', 'department_id', 'is_enabled'], 'udf_access_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_department_features');
    }
};
