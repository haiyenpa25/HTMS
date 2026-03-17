<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use App\Models\Church;
use App\Models\Department;
use App\Models\Team;
use App\Models\OrgRole;
use App\Models\User;
use App\Models\Member;
use App\Models\MemberSensitive;
use App\Models\OrgMembership;

class OrgStructureSeeder extends Seeder
{
    public function run()
    {
        $domain = env('SYSTEM_DOMAIN', 'httlthanhmyloi.com');
        $churchName = env('CHURCH_NAME', 'Hội Thánh Tin Lành');

        // 1. Spatie Permissions
        $portalPermissions = [
            'access_department_portal',
            'portal_view_members',
            'portal_manage_attendance',
            'view_attendance',
            'mark_attendance',
            'bypass_attendance_lock',
            'view_visitations',
            'create_visitation_requests',
            'manage_visitations',
            'view_sensitive_visitation_content',
            'view_finance',
            'manage_finance',
            'approve_finance',
        ];
        foreach ($portalPermissions as $perm) {
            \Spatie\Permission\Models\Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }

        // 2. Spatie Roles (for application permissions)
        $spatieRoles = ['Super_Admin', 'Pastor', 'BTS_Admin', 'Department_Lead', 'Secretary', 'Team_Lead', 'Member', 'Visitation_Staff'];
        foreach ($spatieRoles as $roleName) {
            $role = Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
            
            // Assign specific permissions to roles here
            if (in_array($roleName, ['Pastor', 'BTS_Admin', 'Super_Admin', 'Department_Lead', 'Team_Lead', 'Member'])) {
                $role->givePermissionTo([
                    'access_department_portal',
                    'portal_view_members',
                    'portal_manage_attendance'
                ]);
            }
            if (in_array($roleName, ['Pastor', 'BTS_Admin', 'Super_Admin', 'Department_Lead', 'Team_Lead'])) {
                $role->givePermissionTo([
                    'view_attendance',
                    'mark_attendance'
                ]);
            }
            if (in_array($roleName, ['Pastor', 'BTS_Admin', 'Super_Admin'])) {
                $role->givePermissionTo([
                    'bypass_attendance_lock',
                    'manage_visitations',
                    'view_sensitive_visitation_content',
                    'view_finance',
                    'manage_finance',
                    'approve_finance'
                ]);
            }
            if (in_array($roleName, ['Pastor', 'BTS_Admin', 'Super_Admin', 'Visitation_Staff', 'Department_Lead'])) {
                $role->givePermissionTo([
                    'view_visitations',
                    'create_visitation_requests'
                ]);
            }
            if ($roleName === 'Pastor') {
                $role->givePermissionTo(['view_speakers', 'manage_speakers']);
            }

            if ($roleName === 'Super_Admin') {
                // Super Admin gets EVERYTHING
                $allPerms = \Spatie\Permission\Models\Permission::all();
                $role->syncPermissions($allPerms);
            }
        }

        // 2. OrgRoles (Functional roles within the organization)
        $roleData = [
            ['name' => 'Trưởng Ban', 'code' => 'tb', 'level' => 70],
            ['name' => 'Phó Ban', 'code' => 'pb', 'level' => 60],
            ['name' => 'Thư Ký', 'code' => 'tk', 'level' => 50],
            ['name' => 'Thủ Quỹ', 'code' => 'tq', 'level' => 50],
            ['name' => 'Ủy Viên', 'code' => 'uv', 'level' => 40],
            ['name' => 'Tổ Trưởng', 'code' => 'tt', 'level' => 30],
            ['name' => 'Ban Viên', 'code' => 'bv', 'level' => 10],
            ['name' => 'Chấp Sự', 'code' => 'cs', 'level' => 80],
            ['name' => 'Thư ký Hội Thánh', 'code' => 'tkhu', 'level' => 95],
            ['name' => 'Phó thư ký', 'code' => 'ptk', 'level' => 90],
            ['name' => 'Thủ quỹ HT', 'code' => 'tqht', 'level' => 90],
            ['name' => 'Phó thủ quỹ', 'code' => 'ptq', 'level' => 85],
        ];

        $orgRoles = [];
        foreach ($roleData as $data) {
            $orgRoles[$data['code']] = OrgRole::updateOrCreate(['code' => $data['code']], $data);
        }

        // 3. Departments Framework
        
        // I. BAN LÃNH ĐẠO
        $blockLeadership = 'leadership';
        $bcs = Department::updateOrCreate(['code' => 'BCS'], ['name' => 'Ban Chấp sự', 'block' => $blockLeadership]);
        $bts = Department::updateOrCreate(['code' => 'BTS'], [
            'name' => 'Ban Trị sự', 
            'block' => $blockLeadership, 
            'parent_id' => $bcs->id
        ]);

        // II. KHỐI SINH HOẠT
        $blockActivities = 'activities';
        $activities = [
            ['code' => 'BTL', 'name' => 'Ban Trung Lão'],
            ['code' => 'BTN', 'name' => 'Ban Trung Niên'],
            ['code' => 'BTTR', 'name' => 'Ban Thanh Tráng'],
            ['code' => 'BTNI', 'name' => 'Ban Thanh Niên'],
            ['code' => 'BTNH', 'name' => 'Ban Thiếu Nhi'],
        ];
        $activityDepts = [];
        foreach ($activities as $dept) {
            $activityDepts[$dept['code']] = Department::updateOrCreate(['code' => $dept['code']], [
                'name' => $dept['name'], 
                'block' => $blockActivities
            ]);
        }

        // III. KHỐI MỤC VỤ
        $blockMinistry = 'ministry';
        $ministries = [
            ['code' => 'BCDGD', 'name' => 'Ban Cơ Đốc Giáo Dục'],
            ['code' => 'BTG', 'name' => 'Ban Truyền Giảng'],
            ['code' => 'BCDCS', 'name' => 'Ban Chứng Đạo – Chăm Sóc TTH'],
            ['code' => 'BKT', 'name' => 'Ban Kỹ Thuật'],
            ['code' => 'BNC', 'name' => 'Ban Nhạc Cụ'],
            ['code' => 'BKN', 'name' => 'Ban Kết Nối'],
            ['code' => 'BKTI', 'name' => 'Ban Khánh Tiết'],
            ['code' => 'BHC', 'name' => 'Ban Hậu Cần'],
            ['code' => 'BCN', 'name' => 'Ban Cầu Nguyện'],
            ['code' => 'BTTTT', 'name' => 'Ban Tiếp Tân – Trật Tự'],
            ['code' => 'BTTRD', 'name' => 'Ban Tương Trợ'],
            ['code' => 'BTV', 'name' => 'Ban Thăm Viếng'],
            ['code' => 'BHTP', 'name' => 'Ban Hát Thờ Phượng'],
        ];
        $ministryDepts = [];
        foreach ($ministries as $dept) {
            $ministryDepts[$dept['code']] = Department::updateOrCreate(['code' => $dept['code']], [
                'name' => $dept['name'], 
                'block' => $blockMinistry
            ]);
        }

        // 4. Functional Accounts Helper (No Spatie Roles)
        $createFunctionalAccount = function ($email, $name, $roleCode, $dept) use ($orgRoles) {
            $user = User::updateOrCreate(['email' => $email], [
                'name' => $name,
                'password' => 'Abc.1234',
                'is_superadmin' => false,
            ]);

            $member = Member::updateOrCreate(['user_id' => $user->id], [
                'member_code' => 'GEN-' . str_pad($user->id, 5, '0', STR_PAD_LEFT),
                'full_name' => $name,
                'email' => $email,
            ]);

            OrgMembership::updateOrCreate([
                'member_id' => $member->id,
                'model_id' => $dept->id,
                'model_type' => Department::class,
            ], [
                'org_role_id' => $orgRoles[$roleCode]->id,
            ]);

            return $user;
        };

        // 5. Create Representative Accounts

        // Super Admin (God Mode)
        $pastor = User::updateOrCreate(['email' => "superadmin@$domain"], [
            'name' => 'Mục sư Quản nhiệm (SuperAdmin)',
            'password' => 'Abc.1234',
            'is_superadmin' => true,
        ]);

        // Activities (Ban Thanh Tráng)
        $deptTT = $activityDepts['BTTR'];
        
        // Trưởng Ban Thanh Tráng
        $tb = $createFunctionalAccount("tb.thanhtrang@$domain", 'Trưởng ban Thanh Tráng', 'tb', $deptTT);
        
        // Thư Ký Thanh Tráng
        $tk = $createFunctionalAccount("tk.thanhtrang@$domain", 'Thư ký Thanh Tráng', 'tk', $deptTT);

        // Grant Level 2 Features for TB/TK
        // TB and TK will get specifically enabled features so they can see them on the dashboard
        $featuresToGrant = \App\Models\Feature::whereIn('slug', ['attendance', 'members', 'reports', 'assignments'])->get();
        
        foreach ($featuresToGrant as $feature) {
            \App\Models\UserDepartmentFeature::updateOrCreate([
                'user_id' => $tb->id,
                'department_id' => $deptTT->id,
                'feature_id' => $feature->id,
            ], [
                'dept_type' => 'activities',
                'is_enabled' => true,
                'access_level' => 'manage'
            ]);

            \App\Models\UserDepartmentFeature::updateOrCreate([
                'user_id' => $tk->id,
                'department_id' => $deptTT->id,
                'feature_id' => $feature->id,
            ], [
                'dept_type' => 'activities',
                'is_enabled' => true,
                'access_level' => 'manage'
            ]);
        }

        $this->command->info('Organization structure and functional accounts initialized without Legacy Roles!');
    }
}
