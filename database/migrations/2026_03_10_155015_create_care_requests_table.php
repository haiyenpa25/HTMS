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
        Schema::create('care_requests', function (Blueprint $table) {
            $table->id();
            
            // Người gửi (có thể null nếu là khách - tuỳ chọn sau này, hiện tại bắt buộc phải có tài khoản)
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            
            $table->string('title');
            $table->text('content');
            
            // Phân loại Yêu cầu
            $table->enum('category', [
                'prayer',       // Xin cầu nguyện
                'counseling',   // Tư vấn mục vụ
                'feedback',     // Góp ý xây dựng HT
                'support'       // Hỗ trợ khác (kỹ thuật, tài liệu...)
            ])->default('prayer');
            
            // Trạng thái xử lý (Kanban)
            $table->enum('status', [
                'pending',      // Mới gửi
                'in_progress',  // Đang xem xét/Thăm viếng
                'resolved',     // Đã giải quyết / Đã cầu nguyện
                'closed'        // Đóng ticket
            ])->default('pending');
            
            // Mức độ ưu tiên
            $table->enum('priority', ['low', 'normal', 'high', 'urgent'])->default('normal');
            
            // Quyền riêng tư (Đặc biệt cho Tư vấn Mục vụ)
            $table->boolean('is_private')->default(false); // Nếu true -> Chỉ Pastor mới đọc được
            
            // Người phụ trách xử lý ticket này (Mục sư/Ban chăm sóc)
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            
            // Ghi chú của người xử lý (ẩn với người gửi)
            $table->text('resolution_notes')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('care_requests');
    }
};
