<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('duty_assignments', function (Blueprint $table) {
            // slot cho phép nhiều người trên cùng 1 vị trí (slot 1, slot 2...)
            $table->unsignedTinyInteger('slot')->default(1)->after('department_role_id');
        });
    }

    public function down(): void
    {
        Schema::table('duty_assignments', function (Blueprint $table) {
            $table->dropColumn('slot');
        });
    }
};
