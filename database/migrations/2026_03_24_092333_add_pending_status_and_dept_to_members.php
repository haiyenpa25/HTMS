<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('members', function (Blueprint $table) {
            // pending_dept_id: ghi lại ban nào tạo thành viên tạm này
            $table->unsignedBigInteger('pending_dept_id')->nullable()->after('status');
            // submitted_by_user_id: ai đã tạo
            $table->unsignedBigInteger('submitted_by_user_id')->nullable()->after('pending_dept_id');
        });
    }

    public function down(): void
    {
        Schema::table('members', function (Blueprint $table) {
            $table->dropColumn(['pending_dept_id', 'submitted_by_user_id']);
        });
    }
};
