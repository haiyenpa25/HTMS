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
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            
            $table->dateTime('start_time');
            $table->dateTime('end_time')->nullable();
            
            $table->boolean('is_all_day')->default(false);
            
            $table->enum('type', [
                'church_service', // Tiệc Thánh, Báp-tem, Truyền giảng...
                'seminar', // Hội đồng, Bồi linh
                'meeting', // Họp chung (khác với Meeting ban ngành)
                'holiday', // Ngày lễ phụ
                'other'
            ])->default('other');
            
            $table->string('color', 20)->default('#3788d8'); // Hex color cho FullCalendar
            $table->string('location')->nullable();
            
            $table->enum('visibility', [
                'public', // Ai cũng thấy trên lịch HT
                'internal', // Chỉ thành viên đăng nhập thấy
                'leadership' // Chỉ BCS/Mục sư thấy
            ])->default('public');
            
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
