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
        Schema::create('assets', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique()->comment('Mã tài sản (VD: TS-AM-001)');
            
            $table->enum('category', [
                'electronics', // Đồ điện tử, âm thanh, máy chiếu
                'furniture',   // Bàn ghế, tủ
                'musical',     // Nhạc cụ
                'books',       // Sách vở, Kinh Thánh
                'vehicle',     // Phương tiện
                'other'        // Khác
            ])->default('other');
            
            $table->string('brand')->nullable();
            $table->string('model')->nullable();
            $table->string('serial_number')->nullable();
            
            $table->date('purchase_date')->nullable();
            $table->decimal('purchase_price', 15, 2)->nullable();
            
            $table->enum('status', [
                'new',          // Mới
                'in_use',       // Đang sử dụng tốt
                'maintenance',  // Đang bảo trì/sửa chữa
                'broken',       // Hỏng hóc
                'lost',         // Thất lạc
                'liquidated'    // Đã thanh lý
            ])->default('in_use');

            $table->text('notes')->nullable();
            
            $table->foreignId('department_id')->nullable()->constrained()->nullOnDelete()->comment('Ban ngành đang giữ (nếu có)');
            
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assets');
    }
};
