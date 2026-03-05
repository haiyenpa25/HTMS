<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Meeting;
use App\Models\Department;
use App\Models\Member;
use App\Models\MeetingAttendanceSummary;
use App\Models\MeetingFinance;
use App\Models\Visitation;
use App\Models\DepartmentReport;
use App\Models\DeaconAttendanceRecord;
use App\Models\DeaconMonthlyReport;
use App\Models\DeaconReportIncident;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class SeedPortalTestData extends Command
{
    protected $signature = 'portal:seed-test-data';
    protected $description = 'Seed realistic test data for Deacon, Ministry, and Activities Portals (last 6 months)';

    public function handle()
    {
        $this->info('🚀 Starting Portal Test Data Seeding...');

        // 1. Ensure we have some members for sampling
        $members = Member::limit(20)->get();
        if ($members->isEmpty()) {
            $this->error('❌ No members found. Please seed members first.');
            return;
        }

        // 2. Identify or Create Target Departments
        $ministryDept = Department::where('block', 'ministry')->first() 
            ?? Department::create(['name' => 'Ban Thanh Tráng (Mẫu)', 'block' => 'ministry', 'code' => 'BTT_TEST']);
        
        $activityDept = Department::where('block', 'activities')->first()
            ?? Department::create(['name' => 'Ban Trung Niên (Mẫu)', 'block' => 'activities', 'code' => 'BTN_TEST']);

        $this->info("📍 Seeding for Ministry Dept: {$ministryDept->name} (ID: {$ministryDept->id})");
        $this->info("📍 Seeding for Activity Dept: {$activityDept->name} (ID: {$activityDept->id})");

        // 3. Clear old test data for these depts to avoid duplicates (optional, but safer for re-run)
        // We'll just append but keep dates unique for Sundays.

        // 4. Loop through last 6 months
        $now = now();
        for ($i = 5; $i >= 0; $i--) {
            $monthDt = $now->copy()->subMonths($i);
            $month = $monthDt->month;
            $year = $monthDt->year;

            $this->comment("📅 Seeding data for month $month/$year...");

            // A. Create Church Meetings (Sundays)
            $sundays = $this->getSundaysOfMonth($month, $year);
            foreach ($sundays as $date) {
                // Check if meeting exists
                $meeting = Meeting::updateOrCreate(
                    ['date' => $date->toDateString(), 'type' => 'church'],
                    [
                        'time' => '08:00:00',
                        'topic' => 'Chủ đề Chúa Nhật Tuần ' . ceil($date->day / 7),
                        'scripture' => 'Thi thiên ' . rand(1, 150),
                        'memory_verse' => 'Câu gốc tuần ' . $date->day,
                        'preacher' => 'Mục sư Nhiệm chức',
                        'attendance_marked' => true,
                    ]
                );

                // B. Seed Deacon Attendance (Global Church)
                DeaconAttendanceRecord::updateOrCreate(
                    ['meeting_id' => $meeting->id],
                    [
                        'total_present' => rand(100, 150),
                        'total_online' => rand(30, 80),
                        'total_visitors' => rand(2, 10),
                        'notes' => 'Buổi nhóm phước hạnh.',
                        'recorded_by' => $members->first()->user_id ?? 1,
                    ]
                );

                // C. Seed Dept attendance for this Church meeting
                foreach ([$ministryDept, $activityDept] as $dept) {
                    MeetingAttendanceSummary::updateOrCreate(
                        ['meeting_id' => $meeting->id, 'department_id' => $dept->id],
                        [
                            'manual_count' => rand(20, 50),
                            'notes' => 'Thành viên ban ngành tham dự đầy đủ.',
                        ]
                    );
                }
            }

            // B. Create Dept Specific Meetings (Weekly or Bi-weekly)
            foreach ([$ministryDept, $activityDept] as $dept) {
                // Let's say Saturdays
                $saturdays = $this->getSaturdaysOfMonth($month, $year);
                foreach ($saturdays as $date) {
                    $deptMeeting = Meeting::updateOrCreate(
                        ['date' => $date->toDateString(), 'type' => 'department', 'department_id' => $dept->id],
                        [
                            'time' => '19:30:00',
                            'topic' => 'Bồi linh Ban ngành - ' . $dept->name,
                            'preacher' => 'Ban Điều Hành',
                        ]
                    );

                    // Attendance for Dept meeting
                    MeetingAttendanceSummary::updateOrCreate(
                        ['meeting_id' => $deptMeeting->id, 'department_id' => $dept->id],
                        [
                            'manual_count' => rand(15, 30),
                            'notes' => 'Nhóm tại phòng nhóm phụ.',
                        ]
                    );

                    // Finance records for Dept meeting
                    MeetingFinance::updateOrCreate(
                        ['meeting_id' => $deptMeeting->id, 'type' => 'thu'],
                        ['amount' => rand(200000, 1000000), 'notes' => 'Tiền dâng hiến']
                    );
                    if (rand(0, 1)) {
                        MeetingFinance::updateOrCreate(
                            ['meeting_id' => $deptMeeting->id, 'type' => 'chi'],
                            ['amount' => rand(50000, 200000), 'notes' => 'Nước uống / In ấn']
                        );
                    }
                }
            }

            // C. Seed Visitations
            foreach ([$ministryDept, $activityDept] as $dept) {
                for ($v = 0; $v < 2; $v++) {
                    Visitation::create([
                        'member_id' => $members->random()->id,
                        'department_id' => $dept->id,
                        'visitation_type' => 'department',
                        'visit_date' => $monthDt->copy()->startOfMonth()->addDays(rand(1, 25)),
                        'reason' => 'khích lệ', // Match enum: ['ốm đau', 'mới tin Chúa', 'khích lệ', 'khác']
                        'content' => 'Gia đình bình an, công việc ổn định.',
                        'status' => 'completed',
                    ]);
                }
            }

            // D. Seed Monthly Reports
            // Ministry/Activities reports
            foreach ([$ministryDept, $activityDept] as $dept) {
                DepartmentReport::updateOrCreate(
                    ['department_id' => $dept->id, 'report_month' => $month, 'report_year' => $year],
                    [
                        'reporter_name' => 'Trưởng ban ' . $dept->name,
                        'evaluation' => 'Sinh hoạt ban ngành ổn định, tinh thần anh em hiệp nhất.',
                        'proposals' => 'Hỗ trợ kinh phí cho chương trình dã ngoại sắp tới.',
                        'activities_notes' => 'Đã tổ chức nhóm bồi linh ban ngành hàng tuần.',
                        'status' => 'approved',
                    ]
                );
            }

            // Deacon Monthly Report (with YouTube)
            $deaconReport = DeaconMonthlyReport::updateOrCreate(
                ['report_month' => $month, 'report_year' => $year],
                [
                    'yt_subscribers' => 1000 + ($i * 50),
                    'yt_new_subscribers' => rand(5, 20),
                    'yt_views' => rand(2000, 8000),
                    'yt_watch_hours' => rand(50, 300),
                    'status' => 'approved',
                    'reporter_name' => 'Thư Ký Hội Thánh',
                    'evaluation' => 'Kênh truyền thông phát triển tốt.',
                    'proposals' => 'Tăng cường chất lượng hình ảnh livestream.',
                    'notes' => 'Mọi sự bình an.',
                    'submitted_by' => 1,
                ]
            );

            // Add an incident to Deacon report
            DeaconReportIncident::updateOrCreate(
                ['deacon_report_id' => $deaconReport->id, 'week_label' => 'Tuần 2'],
                [
                    'incident_description' => 'Máy tính livestream bị treo giữa chừng.',
                    'resolution' => 'Khởi động lại và tiếp tục.',
                    'direction' => 'Kiểm tra tản nhiệt và nâng cấp RAM.',
                    'status' => 'resolved',
                ]
            );
        }

        $this->info('✅ Seeding completed! Database is now ready for testing.');
    }

    private function getSundaysOfMonth($month, $year)
    {
        $sundays = [];
        $date = Carbon::createFromDate($year, $month, 1);
        $daysInMonth = $date->daysInMonth;

        for ($i = 1; $i <= $daysInMonth; $i++) {
            $dt = Carbon::createFromDate($year, $month, $i);
            if ($dt->isSunday()) {
                $sundays[] = $dt;
            }
        }
        return $sundays;
    }

    private function getSaturdaysOfMonth($month, $year)
    {
        $saturdays = [];
        $date = Carbon::createFromDate($year, $month, 1);
        $daysInMonth = $date->daysInMonth;

        for ($i = 1; $i <= $daysInMonth; $i++) {
            $dt = Carbon::createFromDate($year, $month, $i);
            if ($dt->isSaturday()) {
                $saturdays[] = $dt;
            }
        }
        return $saturdays;
    }
}
