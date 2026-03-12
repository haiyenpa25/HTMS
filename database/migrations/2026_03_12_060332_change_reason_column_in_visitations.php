<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE visitations MODIFY reason VARCHAR(255) NOT NULL');
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE visitations MODIFY reason ENUM('ốm đau','mới tin Chúa','khích lệ','khác') NOT NULL");
    }
};
