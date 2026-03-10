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
            ['email' => env('CHURCH_EMAIL', 'contact@' . env('SYSTEM_DOMAIN', 'httlthanhmyloi.com'))],
            [
                'name' => env('CHURCH_NAME', 'Hội Thánh Tin Lành'),
                'address' => env('CHURCH_ADDRESS', 'Địa chỉ Hội Thánh'),
                'phone_number' => env('CHURCH_PHONE', '0123456789'),
            ]
        );

        // Accounts have been moved to OrgStructureSeeder

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
