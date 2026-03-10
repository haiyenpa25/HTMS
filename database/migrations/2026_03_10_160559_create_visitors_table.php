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
        Schema::create('visitors', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->date('birth_date')->nullable();
            $table->string('address')->nullable();
            
            // Ngày đên xem lần đầu
            $table->date('first_visit_date')->nullable();
            
            // Người mời đến (có thể là tín hữu)
            $table->string('invited_by')->nullable();
            
            // Nhu cầu được cầu nguyện
            $table->text('prayer_requests')->nullable();
            
            // Mức độ quan tâm / Giai đoạn phễu
            $table->enum('status', [
                'new',           // Thân hữu mới
                'contacted',     // Đã liên hệ
                'studying',      // Đang học Giáo lý (Báp-tem)
                'baptized',      // Đã Báp-tem (Đủ điều kiện convert sang User)
                'lost'           // Không còn đi / Mất liên lạc
            ])->default('new');
            
            // Người được phân công chăm sóc (Chấp sự / Nhân sự)
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('visitors');
    }
};
