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
use App\Models\DepartmentSupervisor;

class DemoDataSeeder extends Seeder
{
    public function run()
    {
        // 1. Spatie Roles
        $spatieRoles = ['Pastor', 'BTS_Admin', 'Department_Lead', 'Secretary', 'Team_Lead', 'Member'];
        foreach ($spatieRoles as $role) {
            Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']);
        }

        // 2. Church
        $church = Church::create([
            'name' => 'Hội Thánh Lõi',
            'address' => 'Hồ Chí Minh',
            'phone_number' => '0123456789',
            'email' => 'contact@church.com'
        ]);

        // 3. OrgRoles
        $orgRoles = [
            'pastor' => OrgRole::create(['name' => 'Mục sư', 'code' => 'pastor', 'level' => 100]),
            'bts_admin' => OrgRole::create(['name' => 'BTS Admin', 'code' => 'bts_admin', 'level' => 90]),
            'dept_lead' => OrgRole::create(['name' => 'Trưởng ban', 'code' => 'dept_lead', 'level' => 50]),
            'secretary' => OrgRole::create(['name' => 'Thư ký', 'code' => 'secretary', 'level' => 45]),
            'team_lead' => OrgRole::create(['name' => 'Tổ trưởng', 'code' => 'team_lead', 'level' => 30]),
            'team_member' => OrgRole::create(['name' => 'Tổ viên', 'code' => 'team_member', 'level' => 10]),
        ];

        // 4. Departments & Teams
        $dept1 = Department::create(['name' => 'Ban Mục vụ', 'code' => 'BMV']);
        $dept1_team1 = Team::create(['department_id' => $dept1->id, 'name' => 'Tổ Lời Chúa', 'code' => 'TLCH']);
        $dept1_team2 = Team::create(['department_id' => $dept1->id, 'name' => 'Tổ Cầu nguyện', 'code' => 'TCN']);

        $dept2 = Department::create(['name' => 'Ban Truyền thông', 'code' => 'BTT']);
        $dept2_team1 = Team::create(['department_id' => $dept2->id, 'name' => 'Tổ Âm thanh', 'code' => 'TAT']);
        $dept2_team2 = Team::create(['department_id' => $dept2->id, 'name' => 'Tổ Hình ảnh', 'code' => 'THA']);

        // User and Member seeding logic has been removed to avoid overwriting test accounts
    }
}
