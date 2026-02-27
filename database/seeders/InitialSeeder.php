<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use App\Models\Church;
use App\Models\User;
use App\Models\Member;
use App\Models\MemberSensitive;
use App\Models\OrgRole;
use App\Models\Department;
use App\Models\Team;

class InitialSeeder extends Seeder
{
    public function run()
    {
        // 1. Roles (Spatie)
        $roles = ['Pastor', 'BTS_Admin', 'Department_Lead', 'Secretary', 'Team_Lead', 'Member'];
        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']);
        }

        // 2. OrgRoles (Legacy/App Logic)
        $orgRoles = [
            'pastor' => OrgRole::firstOrCreate(['code' => 'pastor'], ['name' => 'Mục sư', 'level' => 100]),
            'bts_admin' => OrgRole::firstOrCreate(['code' => 'bts_admin'], ['name' => 'BTS Admin', 'level' => 90]),
            'dept_lead' => OrgRole::firstOrCreate(['code' => 'dept_lead'], ['name' => 'Trưởng ban', 'level' => 50]),
            'team_lead' => OrgRole::firstOrCreate(['code' => 'team_lead'], ['name' => 'Tổ trưởng', 'level' => 30]),
            'member' => OrgRole::firstOrCreate(['code' => 'team_member'], ['name' => 'Tổ viên', 'level' => 10]),
        ];

        // 3. Church
        $church = Church::firstOrCreate(
            ['email' => 'contact@httlthanhmyloi.com'],
            [
                'name' => 'Hội Thánh Tin Lành Thạnh Mỹ Lợi',
                'address' => 'Quận 2, TP. Hồ Chí Minh',
                'phone_number' => '0123456789',
            ]
        );

        // 4. Create Super Admin User
        $user = User::updateOrCreate(
            ['email' => 'superadmin@httlthanhmyloi.com'],
            [
                'name' => 'Quản trị viên Hệ thống',
                'password' => Hash::make('Abc.1234'),
            ]
        );

        // Assign Pastor role (highest in system)
        $user->assignRole('Pastor');

        // 5. Create Member profile for Super Admin
        $member = Member::updateOrCreate(
            ['user_id' => $user->id],
            [
                'member_code' => 'MBR-00001',
                'full_name' => 'Quản trị viên',
                'email' => 'superadmin@httlthanhmyloi.com',
                'phone' => '0901234567',
            ]
        );

        MemberSensitive::updateOrCreate(
            ['member_id' => $member->id],
            ['id_card_number' => '000000000000']
        );

        // 6. Basic Departments & Teams
        $dept1 = Department::firstOrCreate(['code' => 'BMV'], ['name' => 'Ban Mục vụ']);
        Team::firstOrCreate(['code' => 'TLCH'], ['department_id' => $dept1->id, 'name' => 'Tổ Lời Chúa']);
        Team::firstOrCreate(['code' => 'TCN'], ['department_id' => $dept1->id, 'name' => 'Tổ Cầu nguyện']);

        $dept2 = Department::firstOrCreate(['code' => 'BTT'], ['name' => 'Ban Truyền thông']);
        Team::firstOrCreate(['code' => 'TAT'], ['department_id' => $dept2->id, 'name' => 'Tổ Âm thanh']);
        Team::firstOrCreate(['code' => 'THA'], ['department_id' => $dept2->id, 'name' => 'Tổ Hình ảnh']);

        $this->command->info('Super Admin and Basic Structure created successfully!');
    }
}
