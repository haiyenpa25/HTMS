<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('visitations', function (Blueprint $table) {
            $table->enum('status', ['planned', 'completed', 'cancelled'])->default('planned');
            $table->enum('priority', ['high', 'medium', 'normal'])->default('normal');
        });
    }

    public function down(): void
    {
        Schema::table('visitations', function (Blueprint $table) {
            $table->dropColumn(['status', 'priority']);
        });
    }
};
