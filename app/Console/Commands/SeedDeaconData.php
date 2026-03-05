<?php

namespace App\Console\Commands;

use App\Models\DeaconAttendanceRecord;
use App\Models\DeaconMonthlyReport;
use App\Models\Meeting;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SeedDeaconData extends Command
{
    protected $signature = 'deacon:seed-data';
    protected $description = 'Seed dữ liệu điểm danh và báo cáo thực tế cho Deacon Portal';

    public function handle(): int
    {
        $adminUser = User::first();
        $adminId = $adminUser?->id ?? 1;
        $now = Carbon::now();

        $this->info('=== Kiểm tra buổi nhóm church ===');
        $allChurch = Meeting::where('type', 'church')->count();
        $this->line("Tổng buổi nhóm church: $allChurch");

        // Lấy tất cả meetings church trong 6 tháng qua
        $from = $now->copy()->subMonths(5)->startOfMonth()->toDateString();
        $to   = $now->copy()->endOfMonth()->toDateString();

        $meetings = Meeting::where('type', 'church')
            ->whereBetween('date', [$from, $to])
            ->orderBy('date')
            ->get();

        $this->info("Số buổi nhóm từ {$from} đến {$to}: " . $meetings->count());

        if ($meetings->isEmpty()) {
            $this->warn('Không có buổi nhóm church! Hãy kiểm tra bảng meetings có cột type=church không.');
            // Thử xem cột type có gì
            $sample = Meeting::take(5)->get(['id', 'type', 'date', 'topic']);
            foreach ($sample as $s) {
                $this->line("  Meeting: {$s->id} | type={$s->type} | {$s->date} | " . ($s->topic ?? 'NoTopic'));
            }
            return 1;
        }

        // Seed điểm danh
        $seeded = 0;
        foreach ($meetings as $meeting) {
            $meetingDate = Carbon::parse($meeting->date);
            $monthAge = $now->diffInMonths($meetingDate);

            // Số người dao động thực tế: tháng xa hơn có thể thấp hơn
            $basePresent = rand(85, 130);
            $online = rand(12, 40);
            $visitors = rand(1, 8);
            $children = rand(8, 22);

            DeaconAttendanceRecord::updateOrCreate(
                ['meeting_id' => $meeting->id],
                [
                    'total_present'  => $basePresent,
                    'total_online'   => $online,
                    'total_visitors' => $visitors,
                    'total_children' => $children,
                    'recorded_by'    => $adminId,
                    'notes'          => null,
                ]
            );

            Meeting::where('id', $meeting->id)->update(['attendance_marked' => true]);
            $seeded++;
            $this->line("  ✓ Seeded: {$meeting->date} | HD={$basePresent} Online={$online}");
        }

        $this->info("Đã seed {$seeded} bản ghi điểm danh.");

        // Seed YouTube stats cho 6 tháng
        $this->info('=== Seed YouTube stats ===');
        $baseSubscribers = 1150;
        for ($i = 5; $i >= 0; $i--) {
            $dt = $now->copy()->subMonths($i);
            $subs = $baseSubscribers + ($i * -15) + rand(-5, 5);
            $newSubs = rand(8, 22);
            $views = rand(600, 2200);
            $watchHours = rand(80, 350);

            DeaconMonthlyReport::updateOrCreate(
                ['report_month' => $dt->month, 'report_year' => $dt->year],
                [
                    'yt_subscribers'     => $subs,
                    'yt_new_subscribers' => $newSubs,
                    'yt_views'           => $views,
                    'yt_watch_hours'     => $watchHours,
                    'status'             => $i === 0 ? 'draft' : 'approved',
                    'submitted_by'       => $adminId,
                    'reporter_name'      => 'CS. Nguyễn Thị Thư Ký',
                    'evaluation'         => $i === 0
                        ? "Sinh hoạt Hội Thánh tháng {$dt->month}/{$dt->year}: Số lượng tham dự ổn định. Kênh YouTube tăng trưởng tốt với {$newSubs} đăng ký mới."
                        : "Tháng {$dt->month}/{$dt->year} - Đã phê duyệt.",
                    'proposals'          => $i === 0 ? 'Dự kiến tổ chức buổi thờ phượng đặc biệt vào cuối tháng.' : null,
                ]
            );
            $this->line("  ✓ YouTube {$dt->month}/{$dt->year}: subs={$subs} new={$newSubs} views={$views}");
        }

        $this->info('✅ DONE! Tất cả dữ liệu Deacon Portal đã được seed.');
        $this->line("  - Bản ghi điểm danh: " . DeaconAttendanceRecord::count());
        $this->line("  - Báo cáo tháng: " . DeaconMonthlyReport::count());

        return 0;
    }
}
