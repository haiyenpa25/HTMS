<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * Seeder tạo dữ liệu mẫu cho Ban Thanh Tráng - Tháng 3/2026
 * Chạy: php artisan db:seed --class=ThanhTrangMarch2026Seeder
 */
class ThanhTrangMarch2026Seeder extends Seeder
{
    public function run(): void
    {
        // ── Tìm Ban Thanh Tráng ──────────────────────────────────
        $dept = DB::table('departments')
            ->where('block', 'activities')
            ->where(function ($q) {
                $q->where('name', 'like', '%Thanh Tráng%')
                  ->orWhere('name', 'like', '%Thanh Trang%')
                  ->orWhere('name', 'like', '%thanh trang%');
            })
            ->first();

        if (!$dept) {
            $this->command->error('❌ Không tìm thấy Ban Thanh Tráng! Hãy kiểm tra tên ban trong bảng departments.');
            $this->command->info('Danh sách ban hiện có:');
            DB::table('departments')->where('block', 'activities')->get(['id', 'name'])->each(function ($d) {
                $this->command->line("  - [{$d->id}] {$d->name}");
            });
            return;
        }

        $deptId = $dept->id;
        $this->command->info("✅ Tìm thấy: [{$deptId}] {$dept->name}");

        // ── Tìm một speaker/member để dùng ──────────────────────
        $speakerId = DB::table('members')->value('id'); // lấy member đầu tiên làm diễn giả mẫu

        // ════════════════════════════════════════════════════════
        // BUỔI NHÓM HỘI THÁNH (type = 'church')
        // Chủ Nhật hàng tuần tháng 3/2026
        // 01/03, 08/03, 15/03, 22/03, 29/03
        // ════════════════════════════════════════════════════════
        $churchMeetings = [
            [
                'date'         => '2026-03-01',
                'time'         => '09:00:00',
                'topic'        => 'Sống Theo Lời Chúa',
                'scripture'    => 'Thi-thiên 119:9-16',
                'memory_verse' => '"Lời Chúa là đèn cho chân tôi" - Thi 119:105',
                'preacher'     => 'MS. Nguyễn Văn A',
            ],
            [
                'date'         => '2026-03-08',
                'time'         => '09:00:00',
                'topic'        => 'Tình Yêu Thương Của Đức Chúa Trời',
                'scripture'    => 'Giăng 3:16-21',
                'memory_verse' => '"Vì Đức Chúa Trời yêu thích thế gian" - Gi 3:16',
                'preacher'     => 'MS. Trần Văn B',
            ],
            [
                'date'         => '2026-03-15',
                'time'         => '09:00:00',
                'topic'        => 'Đức Tin Và Việc Làm',
                'scripture'    => 'Gia-cơ 2:14-26',
                'memory_verse' => '"Đức tin không có việc làm là đức tin chết" - Gc 2:17',
                'preacher'     => 'MS. Lê Văn C',
            ],
            [
                'date'         => '2026-03-22',
                'time'         => '09:00:00',
                'topic'        => 'Cầu Nguyện Trong Thánh Linh',
                'scripture'    => 'Ê-phê-sô 6:18-20',
                'memory_verse' => '"Hãy cầu nguyện không thôi" - 1Tê 5:17',
                'preacher'     => 'MS. Nguyễn Văn A',
            ],
            [
                'date'         => '2026-03-29',
                'time'         => '09:00:00',
                'topic'        => 'Phục Sinh Và Niềm Hi Vọng',
                'scripture'    => '1 Cô-rinh-tô 15:12-22',
                'memory_verse' => '"Ta là sự phục sinh và sự sống" - Gi 11:25',
                'preacher'     => 'MS. Phạm Văn D',
            ],
        ];

        $churchAttendances = [32, 35, 28, 40, 25]; // hiện diện HT của ban Thanh Tráng

        foreach ($churchMeetings as $i => $m) {
            $meetingId = DB::table('meetings')->insertGetId([
                'type'          => 'church',
                'department_id' => null, // church-wide
                'date'          => $m['date'],
                'time'          => $m['time'],
                'topic'         => $m['topic'],
                'scripture'     => $m['scripture'],
                'memory_verse'  => $m['memory_verse'],
                'preacher'      => $m['preacher'],
                'created_at'    => now(),
                'updated_at'    => now(),
            ]);

            // Attendance summary cho Ban Thanh Tráng
            DB::table('meeting_attendance_summaries')->insertOrIgnore([
                'meeting_id'   => $meetingId,
                'department_id'=> $deptId,
                'manual_count' => $churchAttendances[$i],
                'notes'        => null,
                'created_at'   => now(),
                'updated_at'   => now(),
            ]);

            $this->command->line("  🏛 HT [{$m['date']}] {$m['topic']} - HD: {$churchAttendances[$i]}");
        }

        // ════════════════════════════════════════════════════════
        // BUỔI NHÓM BAN NGÀNH (type = 'department')
        // Thứ 7 hàng tuần tháng 3/2026
        // 07/03, 14/03, 21/03, 28/03
        // ════════════════════════════════════════════════════════
        $deptMeetingData = [
            [
                'date'         => '2026-03-07',
                'time'         => '18:00:00',
                'topic'        => 'Bài Học: Giô-sép Và Lòng Kiên Nhẫn',
                'scripture'    => 'Sáng Thế Ký 39:1-23',
                'memory_verse' => '"Cha tôi đã định ác cho tôi, song Đức Chúa Trời định điều lành" - St 50:20',
                'preacher'     => 'CS. Nguyễn Thị E',
                'attendance'   => 18,
                'income'       => 850000,
                'expense'      => 200000,
                'income_cat'   => 'Tiền hộp tuần',
                'expense_cat'  => 'Chi hoạt động',
            ],
            [
                'date'         => '2026-03-14',
                'time'         => '18:00:00',
                'topic'        => 'Thanh Niên Sống Thánh Khiết',
                'scripture'    => '1 Ti-mô-thê 4:12',
                'memory_verse' => '"Chớ ai khinh con vì trẻ tuổi" - 1Ti 4:12',
                'preacher'     => 'CS. Trần Văn F',
                'attendance'   => 22,
                'income'       => 1200000,
                'expense'      => 350000,
                'income_cat'   => 'Tiền hộp tuần',
                'expense_cat'  => 'Chi thăm viếng',
            ],
            [
                'date'         => '2026-03-21',
                'time'         => '18:00:00',
                'topic'        => 'Dâng Hiến Và Phước Lành',
                'scripture'    => 'Ma-la-chi 3:10',
                'memory_verse' => '"Hãy đem hết thảy phần mười vào kho" - Mal 3:10',
                'preacher'     => 'CS. Lê Thị G',
                'attendance'   => 20,
                'income'       => 950000,
                'expense'      => 0,
                'income_cat'   => 'Tiền hộp tuần',
                'expense_cat'  => null,
            ],
            [
                'date'         => '2026-03-28',
                'time'         => '18:00:00',
                'topic'        => 'Ôn Tập Quý I — Cùng Nhìn Lại Hành Trình',
                'scripture'    => 'Phi-líp 3:13-14',
                'memory_verse' => '"Tôi quên những sự ở đằng sau mà duỗi ra tới những sự ở đằng trước" - Phi 3:13',
                'preacher'     => 'CS. Nguyễn Văn H',
                'attendance'   => 25,
                'income'       => 1050000,
                'expense'      => 500000,
                'income_cat'   => 'Tiền hộp tuần',
                'expense_cat'  => 'Chi mua sắm',
            ],
        ];

        foreach ($deptMeetingData as $m) {
            $meetingId = DB::table('meetings')->insertGetId([
                'type'          => 'department',
                'department_id' => $deptId,
                'date'          => $m['date'],
                'time'          => $m['time'],
                'topic'         => $m['topic'],
                'scripture'     => $m['scripture'],
                'memory_verse'  => $m['memory_verse'],
                'preacher'      => $m['preacher'],
                'created_at'    => now(),
                'updated_at'    => now(),
            ]);

            // Attendance summary
            DB::table('meeting_attendance_summaries')->insertOrIgnore([
                'meeting_id'    => $meetingId,
                'department_id' => $deptId,
                'manual_count'  => $m['attendance'],
                'notes'         => null,
                'created_at'    => now(),
                'updated_at'    => now(),
            ]);

            // Finance: income (tiền dâng)
            if ($m['income'] > 0) {
                DB::table('meeting_finances')->insert([
                    'meeting_id' => $meetingId,
                    'amount'     => $m['income'],
                    'type'       => 'thu',
                    'category'   => $m['income_cat'],
                    'status'     => 'approved',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // Finance: expense
            if ($m['expense'] > 0 && $m['expense_cat']) {
                DB::table('meeting_finances')->insert([
                    'meeting_id' => $meetingId,
                    'amount'     => $m['expense'],
                    'type'       => 'chi',
                    'category'   => $m['expense_cat'],
                    'status'     => 'approved',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            $this->command->line("  🏠 Ban [{$m['date']}] {$m['topic']} - HD: {$m['attendance']} - Thu: " . number_format($m['income'], 0, ',', '.'));
        }

        // ════════════════════════════════════════════════════════
        // THĂM VIẾNG tháng 3
        // ════════════════════════════════════════════════════════
        // Lấy một vài member ID để thăm viếng
        $memberIds = DB::table('members')->limit(4)->pluck('id');

        if ($memberIds->count() > 0) {
            $visitData = [
                ['date' => '2026-03-05', 'reason' => 'ốm đau',         'status' => 'completed', 'member_idx' => 0],
                ['date' => '2026-03-12', 'reason' => 'khích lệ',        'status' => 'completed', 'member_idx' => 1],
                ['date' => '2026-03-19', 'reason' => 'khích lệ',        'status' => 'completed', 'member_idx' => 2 % $memberIds->count()],
                ['date' => '2026-03-26', 'reason' => 'khác',            'status' => 'planned',   'member_idx' => 3 % $memberIds->count()],
            ];

            foreach ($visitData as $v) {
                $mid = $memberIds[$v['member_idx']];
                // Check if visitation table has expected columns
                $exists = DB::table('visitations')
                    ->where('department_id', $deptId)
                    ->where('member_id', $mid)
                    ->where('visit_date', $v['date'])
                    ->exists();

                if (!$exists) {
                    DB::table('visitations')->insert([
                        'visitation_type' => 'department',
                        'department_id'   => $deptId,
                        'member_id'       => $mid,
                        'visit_date'      => $v['date'],
                        'reason'          => $v['reason'],
                        'content'         => 'Thăm viếng và cầu nguyện.',
                        'status'          => $v['status'],
                        'priority'        => 'normal',
                        'created_at'      => now(),
                        'updated_at'      => now(),
                    ]);
                    $this->command->line("  🏠 Thăm viếng [{$v['date']}] lý do: {$v['reason']} - {$v['status']}");
                }
            }
        }

        $this->command->newLine();
        $this->command->info('🎉 Hoàn tất! Dữ liệu mẫu Ban Thanh Tráng tháng 3/2026:');
        $this->command->table(
            ['Loại', 'Số lượng'],
            [
                ['Buổi nhóm Hội Thánh', count($churchMeetings)],
                ['Buổi nhóm Ban Ngành', count($deptMeetingData)],
                ['Thăm viếng', 4],
            ]
        );
    }
}
