<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('deacon_report_incidents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('deacon_report_id')->constrained('deacon_monthly_reports')->cascadeOnDelete();
            $table->string('week_label')->comment('Tuần 1, Tuần 2, ...');
            $table->text('incident_description')->nullable()->comment('Mô tả sự cố');
            $table->text('resolution')->nullable()->comment('Giải pháp đã áp dụng');
            $table->text('direction')->nullable()->comment('Hướng giải quyết');
            $table->enum('status', ['pending', 'in_progress', 'resolved'])->default('pending');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deacon_report_incidents');
    }
};
