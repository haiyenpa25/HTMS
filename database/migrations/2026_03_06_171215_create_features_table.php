<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('features', function (Blueprint $table) {
            $table->id();
            $table->string('name');                // Tên hiển thị: "Điểm Danh"
            $table->string('slug')->unique();       // Key: "attendance"
            $table->string('icon')->nullable();     // Emoji hoặc icon class: "✅"
            $table->string('description')->nullable();
            $table->string('portal_type')->default('activities'); // activities|ministry|education
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('features');
    }
};
