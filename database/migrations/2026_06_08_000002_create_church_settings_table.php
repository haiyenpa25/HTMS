<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('church_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key', 100)->unique();
            $table->text('value')->nullable();
            $table->string('type', 30)->default('string'); // string, integer, boolean, json
            $table->string('label', 200)->nullable();       // Tên hiển thị cho Admin
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // Seed giá trị mặc định
        DB::table('church_settings')->insert([
            ['key' => 'current_term_year', 'value' => '2024', 'type' => 'integer',
             'label' => 'Năm nhiệm kỳ hiện tại', 'description' => 'Năm bắt đầu của nhiệm kỳ Chấp Sự đang hoạt động',
             'created_at' => now(), 'updated_at' => now()],
            ['key' => 'church_name', 'value' => 'Hội Thánh', 'type' => 'string',
             'label' => 'Tên Hội Thánh', 'description' => 'Tên đầy đủ của Hội Thánh',
             'created_at' => now(), 'updated_at' => now()],
            ['key' => 'sunday_service_time', 'value' => '08:30', 'type' => 'string',
             'label' => 'Giờ nhóm Chủ Nhật', 'description' => 'Giờ bắt đầu buổi nhóm chính Chủ Nhật',
             'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('church_settings');
    }
};
