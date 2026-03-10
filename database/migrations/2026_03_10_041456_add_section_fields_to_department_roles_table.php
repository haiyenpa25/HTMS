<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('department_roles', function (Blueprint $table) {
            $table->string('section')->nullable()->after('name'); // VD: "I. Chương Trình Lễ"
            $table->unsignedTinyInteger('sort_order')->default(0)->after('section');
            $table->unsignedTinyInteger('max_count')->default(1)->after('sort_order'); // số người cho vị trí này
        });
    }

    public function down(): void
    {
        Schema::table('department_roles', function (Blueprint $table) {
            $table->dropColumn(['section', 'sort_order', 'max_count']);
        });
    }
};
