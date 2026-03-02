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
        // Department Funds
        Schema::create('department_funds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('department_id')->constrained('departments')->cascadeOnDelete();
            $table->string('name'); // Quỹ thường xuyên, Quỹ thăm viếng, etc.
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // Department Meetings (weekly/bi-weekly sessions)
        Schema::create('department_meetings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('department_id')->constrained('departments')->cascadeOnDelete();
            $table->date('meeting_date');
            $table->unsignedInteger('attendance_morning')->default(0)->comment('Sỉ số buổi sáng');
            $table->unsignedInteger('attendance_afternoon')->default(0)->comment('Sỉ số buổi chiều');
            $table->text('note')->nullable();
            $table->timestamps();
        });

        // Department Transactions (linked to a meeting session optionally)
        Schema::create('department_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('department_fund_id')->constrained('department_funds')->cascadeOnDelete();
            $table->foreignId('department_meeting_id')->nullable()->constrained('department_meetings')->nullOnDelete();
            $table->enum('type', ['income', 'expense']);
            $table->bigInteger('amount');
            $table->string('category')->nullable(); // Tiền hộp tuần, Chi hoạt động, Thăm viếng...
            $table->text('description')->nullable();
            $table->date('transaction_date');
            $table->enum('status', ['pending', 'approved'])->default('pending');
            $table->timestamps();
        });

        // --- Permissions ---
        $permissions = ['view_dept_finance', 'manage_dept_finance'];
        foreach ($permissions as $p) {
            Permission::firstOrCreate(['name' => $p, 'guard_name' => 'web']);
        }

        $roles = ['Super_Admin', 'Pastor'];
        foreach ($roles as $roleName) {
            $role = Role::where('name', $roleName)->where('guard_name', 'web')->first();
            if ($role) {
                $role->givePermissionTo($permissions);
            }
        }

        // Department leads also get view + manage
        $leadRoles = ['Department_Lead', 'Team_Lead'];
        foreach ($leadRoles as $roleName) {
            $role = Role::where('name', $roleName)->where('guard_name', 'web')->first();
            if ($role) {
                $role->givePermissionTo($permissions);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('department_transactions');
        Schema::dropIfExists('department_meetings');
        Schema::dropIfExists('department_funds');

        $perms = ['view_dept_finance', 'manage_dept_finance'];
        foreach ($perms as $p) {
            Permission::where('name', $p)->delete();
        }
    }
};
