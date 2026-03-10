<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Feature;
use App\Models\User;
use App\Models\UserDepartmentFeature;
use App\Services\PortalService;
use Illuminate\Database\Seeder;

/**
 * FeatureSeeder — Khởi tạo MAC system.
 *
 * 1. Seed bảng features (10 tính năng chuẩn)
 * 2. Reset user_department_features (clean slate)
 * 3. Grant superadmin FULL ACCESS tất cả features + departments
 */
class FeatureSeeder extends Seeder
{
    const FEATURES = [
        // ── Ban Sinh Hoạt (activities) ──────────────────────────────
        ['name' => 'Điểm Danh',   'slug' => 'attendance',    'icon' => '✅', 'portal_type' => 'activities', 'description' => 'Điểm danh tại các buổi nhóm sinh hoạt'],
        ['name' => 'Thăm Viếng',  'slug' => 'visitation',   'icon' => '💚', 'portal_type' => 'activities', 'description' => 'Ghi nhận và theo dõi hoạt động thăm viếng'],
        ['name' => 'Thành Viên',  'slug' => 'members',      'icon' => '👥', 'portal_type' => 'activities', 'description' => 'Quản lý danh sách thành viên ban ngành'],
        ['name' => 'Phân Công',   'slug' => 'assignments',  'icon' => '📋', 'portal_type' => 'activities', 'description' => 'Phân công trực nhật, chức vụ trong buổi nhóm'],
        ['name' => 'Báo Cáo',     'slug' => 'reports',      'icon' => '📊', 'portal_type' => 'activities', 'description' => 'Xem và xuất báo cáo theo tháng/quý'],
        ['name' => 'Tài Chính',   'slug' => 'finance',      'icon' => '💰', 'portal_type' => 'activities', 'description' => 'Quản lý thu chi, quỹ ban ngành'],
        // ── Ban Cơ Đốc Giáo Dục (ministry/education) ────────────────
        ['name' => 'Lớp Học',           'slug' => 'education-classes',    'icon' => '🏫', 'portal_type' => 'ministry', 'description' => 'Quản lý danh sách lớp học Cơ Đốc Giáo Dục'],
        ['name' => 'Điểm Danh Lớp',     'slug' => 'education-attendance', 'icon' => '📝', 'portal_type' => 'ministry', 'description' => 'Điểm danh và chấm điểm theo buổi học'],
        ['name' => 'Tiền Dâng Lớp',     'slug' => 'education-offering',   'icon' => '💵', 'portal_type' => 'ministry', 'description' => 'Theo dõi tiền dâng theo lớp và buổi học'],
        ['name' => 'Báo Cáo Giáo Dục',  'slug' => 'education-report',     'icon' => '📈', 'portal_type' => 'ministry', 'description' => 'Báo cáo tổng hợp theo tháng cho cơ đốc giáo dục'],
    ];

    public function run(): void
    {
        $this->command->info('🔄 Seeding features table...');

        // 1. Seed 10 features chuẩn (updateOrCreate để idempotent)
        foreach (self::FEATURES as $data) {
            Feature::updateOrCreate(
                ['slug' => $data['slug']],
                $data
            );
        }
        $this->command->info('✅ ' . Feature::count() . ' features seeded.');

        // 2. Clean slate — xóa tất cả permissions cũ
        $deleted = UserDepartmentFeature::truncate();
        $this->command->info('🗑  user_department_features table cleared.');

        // 3. Grant superadmin FULL ACCESS
        $domain = env('SYSTEM_DOMAIN', 'httlthanhmyloi.com');
        $superAdmin = User::where('email', "superadmin@$domain")->first();
        if (!$superAdmin) {
            $this->command->warn("⚠  Superadmin user (superadmin@$domain) not found! Skipping full access grant.");
            return;
        }

        $service = app(PortalService::class);
        $count   = $service->grantSuperadminFullAccess($superAdmin);
        $this->command->info("🔑 Granted {$count} feature-department permissions to superadmin.");
        $this->command->info('✅ FeatureSeeder completed.');
    }
}
