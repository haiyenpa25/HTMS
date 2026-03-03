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
            ['code' => 'BCĐGD', 'name' => 'Ban Cơ Đốc Giáo Dục'],
            ['code' => 'BTG', 'name' => 'Ban Truyền Giảng'],
            ['code' => 'BCĐCS', 'name' => 'Ban Chứng Đạo – Chăm Sóc TTH'],
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

        // 4. Functional Accounts Helper
        $createFunctionalAccount = function ($email, $name, $roleCode, $dept, $spatieRole) use ($orgRoles) {
            $user = User::updateOrCreate(['email' => $email], [
                'name' => $name,
                'password' => Hash::make('Abc.1234')
            ]);
            $user->assignRole($spatieRole);

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

        // Super Admin (Pastor)
        $pastor = User::updateOrCreate(['email' => 'superadmin@httlthanhmyloi.com'], [
            'name' => 'Mục sư Quản nhiệm',
            'password' => Hash::make('Abc.1234')
        ]);
        $pastor->assignRole('Pastor');
        $pastor->assignRole('Super_Admin');

        // Activities (Ban Thanh Tráng)
        $deptTT = $activityDepts['BTTR'];
        $createFunctionalAccount('tb.thanhtrang@httlthanhmyloi.com', 'Trưởng ban Thanh Tráng', 'tb', $deptTT, 'Department_Lead');
        $createFunctionalAccount('pb.thanhtrang@httlthanhmyloi.com', 'Phó ban Thanh Tráng', 'pb', $deptTT, 'Department_Lead');
        $createFunctionalAccount('tk.thanhtrang@httlthanhmyloi.com', 'Thư ký Thanh Tráng', 'tk', $deptTT, 'Secretary');
        $createFunctionalAccount('tq.thanhtrang@httlthanhmyloi.com', 'Thủ quỹ Thanh Tráng', 'tq', $deptTT, 'Secretary');
        $createFunctionalAccount('tt.thanhtrang@httlthanhmyloi.com', 'Tổ trưởng Thanh Tráng', 'tt', $deptTT, 'Team_Lead');

        $this->command->info('Organization structure and functional accounts initialized!');
    }
}
