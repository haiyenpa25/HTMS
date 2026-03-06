<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('deacon_monthly_reports', function (Blueprint $table) {
            $table->boolean('unlock_requested')->default(false)->after('status')->comment('Đánh dấu Xin mở khoá (khi báo cáo đã duyệt)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('deacon_monthly_reports', function (Blueprint $table) {
            $table->dropColumn('unlock_requested');
        });
    }
};
