<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Feature;
use App\Models\FeatureDepartment;
use App\Models\User;
use App\Models\UserDepartmentFeature;
use Illuminate\Database\Seeder;

/**
 * PermissionSampleDataSeeder — Cấp dữ liệu phân quyền mẫu theo MAC V2.
 *
 * MAC V2 Design:
 *  Level 1: feature_departments — cấu hình admin cấp cho từng ban ngành
 *  Level 2: user_department_features — override cụ thể cho từng user (tùy chọn)
 *
 * Seeder này:
 *  1. Cấu hình Level 1: Gán features phù hợp cho Ban Thanh Tráng, Ban Trung Lão
 *  2. Cấu hình Level 2: Cấp quyền mặc định cho user "Trưởng ban Thanh Tráng"
 *     (chỉ các features mà user KHÔNG muốn theo mặc định của dept)
 *  3. Không xóa dữ liệu cũ — sử dụng updateOrCreate để idempotent
 */
class PermissionSampleDataSeeder extends Seeder
{
    // Features mặc định cho Activities departments (block = 'activities')
    const ACTIVITIES_DEFAULT_FEATURES = [
        'attendance',   // Điểm danh
        'visitation',   // Thăm viếng
        'members',      // Thành viên
        'assignments',  // Phân công
        'reports',      // Báo cáo
        'finance',      // Tài chính
        'care',         // Chăm sóc
        'chronicles',   // Sổ tay HT (global - mặc định có)
        'documents',    // Tài liệu (global)
    ];

    // Features cho Ministry departments (block = 'ministry')
    const MINISTRY_DEFAULT_FEATURES = [
        'education-classes',    // Lớp học
        'education-attendance', // Điểm danh lớp
        'education-offering',   // Tiền dâng lớp
        'education-report',     // Báo cáo giáo dục
        'chronicles',           // Sổ tay HT (global)
        'documents',            // Tài liệu (global)
    ];

    public function run(): void
    {
        $this->command->info('🔄 Seeding sample permissions (MAC V2)...');

        // ── STEP 1: Cấu hình Level 1 (feature_departments) ──────────────────
        $this->seedLevel1();

        // ── STEP 2: Cấp quyền Level 2 mẫu cho superadmin ────────────────────
        $superAdmin = User::where('email', 'superadmin@httlthanhmyloi.com')->first();
        if ($superAdmin) {
            $this->command->info("   ↳ SuperAdmin bypasses Level 2 (is_superadmin=true). Skipping.");
        }

        // ── STEP 3: Cấp quyền Level 2 cho các user ban Thanh Tráng ─────────
        $this->seedThanhTrangUserPermissions();

        $this->command->info('✅ PermissionSampleDataSeeder completed.');
    }

    protected function seedLevel1(): void
    {
        $this->command->info('   📋 Configuring Level 1 (feature_departments)...');

        // Áp dụng cho TẤT CẢ departments theo block
        $activitiesDepts = Department::where('block', 'activities')->where('is_active', true)->get();
        $ministryDepts   = Department::where('block', 'ministry')->where('is_active', true)->get();

        foreach ($activitiesDepts as $dept) {
            $this->assignFeaturesToDept($dept, self::ACTIVITIES_DEFAULT_FEATURES, 'block', 'activities');
        }

        foreach ($ministryDepts as $dept) {
            $this->assignFeaturesToDept($dept, self::MINISTRY_DEFAULT_FEATURES, 'block', 'ministry');
        }

        $this->command->info('   ✅ Level 1 configured for ' . ($activitiesDepts->count() + $ministryDepts->count()) . ' departments.');
    }

    protected function assignFeaturesToDept(Department $dept, array $featureSlugs, string $scope, string $blockType): void
    {
        foreach ($featureSlugs as $slug) {
            $feature = Feature::where('slug', $slug)->first();
            if (!$feature) {
                $this->command->warn("      ⚠️  Feature not found: {$slug}");
                continue;
            }

            FeatureDepartment::updateOrCreate(
                [
                    'feature_id'    => $feature->id,
                    'department_id' => $dept->id,
                ],
                [
                    'scope'      => 'specific',
                    'block_type' => $dept->block,
                    'data_scope' => 'dept',
                    'is_active'  => true,
                ]
            );
        }
    }

    protected function seedThanhTrangUserPermissions(): void
    {
        $this->command->info('   👤 Seeding user-level permissions for Ban Thanh Tráng...');

        $thanhTrangDept = Department::where('name', 'like', '%Thanh Tráng%')
            ->orWhere('code', 'like', '%TT%')
            ->where('block', 'activities')
            ->first();

        if (!$thanhTrangDept) {
            $this->command->warn('   ⚠️  Ban Thanh Tráng department not found. Skipping user permissions.');
            return;
        }

        // Trưởng ban - toàn quyền (Level 2 sẽ không block gì cả → kế thừa Level 1)
        $leader = User::where('email', 'like', '%tb.thanhtrang%')
            ->orWhere('email', 'like', '%truongban.thanhtrang%')
            ->first();

        if ($leader) {
            $this->command->info("      ↳ User {$leader->email}: sẽ kế thừa từ Level 1 (không tạo override).");
        }

        // Thư ký - có tất cả trừ tài chính (revoke finance)
        $secretary = User::where('email', 'like', '%tk.thanhtrang%')
            ->orWhere('email', 'like', '%thukythanhtrang%')
            ->first();

        if ($secretary) {
            $this->command->info("      ↳ Seeding secretary {$secretary->email}: revoke 'finance'...");
            $financeFeature = Feature::where('slug', 'finance')->first();
            if ($financeFeature) {
                UserDepartmentFeature::updateOrCreate(
                    [
                        'user_id'       => $secretary->id,
                        'department_id' => $thanhTrangDept->id,
                        'feature_id'    => $financeFeature->id,
                    ],
                    [
                        'dept_type'    => 'activities',
                        'is_enabled'   => false, // Explicit revoke
                        'access_level' => 'view',
                    ]
                );
            }
        }

        $this->command->info('   ✅ User-level permissions seeded for Ban Thanh Tráng.');
    }
}
