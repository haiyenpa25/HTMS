<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    public function up(): void
    {
        // Main report header: one report per (department, month, year)
        Schema::create('department_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('department_id')->constrained('departments')->cascadeOnDelete();
            $table->unsignedSmallInteger('report_month');
            $table->unsignedSmallInteger('report_year');
            $table->string('reporter_name')->nullable();  // Người báo cáo
            $table->enum('status', ['draft', 'submitted', 'approved'])->default('draft');
            // Narrative sections (Report Feedback)
            $table->text('evaluation')->nullable();  // Nhận xét
            $table->text('request')->nullable();     // Yêu cầu
            $table->text('proposals')->nullable();   // Đề nghị/Kế hoạch
            // Upcoming activities plan (JSON array of weekly schedule)
            $table->json('upcoming_plan')->nullable();
            // Activities section free-text
            $table->text('activities_notes')->nullable(); // Ban viên mới, thăm viếng...
            $table->unique(['department_id','report_month','report_year']);
            $table->timestamps();
        });

        // --- Permissions ---
        $permissions = ['view_reports', 'create_reports', 'approve_reports'];
        foreach ($permissions as $p) {
            Permission::firstOrCreate(['name' => $p, 'guard_name' => 'web']);
        }
        // Super_Admin + Pastor get all
        foreach (['Super_Admin', 'Pastor'] as $rn) {
            $role = Role::where('name', $rn)->where('guard_name', 'web')->first();
            if ($role) $role->givePermissionTo($permissions);
        }
        // Dept_Lead / Team_Lead can view + create
        foreach (['Department_Lead', 'Team_Lead'] as $rn) {
            $role = Role::where('name', $rn)->where('guard_name', 'web')->first();
            if ($role) $role->givePermissionTo(['view_reports', 'create_reports']);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('department_reports');
        foreach (['view_reports', 'create_reports', 'approve_reports'] as $p) {
            Permission::where('name', $p)->delete();
        }
    }
};
