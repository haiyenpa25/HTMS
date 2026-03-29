<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('meeting_attendance_summaries', function (Blueprint $table) {
            $table->unsignedInteger('memory_verse_count')->default(0)->after('manual_count')
                ->comment('Số người thuộc câu gốc trong buổi nhóm');
        });
    }

    public function down(): void
    {
        Schema::table('meeting_attendance_summaries', function (Blueprint $table) {
            $table->dropColumn('memory_verse_count');
        });
    }
};
