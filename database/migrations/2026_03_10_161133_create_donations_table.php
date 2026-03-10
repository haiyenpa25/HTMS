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
        // Donations / Tithes
        Schema::create('donations', function (Blueprint $table) {
            $table->id();
            
            $table->foreignId('fund_id')->constrained('funds')->cascadeOnDelete();
            
            // Người dâng (null = giấu tên / khách vãng lai)
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            
            $table->enum('type', [
                'tithe',         // 1/10
                'offering',      // Lạc quyên / Tự nguyện
                'thanksgiving',  // Cảm tạ
                'pledge',        // Lời hứa dâng (Đã thực hiện)
                'special'        // Đặc biệt
            ])->default('offering');
            
            $table->decimal('amount', 15, 2);
            $table->date('donation_date');
            
            $table->enum('payment_method', [
                'cash',          // Tiền mặt
                'transfer',      // Chuyển khoản
                'card'           // Quẹt thẻ
            ])->default('cash');
            
            $table->string('reference_number')->nullable()->comment('Mã đối chiếu GD/Biên lai ngân hàng');
            $table->text('notes')->nullable();
            
            // Người ghi sổ
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('donations');
    }
};
