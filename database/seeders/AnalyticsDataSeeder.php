<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Meeting;
use App\Models\MeetingAttendanceSummary;
use App\Models\Department;
use App\Models\Visitation;
use App\Models\Member;
use App\Models\DepartmentFund;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AnalyticsDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $activities = Department::where('block', 'activities')->get();
        if ($activities->isEmpty()) return;

        $monthStart = Carbon::now()->startOfMonth();
        $monthEnd = Carbon::now()->endOfMonth();
        
        // Clean old test data
        Meeting::whereBetween('date', [$monthStart->toDateString(), $monthEnd->toDateString()])->delete();

        // 1. Department Meetings (5 weeks)
        foreach ($activities as $dept) {
            for ($week = 1; $week <= 5; $week++) {
                $date = $monthStart->copy()->addDays(($week - 1) * 7);
                if ($date->gt($monthEnd)) break;

                $meeting = Meeting::create([
                    'type' => 'department',
                    'department_id' => $dept->id,
                    'date' => $date->toDateString(),
                    'time' => '19:00:00',
                    'topic' => 'Demo Topic Tuần ' . $week,
                    'attendance_marked' => true,
                ]);

                MeetingAttendanceSummary::create([
                    'meeting_id' => $meeting->id,
                    'department_id' => $dept->id,
                    'manual_count' => rand(20, 150),
                ]);
            }
        }

        // 2. Church Meetings
        // Find all Sundays in the month
        $sundays = [];
        for ($date = $monthStart->copy(); $date->lte($monthEnd); $date->addDay()) {
            if ($date->isSunday()) {
                $sundays[] = $date->copy();
            }
        }

        foreach ($sundays as $sunday) {
            $meeting = Meeting::create([
                'type' => 'church',
                'department_id' => null, // One meeting for the whole church
                'date' => $sunday->toDateString(),
                'time' => '08:00:00',
                'topic' => 'Thờ phượng sáng Chúa Nhật',
                'attendance_marked' => true,
            ]);

            foreach ($activities as $dept) {
                MeetingAttendanceSummary::create([
                    'meeting_id' => $meeting->id,
                    'department_id' => $dept->id,
                    'manual_count' => rand(10, 80),
                ]);
            }
        }

        // 3. Transactions 3 months
        foreach ($activities as $dept) {
            $fund = DepartmentFund::firstOrCreate(
                ['department_id' => $dept->id],
                ['name' => 'Quỹ ' . $dept->name]
            );
            
            for ($i = 0; $i < 3; $i++) {
                $txDate = Carbon::now()->subMonths($i)->startOfMonth()->addDays(rand(1, 20));

                DB::table('department_transactions')->insert([
                    'department_fund_id' => $fund->id,
                    'type' => 'income',
                    'amount' => rand(1000000, 5000000),
                    'category' => 'Thu quỹ sinh hoạt',
                    'description' => 'Dâng hiến',
                    'transaction_date' => $txDate->toDateString(),
                    'status' => 'approved',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                DB::table('department_transactions')->insert([
                    'department_fund_id' => $fund->id,
                    'type' => 'expense',
                    'amount' => rand(500000, 3000000),
                    'category' => 'Chi phí hoạt động',
                    'description' => 'Sinh hoạt phí',
                    'transaction_date' => $txDate->copy()->addDays(2)->toDateString(),
                    'status' => 'approved',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // 4. Visitations (6 months)
        $member = Member::first();
        if ($member) {
            for ($i = 0; $i < 6; $i++) {
                $count = rand(0, 15);
                for ($j = 0; $j < $count; $j++) {
                    $vDate = Carbon::now()->subMonths($i)->startOfMonth()->addDays(rand(1, 28));
                     visitation::create([
                        'member_id' => $member->id,
                        'department_id' => $activities->random()->id,
                        'reason' => 'khích lệ',
                        'visit_date' => $vDate->toDateString(),
                        'status' => rand(0,1) ? 'completed' : 'planned',
                    ]);
                }
            }
        }

        // 5. Evangelistic Guests (Ban Truyền Giảng ID 9)
        $evangelisticDept = Department::find(9);
        if ($evangelisticDept) {
            $meeting = Meeting::create([
                'type' => 'department',
                'department_id' => 9,
                'date' => Carbon::now()->startOfMonth()->addDays(10)->toDateString(),
                'time' => '19:00:00',
                'topic' => 'Truyền giảng đặc biệt',
                'attendance_marked' => true,
            ]);

            MeetingAttendanceSummary::create([
                'meeting_id' => $meeting->id,
                'department_id' => 9,
                'manual_count' => rand(10, 50),
            ]);
        }
    }
}
