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

        // Helper func
        $createMember = function ($name, $email, $roleName) {
            $user = User::create([
                'name' => $name,
                'email' => $email,
                'password' => Hash::make('password')
            ]);
            $user->assignRole($roleName);

            $member = Member::create([
                'user_id' => $user->id,
                'member_code' => 'MBR-' . str_pad($user->id, 5, '0', STR_PAD_LEFT),
                'full_name' => $name,
                'email' => $email,
                'phone' => '090000000' . rand(0, 9),
            ]);

            MemberSensitive::create([
                'member_id' => $member->id,
                'id_card_number' => '0123456789' . rand(10, 99),
            ]);

            return $member;
        };

        // 5. Generate Members
        $pastor = $createMember('Pastor Nam', 'pastor@church.com', 'Pastor');
        
        $deptLead1 = $createMember('Lead MV', 'lead1@church.com', 'Department_Lead');
        $deptLead2 = $createMember('Lead TT', 'lead2@church.com', 'Department_Lead');
        
        $teamLead1_1 = $createMember('Lead Lời Chúa', 'tlead1@church.com', 'Team_Lead');
        $teamLead1_2 = $createMember('Lead Cầu nguyện', 'tlead2@church.com', 'Team_Lead');
        $teamLead2_1 = $createMember('Lead Âm thanh', 'tlead3@church.com', 'Team_Lead');
        $teamLead2_2 = $createMember('Lead Hình ảnh', 'tlead4@church.com', 'Team_Lead');
        
        // Members
        $membersDept1Team1 = [$createMember('Member A1', 'mem1@church.com', 'Member'), $createMember('Member A2', 'mem2@church.com', 'Member')];
        $membersDept2Team1 = [$createMember('Member B1', 'mem3@church.com', 'Member'), $createMember('Member B2', 'mem4@church.com', 'Member')];
        $membersDept2Team2 = [$createMember('Member C1', 'mem5@church.com', 'Member')];

        // 6. Assign OrgMemberships
        // Pastor
        DepartmentSupervisor::create(['department_id' => $dept1->id, 'member_id' => $pastor->id]);
        DepartmentSupervisor::create(['department_id' => $dept2->id, 'member_id' => $pastor->id]);

        // Dept Leads
        OrgMembership::create(['member_id' => $deptLead1->id, 'org_role_id' => $orgRoles['dept_lead']->id, 'model_id' => $dept1->id, 'model_type' => Department::class]);
        OrgMembership::create(['member_id' => $deptLead2->id, 'org_role_id' => $orgRoles['dept_lead']->id, 'model_id' => $dept2->id, 'model_type' => Department::class]);

        // Team Leads
        OrgMembership::create(['member_id' => $teamLead1_1->id, 'org_role_id' => $orgRoles['team_lead']->id, 'model_id' => $dept1_team1->id, 'model_type' => Team::class]);
        OrgMembership::create(['member_id' => $teamLead1_2->id, 'org_role_id' => $orgRoles['team_lead']->id, 'model_id' => $dept1_team2->id, 'model_type' => Team::class]);
        OrgMembership::create(['member_id' => $teamLead2_1->id, 'org_role_id' => $orgRoles['team_lead']->id, 'model_id' => $dept2_team1->id, 'model_type' => Team::class]);
        OrgMembership::create(['member_id' => $teamLead2_2->id, 'org_role_id' => $orgRoles['team_lead']->id, 'model_id' => $dept2_team2->id, 'model_type' => Team::class]);

        // Assgin regular members
        foreach($membersDept1Team1 as $m) OrgMembership::create(['member_id' => $m->id, 'org_role_id' => $orgRoles['team_member']->id, 'model_id' => $dept1_team1->id, 'model_type' => Team::class]);
        foreach($membersDept2Team1 as $m) OrgMembership::create(['member_id' => $m->id, 'org_role_id' => $orgRoles['team_member']->id, 'model_id' => $dept2_team1->id, 'model_type' => Team::class]);
        foreach($membersDept2Team2 as $m) OrgMembership::create(['member_id' => $m->id, 'org_role_id' => $orgRoles['team_member']->id, 'model_id' => $dept2_team2->id, 'model_type' => Team::class]);
        
        // Multi-Team / Multi-Dept Test Case
        // A common secretary for both teams in Dept1
        $secretaryMv = $createMember('Sec MV', 'secmv@church.com', 'Secretary');
        OrgMembership::create(['member_id' => $secretaryMv->id, 'org_role_id' => $orgRoles['secretary']->id, 'model_id' => $dept1_team1->id, 'model_type' => Team::class]);
        OrgMembership::create(['member_id' => $secretaryMv->id, 'org_role_id' => $orgRoles['secretary']->id, 'model_id' => $dept1_team2->id, 'model_type' => Team::class]);

        // And Team Lead 1_1 is also a regular member of Dept 2 Team 1 (for array cross checking)
        OrgMembership::create(['member_id' => $teamLead1_1->id, 'org_role_id' => $orgRoles['team_member']->id, 'model_id' => $dept2_team1->id, 'model_type' => Team::class]);
    }
}
