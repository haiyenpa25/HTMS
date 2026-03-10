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
        Schema::create('visitor_followups', function (Blueprint $table) {
            $table->id();
            
            $table->foreignId('visitor_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete()->comment('Người thực hiện chăm sóc');
            
            $table->enum('type', [
                'call',      // Gọi điện
                'visit',     // Thăm viếng tận nhà
                'message',   // Nhắn tin
                'meeting'    // Gặp mặt tại HT
            ])->default('call');
            
            $table->date('contact_date');
            
            $table->text('notes')->nullable(); // Ghi chú kết quả chuyến thăm
            
            // Có tiếp tục màng chăm sóc sau buổi này?
            $table->enum('outcome', [
                'positive',   // Phản hồi tích cực, hẹn gặp lại
                'neutral',    // Bình thường
                'negative',   // Từ chối nhận chăm sóc
                'no_answer'   // Không liên lạc được
            ])->default('neutral');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('visitor_followups');
    }
};
