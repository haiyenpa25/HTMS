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
        Schema::table('events', function (Blueprint $table) {
            if (\Illuminate\Support\Facades\DB::getDriverName() !== 'sqlite') {
                \Illuminate\Support\Facades\DB::statement("ALTER TABLE events MODIFY COLUMN scope_type ENUM('global', 'department', 'internal', 'leadership', 'personal') NOT NULL DEFAULT 'global'");
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            if (\Illuminate\Support\Facades\DB::getDriverName() !== 'sqlite') {
                \Illuminate\Support\Facades\DB::statement("ALTER TABLE events MODIFY COLUMN scope_type ENUM('global', 'department', 'internal', 'leadership') NOT NULL DEFAULT 'global'");
            }
        });
    }
};
