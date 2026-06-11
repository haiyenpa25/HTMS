<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('deacon_attendance_records', function (Blueprint $table) {
            // Số người xem YouTube Live trong buổi nhóm chính (CN)
            $table->unsignedInteger('youtube_live_count')->nullable()->after('incident_note');
        });
    }

    public function down(): void
    {
        Schema::table('deacon_attendance_records', function (Blueprint $table) {
            $table->dropColumn('youtube_live_count');
        });
    }
};
