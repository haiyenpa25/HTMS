<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('edu_sessions', function (Blueprint $table) {
            // Loạt bài (series) — VD: "Thư Hê-bơ-rơ", "Sáng Thế Ký"
            $table->string('lesson_series')->nullable()->after('topic');
        });
    }

    public function down(): void
    {
        Schema::table('edu_sessions', function (Blueprint $table) {
            $table->dropColumn('lesson_series');
        });
    }
};
