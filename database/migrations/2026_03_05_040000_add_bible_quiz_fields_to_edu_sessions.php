<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('edu_sessions', function (Blueprint $table) {
            // Thông tin bài kiểm tra trắc nghiệm
            $table->string('book', 100)->nullable()->after('scripture');        // Sách Kinh Thánh
            $table->unsignedTinyInteger('total_questions')->nullable()->after('book'); // Số câu hỏi
            $table->unsignedBigInteger('grader_id')->nullable()->after('total_questions'); // Người chấm (member)
            $table->string('photo_path', 500)->nullable()->after('grader_id'); // Ảnh bài thi

            $table->foreign('grader_id')->references('id')->on('members')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('edu_sessions', function (Blueprint $table) {
            $table->dropForeign(['grader_id']);
            $table->dropColumn(['book', 'total_questions', 'grader_id', 'photo_path']);
        });
    }
};
