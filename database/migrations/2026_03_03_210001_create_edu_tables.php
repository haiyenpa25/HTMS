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
        // ── 1. Lớp học CĐGD ──────────────────────────────────────────
        Schema::create('edu_classes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('department_id')->constrained('departments')->cascadeOnDelete();
            $table->string('name');                // "Lớp Trung Lão 2025"
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // ── 2. Thành viên mỗi lớp (học viên + giáo viên) ────────────
        Schema::create('edu_class_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('edu_class_id')->constrained('edu_classes')->cascadeOnDelete();
            $table->foreignId('member_id')->constrained('members')->cascadeOnDelete();
            $table->enum('role', ['teacher', 'student'])->default('student');
            $table->date('joined_at')->nullable();
            $table->timestamps();

            $table->unique(['edu_class_id', 'member_id']);
        });

        // ── 3. Buổi học ──────────────────────────────────────────────
        Schema::create('edu_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('edu_class_id')->constrained('edu_classes')->cascadeOnDelete();
            $table->date('session_date');
            $table->unsignedSmallInteger('lesson_number')->nullable(); // Bài số (nhập tay)
            $table->string('topic')->nullable();          // Chủ đề bài học
            $table->string('scripture')->nullable();      // Câu gốc
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // ── 4. Điểm danh + điểm thi từng buổi học (per-student) ─────
        Schema::create('edu_session_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('edu_session_id')->constrained('edu_sessions')->cascadeOnDelete();
            $table->foreignId('member_id')->constrained('members')->cascadeOnDelete();
            $table->enum('attendance', ['present', 'absent', 'excused'])->default('absent');
            $table->boolean('memorized_verse')->default(false); // Kiểm tra câu gốc
            $table->unsignedSmallInteger('quiz_score')->nullable(); // Điểm thi (0-100)
            $table->timestamps();

            $table->unique(['edu_session_id', 'member_id']);
        });

        // ── 5. Quỹ nội bộ mỗi lớp (giống department_funds) ──────────
        Schema::create('edu_class_funds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('edu_class_id')->constrained('edu_classes')->cascadeOnDelete();
            $table->string('name'); // "Quỹ tiền dâng lớp"
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // ── 6. Giao dịch tài chính mỗi lớp (giống dept_transactions) ─
        Schema::create('edu_class_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('edu_class_fund_id')->constrained('edu_class_funds')->cascadeOnDelete();
            $table->foreignId('edu_session_id')->nullable()->constrained('edu_sessions')->nullOnDelete();
            $table->enum('type', ['income', 'expense']);
            $table->bigInteger('amount'); // VND
            $table->string('category')->nullable(); // Tiền dâng, Chi hoạt động...
            $table->text('description')->nullable();
            $table->date('transaction_date');
            $table->enum('status', ['pending', 'approved'])->default('approved'); // Mặc định approved (không cần duyệt)
            $table->timestamps();
        });

        // ── Permissions ───────────────────────────────────────────────
        $permissions = [
            'manage_edu_classes',   // Trưởng ban CĐGD, Mục sư
            'view_edu_classes',     // Tất cả giáo viên và thành viên CĐGD
            'mark_edu_attendance',  // Giáo viên (scoped per class via Policy)
            'record_edu_offering',  // Giáo viên (scoped per class via Policy)
        ];

        foreach ($permissions as $p) {
            Permission::firstOrCreate(['name' => $p, 'guard_name' => 'web']);
        }

        // Global admins & Pastor get all edu permissions
        $adminRoles = ['Super_Admin', 'Pastor'];
        foreach ($adminRoles as $roleName) {
            $role = Role::where('name', $roleName)->where('guard_name', 'web')->first();
            if ($role) {
                $role->givePermissionTo($permissions);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('edu_class_transactions');
        Schema::dropIfExists('edu_class_funds');
        Schema::dropIfExists('edu_session_records');
        Schema::dropIfExists('edu_sessions');
        Schema::dropIfExists('edu_class_members');
        Schema::dropIfExists('edu_classes');

        foreach (['manage_edu_classes', 'view_edu_classes', 'mark_edu_attendance', 'record_edu_offering'] as $p) {
            Permission::where('name', $p)->delete();
        }
    }
};
