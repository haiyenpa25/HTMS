<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('deacon_monthly_reports', function (Blueprint $table) {
            // Add new text columns for the updated report form
            if (!Schema::hasColumn('deacon_monthly_reports', 'reporter_name')) {
                $table->string('reporter_name')->nullable()->after('submitted_by');
            }
            if (!Schema::hasColumn('deacon_monthly_reports', 'evaluation')) {
                $table->text('evaluation')->nullable()->after('reporter_name');
            }
            if (!Schema::hasColumn('deacon_monthly_reports', 'proposals')) {
                $table->text('proposals')->nullable()->after('evaluation');
            }
            if (!Schema::hasColumn('deacon_monthly_reports', 'notes')) {
                $table->text('notes')->nullable()->after('proposals');
            }
        });
    }

    public function down(): void
    {
        if (Schema::hasColumn('deacon_monthly_reports', 'reporter_name')) {
            Schema::table('deacon_monthly_reports', function (Blueprint $table) {
                $table->dropColumn(['reporter_name', 'evaluation', 'proposals', 'notes']);
            });
        }
    }
};
