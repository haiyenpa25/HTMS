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
        // Central Funds of the Church (e.g. Quỹ Khuyến Học, Quỹ Xây Dựng, Quỹ Truyền Giáo)
        Schema::create('funds', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->text('description')->nullable();
            
            $table->enum('type', [
                'general',     // Quỹ chung (Tithe/1-10 flow vào đây)
                'building',    // Quỹ Xây Dựng
                'mission',     // Quỹ Truyền Giáo / Chăm sóc
                'charity',     // Quỹ Từ thiện / Cứu trợ
                'other'
            ])->default('general');
            
            $table->decimal('balance', 15, 2)->default(0); // Số dư tổng của Quỹ
            $table->boolean('is_active')->default(true);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('funds');
    }
};
