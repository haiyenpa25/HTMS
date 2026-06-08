<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('edu_classes', function (Blueprint $table) {
            // Phân loại lứa tuổi: ấu nhi, thiếu nhi, thiếu niên, thanh niên, trung niên, người lớn
            $table->string('class_category', 50)->nullable()->after('class_type')
                ->comment('Lứa tuổi: au_nhi, thieu_nhi, thieu_nien, thanh_nien, trung_nien, nguoi_lon, mixed');

            // Lớp theo mùa (TKMT, v.v.)
            $table->boolean('is_seasonal')->default(false)->after('class_category');
            $table->string('season_name', 150)->nullable()->after('is_seasonal')
                ->comment('VD: Thánh Kinh Mùa Hè 2026');
            $table->date('season_start')->nullable()->after('season_name');
            $table->date('season_end')->nullable()->after('season_start');
        });
    }

    public function down(): void
    {
        Schema::table('edu_classes', function (Blueprint $table) {
            $table->dropColumn(['class_category', 'is_seasonal', 'season_name', 'season_start', 'season_end']);
        });
    }
};
