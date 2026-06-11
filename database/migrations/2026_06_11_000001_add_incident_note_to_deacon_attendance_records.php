<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('deacon_attendance_records', function (Blueprint $table) {
            // Ghi chú sự cố riêng cho từng buổi nhóm (tách khỏi 'notes' chung)
            $table->text('incident_note')->nullable()->after('notes')
                ->comment('Ghi chú sự cố xảy ra trong buổi nhóm');
        });
    }

    public function down(): void
    {
        Schema::table('deacon_attendance_records', function (Blueprint $table) {
            $table->dropColumn('incident_note');
        });
    }
};
