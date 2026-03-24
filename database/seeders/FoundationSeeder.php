<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Feature;
use App\Models\FeatureDepartment;
use App\Models\Member;
use App\Models\OrgMembership;
use App\Models\OrgRole;
use App\Models\User;
use App\Models\UserDepartmentFeature;
use App\Models\Church;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/**
 * FoundationSeeder — Seeder khởi động cho fresh deployment.
 *
 * Chạy an toàn nhiều lần (idempotent) — dùng updateOrCreate ở mọi bước.
 *
 * Thứ tự:
 *  1. Church info (từ .env hoặc default)
 *  2. Spatie roles tối thiểu (Super_Admin, Deacon, Secretary)
 *  3. Features (17 tính năng chuẩn MAC)
 *  4. Departments (leadership + activities + ministry)
 *  5. OrgRoles (Trưởng ban, Phó ban, ...)
 *  6. feature_departments (Level 1 MAC config — block-level)
 *  7. SuperAdmin account + full MAC permissions
 *
 * Cách chạy:
 *   php artisan db:seed --class=FoundationSeeder
 *
 * Sau đó thêm tài khoản đại diện các ban:
 *   php artisan db:seed --class=OrgStructureSeeder
 */
class FoundationSeeder extends Seeder
{
    // ─── 1. Definitions ───────────────────────────────────────────────────────

    /** 17 features chuẩn của hệ thống */
    const FEATURES = [
        ['name' => 'Điểm Danh',         'slug' => 'attendance',          'icon' => '✅', 'portal_type' => 'activities', 'description' => 'Điểm danh tại các buổi nhóm sinh hoạt'],
        ['name' => 'Thăm Viếng',         'slug' => 'visitation',         'icon' => '💚', 'portal_type' => 'activities', 'description' => 'Ghi nhận và theo dõi hoạt động thăm viếng'],
        ['name' => 'Thành Viên',         'slug' => 'members',            'icon' => '👥', 'portal_type' => 'activities', 'description' => 'Quản lý danh sách thành viên ban ngành'],
        ['name' => 'Phân Công',          'slug' => 'assignments',        'icon' => '📋', 'portal_type' => 'activities', 'description' => 'Phân công trực nhật, chức vụ trong buổi nhóm'],
        ['name' => 'Báo Cáo',            'slug' => 'reports',            'icon' => '📊', 'portal_type' => 'activities', 'description' => 'Xem và xuất báo cáo theo tháng/quý'],
        ['name' => 'Tài Chính',          'slug' => 'finance',            'icon' => '💰', 'portal_type' => 'activities', 'description' => 'Quản lý thu chi, quỹ ban ngành'],
        ['name' => 'Lớp Học',            'slug' => 'education-classes',   'icon' => '🏫', 'portal_type' => 'ministry',   'description' => 'Quản lý danh sách lớp học Cơ Đốc Giáo Dục'],
        ['name' => 'Điểm Danh Lớp',     'slug' => 'education-attendance','icon' => '📝', 'portal_type' => 'ministry',   'description' => 'Điểm danh và chấm điểm theo buổi học'],
        ['name' => 'Tiền Dâng Lớp',      'slug' => 'education-offering', 'icon' => '💵', 'portal_type' => 'ministry',   'description' => 'Theo dõi tiền dâng theo lớp và buổi học'],
        ['name' => 'Báo Cáo Giáo Dục',  'slug' => 'education-report',   'icon' => '📈', 'portal_type' => 'ministry',   'description' => 'Báo cáo tổng hợp theo tháng cho cơ đốc giáo dục'],
        ['name' => 'Sổ tay Hội Thánh',  'slug' => 'chronicles',         'icon' => '📖', 'portal_type' => 'global',     'description' => 'Biên niên sử sự kiện trọng đại của tổ chức'],
        ['name' => 'Nhật Ký Hoạt Động', 'slug' => 'activity-logs',      'icon' => '📜', 'portal_type' => 'global',     'description' => 'Tra cứu System Audit Logs'],
        ['name' => 'Tài Liệu',          'slug' => 'documents',          'icon' => '📁', 'portal_type' => 'global',     'description' => 'Lưu trữ văn bản, file trên đám mây'],
        ['name' => 'Thiết Bị',          'slug' => 'assets',             'icon' => '🖨️', 'portal_type' => 'global',     'description' => 'Quản lý cơ sở vật chất, thiết bị mượn/trả'],
        ['name' => 'Người Dùng',        'slug' => 'users-manager',      'icon' => '🧑‍💼','portal_type' => 'global',     'description' => 'Danh sách tài khoản và phiên đăng nhập'],
        ['name' => 'Biểu Mẫu',          'slug' => 'forms-manager',      'icon' => '📄', 'portal_type' => 'global',     'description' => 'Tạo và quản lý các loại Đơn từ/Biểu mẫu trực tuyến'],
        ['name' => 'Chăm Sóc',          'slug' => 'care',               'icon' => '🤝', 'portal_type' => 'global',     'description' => 'Quản lý thông tin và vòng đời chăm sóc tín hữu'],
    ];

    /**
     * Level 1 MAC — Block-level feature visibility.
     * Format: block => [slug, ...]
     *
     * DEFAULT DENY policy: feature KHÔNG có trong bảng này → không ai dùng được.
     * Phải thêm vào đây nếu muốn mở cho block đó.
     */
    const FEATURE_BLOCK_CONFIG = [
        'activities' => [
            'attendance', 'visitation', 'members', 'reports', 'assignments',
            'finance', 'chronicles', 'care',
        ],
        'ministry' => [
            'education-classes', 'education-attendance', 'education-offering',
            'education-report', 'visitation', 'members', 'reports',
            'chronicles', 'care', 'finance',
        ],
        'leadership' => [
            'attendance', 'reports', 'finance', 'chronicles',
        ],
    ];

    /** Danh sách Ban Ngành */
    const DEPARTMENTS = [
        // Khối Lãnh Đạo
        ['code' => 'BCS',  'name' => 'Ban Chấp sự',             'block' => 'leadership', 'parent_code' => null],
        ['code' => 'BTS',  'name' => 'Ban Trị sự',               'block' => 'leadership', 'parent_code' => 'BCS'],

        // Khối Sinh Hoạt
        ['code' => 'BTL',  'name' => 'Ban Trung Lão',            'block' => 'activities', 'parent_code' => null],
        ['code' => 'BTN',  'name' => 'Ban Trung Niên',           'block' => 'activities', 'parent_code' => null],
        ['code' => 'BTTR', 'name' => 'Ban Thanh Tráng',          'block' => 'activities', 'parent_code' => null],
        ['code' => 'BTNI', 'name' => 'Ban Thanh Niên',           'block' => 'activities', 'parent_code' => null],
        ['code' => 'BTNH', 'name' => 'Ban Thiếu Nhi',            'block' => 'activities', 'parent_code' => null],

        // Khối Mục Vụ
        ['code' => 'BCDGD', 'name' => 'Ban Cơ Đốc Giáo Dục',    'block' => 'ministry',   'parent_code' => null],
        ['code' => 'BTG',   'name' => 'Ban Truyền Giảng',        'block' => 'ministry',   'parent_code' => null],
        ['code' => 'BCDCS', 'name' => 'Ban Chứng Đạo – Chăm Sóc TTH', 'block' => 'ministry', 'parent_code' => null],
        ['code' => 'BKT',   'name' => 'Ban Kỹ Thuật',            'block' => 'ministry',   'parent_code' => null],
        ['code' => 'BNC',   'name' => 'Ban Nhạc Cụ',             'block' => 'ministry',   'parent_code' => null],
        ['code' => 'BKN',   'name' => 'Ban Kết Nối',             'block' => 'ministry',   'parent_code' => null],
        ['code' => 'BKTI',  'name' => 'Ban Khánh Tiết',          'block' => 'ministry',   'parent_code' => null],
        ['code' => 'BHC',   'name' => 'Ban Hậu Cần',             'block' => 'ministry',   'parent_code' => null],
        ['code' => 'BCN',   'name' => 'Ban Cầu Nguyện',          'block' => 'ministry',   'parent_code' => null],
        ['code' => 'BTTTT', 'name' => 'Ban Tiếp Tân – Trật Tự', 'block' => 'ministry',   'parent_code' => null],
        ['code' => 'BTTRD', 'name' => 'Ban Tương Trợ',           'block' => 'ministry',   'parent_code' => null],
        ['code' => 'BTV',   'name' => 'Ban Thăm Viếng',          'block' => 'ministry',   'parent_code' => null],
        ['code' => 'BHTP',  'name' => 'Ban Hát Thờ Phượng',      'block' => 'ministry',   'parent_code' => null],
    ];

    /** OrgRoles */
    const ORG_ROLES = [
        ['name' => 'Trưởng Ban',        'code' => 'tb',   'level' => 70],
        ['name' => 'Phó Ban',           'code' => 'pb',   'level' => 60],
        ['name' => 'Thư Ký',            'code' => 'tk',   'level' => 50],
        ['name' => 'Thủ Quỹ',           'code' => 'tq',   'level' => 50],
        ['name' => 'Ủy Viên',           'code' => 'uv',   'level' => 40],
        ['name' => 'Tổ Trưởng',         'code' => 'tt',   'level' => 30],
        ['name' => 'Ban Viên',          'code' => 'bv',   'level' => 10],
        ['name' => 'Chấp Sự',           'code' => 'cs',   'level' => 80],
        ['name' => 'Thư ký Hội Thánh', 'code' => 'tkhu', 'level' => 95],
        ['name' => 'Phó thư ký',        'code' => 'ptk',  'level' => 90],
        ['name' => 'Thủ quỹ HT',        'code' => 'tqht', 'level' => 90],
        ['name' => 'Phó thủ quỹ',       'code' => 'ptq',  'level' => 85],
    ];

    // ─── 2. Run ───────────────────────────────────────────────────────────────

    public function run(): void
    {
        $domain     = env('SYSTEM_DOMAIN', 'httlthanhmyloi.com');
        $churchName = env('CHURCH_NAME', 'Hội Thánh Tin Lành');

        $this->command->info('');
        $this->command->info('════════════════════════════════════════════');
        $this->command->info('  🚀  FOUNDATION SEEDER — Fresh Deployment  ');
        $this->command->info('════════════════════════════════════════════');

        // ── Step 1: Church ───────────────────────────────────────────────────
        $this->command->info('📍 [1/7] Church info...');
        Church::firstOrCreate(
            ['email' => env('CHURCH_EMAIL', "contact@$domain")],
            [
                'name'         => $churchName,
                'address'      => env('CHURCH_ADDRESS', 'Địa chỉ Hội Thánh'),
                'phone_number' => env('CHURCH_PHONE', '0123456789'),
            ]
        );

        // ── Step 2: Spatie Roles (minimal — just what system needs) ─────────
        $this->command->info('🔑 [2/7] Roles...');
        $coreRoles = ['Super_Admin', 'Pastor', 'BTS_Admin', 'Department_Lead',
                      'Secretary', 'Deacon', 'Team_Lead', 'Member', 'Visitation_Staff'];
        foreach ($coreRoles as $roleName) {
            Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
        }
        // Core permissions (admin panel only — MAC handles portal)
        $adminPerms = [
            'view users', 'create users', 'edit users', 'delete users',
            'view roles', 'edit roles',
            'view members', 'create members', 'edit members', 'delete members',
            'view departments', 'create departments', 'edit departments', 'delete departments',
            'view sensitive_info', 'edit sensitive_info',
        ];
        foreach ($adminPerms as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }
        // Super_Admin gets all permissions
        $superRole = Role::findByName('Super_Admin');
        $superRole->syncPermissions(Permission::all());

        // ── Step 3: Features ─────────────────────────────────────────────────
        $this->command->info('🧩 [3/7] Features (MAC)...');
        foreach (self::FEATURES as $data) {
            Feature::updateOrCreate(['slug' => $data['slug']], $data);
        }
        $this->command->info('   → ' . Feature::count() . ' features ready.');

        // ── Step 4: Departments ──────────────────────────────────────────────
        $this->command->info('🏢 [4/7] Departments...');
        $deptMap = [];
        foreach (self::DEPARTMENTS as $data) {
            $dept = Department::updateOrCreate(
                ['code' => $data['code']],
                [
                    'name'  => $data['name'],
                    'block' => $data['block'],
                    'is_active' => true,
                ]
            );
            $deptMap[$data['code']] = $dept;
        }
        // Set parent_id after all depts exist
        foreach (self::DEPARTMENTS as $data) {
            if ($data['parent_code'] && isset($deptMap[$data['parent_code']])) {
                $deptMap[$data['code']]->update(['parent_id' => $deptMap[$data['parent_code']]->id]);
            }
        }
        $this->command->info('   → ' . count($deptMap) . ' departments ready.');

        // ── Step 5: OrgRoles ─────────────────────────────────────────────────
        $this->command->info('🎖  [5/7] OrgRoles...');
        foreach (self::ORG_ROLES as $data) {
            OrgRole::updateOrCreate(['code' => $data['code']], $data);
        }

        // ── Step 6: Level 1 MAC — Feature-Block Config (DEFAULT DENY system) ─
        $this->command->info('🔐 [6/7] MAC Level 1 — Feature-Block assignments...');
        $features = Feature::all()->keyBy('slug');
        $created  = 0;
        foreach (self::FEATURE_BLOCK_CONFIG as $blockType => $slugs) {
            foreach ($slugs as $slug) {
                if (!isset($features[$slug])) continue;
                FeatureDepartment::updateOrCreate(
                    [
                        'feature_id'    => $features[$slug]->id,
                        'block_type'    => $blockType,
                        'department_id' => null, // block-level config
                    ],
                    [
                        'is_active'  => true,
                        'data_scope' => 'dept',
                        'scope'      => 'block',
                    ]
                );
                $created++;
            }
        }
        $this->command->info("   → $created feature-block configs ready.");

        // ── Step 7: SuperAdmin account ───────────────────────────────────────
        $this->command->info('👑 [7/7] SuperAdmin account...');
        $superAdmin = User::updateOrCreate(
            ['email' => "superadmin@$domain"],
            [
                'name'         => "Quản Trị (SuperAdmin)",
                'password'     => Hash::make(env('SUPERADMIN_PASSWORD', 'Abc.1234')),
                'is_superadmin'=> true,
            ]
        );
        $superAdmin->syncRoles(['Super_Admin']);

        // Grant SuperAdmin explicit MAC access to all features across all depts
        // (Backup cho trường hợp isSuperAdmin() không bypass đủ)
        $allDepts    = Department::all();
        $allFeatures = Feature::all();
        $macRows = 0;
        foreach ($allDepts as $dept) {
            foreach ($allFeatures as $feature) {
                UserDepartmentFeature::updateOrCreate(
                    [
                        'user_id'       => $superAdmin->id,
                        'department_id' => $dept->id,
                        'feature_id'    => $feature->id,
                    ],
                    [
                        'dept_type'    => $dept->block ?? 'activities',
                        'is_enabled'   => true,
                        'access_level' => 'manage',
                    ]
                );
                $macRows++;
            }
        }
        $this->command->info("   → SuperAdmin: {$macRows} MAC rows seeded.");

        // ── Done ─────────────────────────────────────────────────────────────
        $this->command->info('');
        $this->command->info('✅  Foundation complete!');
        $this->command->info("    Login: superadmin@$domain / " . env('SUPERADMIN_PASSWORD', 'Abc.1234'));
        $this->command->info('');
        $this->command->info('    Next steps (optional):');
        $this->command->info('    php artisan db:seed --class=OrgStructureSeeder   # Tạo tài khoản đại diện');
        $this->command->info('    php artisan db:seed --class=DemoDataSeeder       # Thêm dữ liệu mẫu');
        $this->command->info('');
    }
}
