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
            // First drop the old column or modify it. SQLite has trouble modifying enums, 
            // but for MySQL we can usually drop and add, or modify. Let's just drop and add to be safe.
            $table->dropColumn('visibility');
            $table->string('type')->change(); // Instead of ENUM, using simple string to avoid future truncation/modification issues.
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->enum('visibility', ['public', 'internal', 'leadership'])->default('public');
            // Assuming type shouldn't easily revert to enum since data might be lost
        });
    }
};
