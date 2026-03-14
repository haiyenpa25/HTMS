<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Department;
use App\Models\User;
use App\Models\Member;
use App\Models\OrgMembership;
use App\Models\OrgRole;
use App\Models\Team;
use App\Models\Meeting;
use App\Models\MeetingAttendance;
use App\Models\MeetingAttendanceSummary;
use App\Models\MeetingFinance;
use App\Models\DutyAssignment;
use App\Models\DepartmentRole;
use App\Models\DepartmentReport;
use App\Models\Visitation;
use App\Models\VisitationReason;
use Carbon\Carbon;
use Illuminate\Support\Str;

class DeptActivitiesSeeder extends Seeder
{
    public function run()
    {
        // Get BTTR (Ban Thanh Tráng)
        $dept = Department::where('code', 'BTTR')->first();
        if (!$dept) {
            $this->command->error("Không tìm thấy Ban Thanh Tráng (BTTR). Vui lòng chạy db:seed trước.");
            return;
        }

        $this->command->info("Bắt đầu Seed dữ liệu cho Ban Thanh Tráng (ID: {$dept->id})");

        // 1. Create Members & Teams
        $teams = [];
        for ($i = 1; $i <= 3; $i++) {
            $teams[] = Team::updateOrCreate(
                ['department_id' => $dept->id, 'code' => 'BTTR_T' . $i],
                ['name' => "Tổ {$i}"]
            );
        }

        $bvRole = OrgRole::where('code', 'bv')->first();
        if (!$bvRole) {
            $bvRole = OrgRole::create(['name' => 'Ban Viên', 'code' => 'bv', 'level' => 10]);
        }

        $members = [];
        for ($i = 1; $i <= 15; $i++) {
            $name = "Thanh Tráng Member {$i}";
            $member = Member::firstOrCreate(
                ['email' => "tt_member{$i}@example.com"],
                [
                    'member_code' => 'BTTR-' . str_pad($i, 4, '0', STR_PAD_LEFT),
                    'full_name' => $name,
                    'phone' => '090' . rand(1000000, 9999999),
                    'gender' => $i % 2 == 0 ? 'female' : 'male',
                    'date_of_birth' => Carbon::parse('1990-01-01')->addDays(rand(1, 3650))->format('Y-m-d'),
                ]
            );

            // Assign to Department
            OrgMembership::firstOrCreate([
                'member_id' => $member->id,
                'model_type' => Department::class,
                'model_id' => $dept->id,
            ], [
                'org_role_id' => $bvRole->id,
            ]);

            // Assign to Team
            $team = $teams[array_rand($teams)];
            OrgMembership::firstOrCreate([
                'member_id' => $member->id,
                'model_type' => Team::class,
                'model_id' => $team->id,
            ], [
                'org_role_id' => $bvRole->id,
            ]);

            $members[] = $member;
        }

        // 2. Setup Department Roles for Duty Assignments
        $roles = ['Hướng Dẫn', 'Cầu Nguyện', 'Đọc Kinh Thánh', 'Đàn', 'Múa'];
        $deptRoles = [];
        foreach ($roles as $r) {
            $deptRoles[] = DepartmentRole::firstOrCreate(
                ['department_id' => $dept->id, 'name' => $r]
            );
        }

        // 3. Create Meetings (Past 4 weeks)
        $meetings = [];
        for ($i = 0; $i < 4; $i++) {
            $date = Carbon::now()->subWeeks($i)->startOfWeek()->addDays(6); // Saturday
            $meeting = Meeting::updateOrCreate(
                [
                    'department_id' => $dept->id,
                    'type' => 'department',
                    'date' => $date->format('Y-m-d')
                ],
                [
                    'topic' => "Chủ đề học KT tuần " . ($i+1),
                    'scripture' => "Giăng 3:16",
                    'memory_verse' => "Giăng 3:16",
                    'time' => '19:30:00'
                ]
            );
            $meetings[] = $meeting;

            // --- 3.1 Seeding Attendance Summary
            $presentCount = rand(10, 15);
            MeetingAttendanceSummary::updateOrCreate(
                ['meeting_id' => $meeting->id, 'department_id' => $dept->id],
                ['manual_count' => $presentCount, 'notes' => 'Điểm danh lưu hệ thống']
            );

            // --- 3.2 Seeding Individual Attendance
            foreach ($members as $mem) {
                $status = rand(1, 10) > 2 ? 'present' : (rand(1, 10) > 5 ? 'absent' : 'excused');
                MeetingAttendance::updateOrCreate(
                    ['meeting_id' => $meeting->id, 'member_id' => $mem->id],
                    [
                        'status' => $status,
                        'memorized_verse' => $status == 'present' && rand(1,10) > 5,
                    ]
                );
            }

            // --- 3.3 Seeding Finances (Thu / Chi)
            MeetingFinance::where('meeting_id', $meeting->id)->delete();
            MeetingFinance::create([
                'meeting_id' => $meeting->id,
                'type' => 'thu',
                'amount' => rand(500, 2000) * 1000,
                'category' => 'Dâng hiến hằng tuần',
                'status' => 'approved'
            ]);
            
            if (rand(1, 10 ) > 5) { // 50% chance có chi
                MeetingFinance::create([
                    'meeting_id' => $meeting->id,
                    'type' => 'chi',
                    'amount' => rand(100, 300) * 1000,
                    'category' => 'Giải khát',
                    'status' => 'approved'
                ]);
            }

            // --- 3.4 Seeding Duty Assignments (Phân công)
            foreach ($deptRoles as $dRole) {
                DutyAssignment::updateOrCreate(
                    [
                        'meeting_id' => $meeting->id,
                        'department_role_id' => $dRole->id,
                    ],
                    [
                        'member_id' => $members[array_rand($members)]->id,
                        'slot' => 1
                    ]
                );
            }
        }

        // 4. Create Department Reports (Monthly)
        DepartmentReport::updateOrCreate(
            [
                'department_id' => $dept->id,
                'report_month' => Carbon::now()->month,
                'report_year' => Carbon::now()->year,
            ],
            [
                'reporter_name' => 'Thư ký Ban Thanh Tráng',
                'evaluation' => "Sinh hoạt đều đặn, số lượng thành viên dự nhóm trung bình 80%.",
                'request' => "Xin thêm ngân sách cho kỳ Trại Hè.",
                'proposals' => "Các thành viên tích cực tham gia học Kinh Thánh, đề nghị biểu dương.",
                'upcoming_plan' => [
                    ['task' => 'Tổ chức picnic tháng tới', 'person_in_charge' => 'Trưởng ban', 'deadline' => Carbon::now()->addDays(20)->format('Y-m-d')],
                ],
                'status' => 'approved',
                'activities_notes' => 'Tháng này có 2 thân hữu tham gia.'
            ]
        );

        // 5. Create Visitations
        $user = User::whereHas('roles', function($q){ $q->where('name', 'Department_Lead'); })->first();
        if($user && $user->member_id) {
            for ($i=0; $i<3; $i++) {
                $visitation = Visitation::create([
                    'member_id' => $members[array_rand($members)]->id,
                    'visitation_type' => 'department',
                    'department_id' => $dept->id,
                    'visit_date' => Carbon::now()->subDays(rand(1, 15))->format('Y-m-d'),
                    'reason' => 'ốm đau',
                    'content' => "Thành viên bị bệnh nhẹ, đã ổn định.",
                    'prayer_points' => "Cầu nguyện cho sức khỏe mau hồi phục.",
                    'gifts' => "Giỏ trái cây"
                ]);
                
                // Add visitor logic
                \DB::table('visitation_visitors')->insert([
                    'visitation_id' => $visitation->id,
                    'visitor_id' => $user->member_id,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now()
                ]);
            }
        }

        $this->command->info("Seed dữ liệu 6 Module Cổng Sinh Hoạt Ban Ngành (Thanh Tráng) hoàn tất!");
    }
}
