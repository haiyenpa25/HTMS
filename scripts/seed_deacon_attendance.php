<?php
/**
 * Script seed dữ liệu điểm danh thực tế cho Deacon Portal
 * Chạy: php artisan tinker --execute="require 'scripts/seed_deacon_attendance.php';"
 */

use App\Models\Meeting;
use App\Models\DeaconAttendanceRecord;
use App\Models\DeaconMonthlyReport;
use Carbon\Carbon;

// 1. Kiểm tra meetings church hiện có
$churchMeetings = Meeting::where('type', 'church')
    ->orderBy('date', 'desc')
    ->take(20)
    ->get();

echo "=== Church Meetings (" . $churchMeetings->count() . " bản ghi gần nhất) ===" . PHP_EOL;
foreach ($churchMeetings as $m) {
    echo "ID:{$m->id} | {$m->date} | " . ($m->topic ?? 'N/A') . PHP_EOL;
}

// 2. Seed dữ liệu điểm danh cho 3 tháng gần nhất
$now = Carbon::now();
$adminUserId = \App\Models\User::first()?->id ?? 1;

$seeded = 0;
for ($monthOffset = 0; $monthOffset <= 5; $monthOffset++) {
    $dt = $now->copy()->subMonths($monthOffset);
    $monthStart = $dt->copy()->startOfMonth()->toDateString();
    $monthEnd = $dt->copy()->endOfMonth()->toDateString();

    $monthMeetings = Meeting::where('type', 'church')
        ->whereBetween('date', [$monthStart, $monthEnd])
        ->get();

    foreach ($monthMeetings as $meeting) {
        // Tạo hoặc cập nhật record với số liệu thực tế giả định
        // Số người hiện diện: dao động từ 85-150 người (thực tế của một hội thánh trung bình)
        $baseAttendance = rand(90, 140);
        $online = rand(15, 45);
        $visitors = rand(2, 10);

        DeaconAttendanceRecord::updateOrCreate(
            ['meeting_id' => $meeting->id],
            [
                'total_present'  => $baseAttendance,
                'total_online'   => $online,
                'total_visitors' => $visitors,
                'total_children' => rand(10, 25),
                'recorded_by'    => $adminUserId,
                'notes'          => $monthOffset === 0 ? 'Buổi nhóm tháng ' . $dt->month . '/' . $dt->year : null,
            ]
        );

        // Cập nhật attendance_marked = true
        $meeting->update(['attendance_marked' => true]);
        $seeded++;
    }
}

echo PHP_EOL . "=== Đã seed $seeded bản ghi điểm danh ===" . PHP_EOL;

// 3. Seed YouTube stats cho 3 tháng
for ($i = 2; $i >= 0; $i--) {
    $dt = $now->copy()->subMonths($i);
    $subscribers = 1200 + ($i === 0 ? 0 : ($i === 1 ? -45 : -90));
    $newSubs = rand(8, 25);

    DeaconMonthlyReport::updateOrCreate(
        ['report_month' => $dt->month, 'report_year' => $dt->year],
        [
            'yt_subscribers'     => $subscribers,
            'yt_new_subscribers' => $newSubs,
            'yt_views'           => rand(800, 2500),
            'yt_watch_hours'     => rand(120, 400),
            'status'             => 'draft',
            'submitted_by'       => $adminUserId,
            'reporter_name'      => 'CS. Nguyễn Văn Thư',
            'evaluation'         => $i === 0 ? 'Sinh hoạt Hội Thánh tháng ' . $dt->month . '/' . $dt->year . ' diễn ra ổn định. Số lượng tham dự duy trì tốt.' : null,
        ]
    );
}

echo "=== Đã seed YouTube stats ===" . PHP_EOL;
echo "DONE!" . PHP_EOL;
