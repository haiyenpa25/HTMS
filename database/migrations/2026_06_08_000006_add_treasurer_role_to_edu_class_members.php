<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Thêm role 'treasurer' cho edu_class_members
        // MySQL: thay đổi column role để nhận thêm giá trị 'treasurer'
        DB::statement("ALTER TABLE edu_class_members MODIFY COLUMN role ENUM('student','teacher','treasurer') NOT NULL DEFAULT 'student'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE edu_class_members MODIFY COLUMN role ENUM('student','teacher') NOT NULL DEFAULT 'student'");
    }
};
