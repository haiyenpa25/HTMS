<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('visitations', function (Blueprint $table) {
            if (!Schema::hasColumn('visitations', 'status')) {
                $table->enum('status', ['planned', 'completed', 'cancelled'])->default('planned');
            }
            if (!Schema::hasColumn('visitations', 'priority')) {
                $table->enum('priority', ['high', 'medium', 'normal'])->default('normal');
            }
        });
    }

    public function down(): void
    {
        Schema::table('visitations', function (Blueprint $table) {
            if (Schema::hasColumn('visitations', 'status')) {
                $table->dropColumn('status');
            }
            if (Schema::hasColumn('visitations', 'priority')) {
                $table->dropColumn('priority');
            }
        });
    }
};
