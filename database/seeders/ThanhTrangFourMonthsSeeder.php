<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Dữ liệu mẫu 4 tháng cho Ban Thanh Tráng
 * Tháng 12/2025 · 01/2026 · 02/2026 · 03/2026
 *
 * Mỗi tháng: 4 buổi Hội Thánh (Chủ Nhật) + 4 buổi Ban Ngành (Thứ 7)
 *
 * Chạy: php artisan db:seed --class=ThanhTrangFourMonthsSeeder
 */
class ThanhTrangFourMonthsSeeder extends Seeder
{
    public function run(): void
    {
        // ── Tìm Ban Thanh Tráng ──────────────────────────────────
        $dept = DB::table('departments')
            ->where('block', 'activities')
            ->where(function ($q) {
                $q->where('name', 'like', '%Thanh Tráng%')
                  ->orWhere('name', 'like', '%Thanh Trang%');
            })
            ->first();

        if (!$dept) {
            $this->command->error('❌ Không tìm thấy Ban Thanh Tráng!');
            DB::table('departments')->where('block', 'activities')->get(['id','name'])->each(function ($d) {
                $this->command->line("  [{$d->id}] {$d->name}");
            });
            return;
        }
        $deptId = $dept->id;
        $this->command->info("✅ Ban: [{$deptId}] {$dept->name}");

        // ── Dọn dữ liệu test cũ của ban này ─────────────────────
        $this->command->warn('🗑  Xóa dữ liệu cũ của Ban Thanh Tráng trong 4 tháng...');

        $testDates = ['2025-12', '2026-01', '2026-02', '2026-03'];
        foreach ($testDates as $ym) {
            // Meetings của ban ngành
            $deptMeetingIds = DB::table('meetings')
                ->where('type', 'department')
                ->where('department_id', $deptId)
                ->where('date', 'like', "$ym%")
                ->pluck('id');
            if ($deptMeetingIds->count()) {
                DB::table('meeting_attendance_summaries')->whereIn('meeting_id', $deptMeetingIds)->delete();
                DB::table('meeting_finances')->whereIn('meeting_id', $deptMeetingIds)->delete();
                DB::table('meetings')->whereIn('id', $deptMeetingIds)->delete();
            }

            // Church meetings mà ban này có attendance summary trong khoảng thời gian này
            $churchMeetingIds = DB::table('meetings')
                ->where('type', 'church')
                ->where('date', 'like', "$ym%")
                ->whereNull('department_id')
                ->pluck('id');
            if ($churchMeetingIds->count()) {
                DB::table('meeting_attendance_summaries')
                    ->whereIn('meeting_id', $churchMeetingIds)
                    ->where('department_id', $deptId)
                    ->delete();
                // Chỉ xóa meeting nếu không còn attendance từ ban khác
                foreach ($churchMeetingIds as $mid) {
                    $hasOther = DB::table('meeting_attendance_summaries')
                        ->where('meeting_id', $mid)->exists();
                    if (!$hasOther) {
                        DB::table('meetings')->where('id', $mid)->delete();
                    }
                }
            }

            // Visitations
            DB::table('visitations')
                ->where('department_id', $deptId)
                ->where('visit_date', 'like', "$ym%")
                ->delete();
        }
        $this->command->info('✅ Xóa xong. Bắt đầu thêm mới...');

        // ════════════════════════════════════════════════════════
        // DỮ LIỆU 4 THÁNG
        // ════════════════════════════════════════════════════════
        $months = [
            // ── Tháng 12/2025 ────────────────────────────────────
            [
                'label'  => 'Tháng 12/2025',
                'church' => [
                    // 4 Chủ Nhật: 7, 14, 21, 28/12
                    ['date'=>'2025-12-07','topic'=>'Sự Giáng Sinh Của Đấng Christ','scripture'=>'Lu-ca 2:1-20','memory_verse'=>'"Vinh hiện cho Đức Chúa Trời ở trên trời cao" - Lc 2:14','preacher'=>'MS. Nguyễn Văn A','att'=>30],
                    ['date'=>'2025-12-14','topic'=>'Sáng Láng Như Ánh Sao','scripture'=>'Ma-thi-ơ 2:1-12','memory_verse'=>'"Vì một con trẻ đã sanh cho chúng ta" - Ês 9:6','preacher'=>'MS. Trần Văn B','att'=>34],
                    ['date'=>'2025-12-21','topic'=>'Hy Vọng Trong Đấng Christ','scripture'=>'Rô-ma 15:13','memory_verse'=>'"Đức Chúa Trời của sự trông cậy" - Rm 15:13','preacher'=>'MS. Lê Văn C','att'=>38],
                    ['date'=>'2025-12-28','topic'=>'Năm Mới — Khởi Đầu Ân Điển','scripture'=>'Thi-thiên 121:1-8','memory_verse'=>'"Chúa gìn giữ ngươi khỏi mọi điều tai hại" - Thi 121:7','preacher'=>'MS. Nguyễn Văn A','att'=>42],
                ],
                'dept' => [
                    // 4 Thứ Bảy: 6, 13, 20, 27/12
                    ['date'=>'2025-12-06','topic'=>'Chuẩn Bị Tâm Lòng Đón Giáng Sinh','scripture'=>'Ê-sai 9:6','memory_verse'=>'"Vì một con trẻ đã sanh cho chúng ta" - Ês 9:6','preacher'=>'CS. Nguyễn Thị E','att'=>16,'income'=>700000,'expense'=>150000],
                    ['date'=>'2025-12-13','topic'=>'Ánh Sáng Trong Bóng Tối','scripture'=>'Giăng 1:1-14','memory_verse'=>'"Ngôi Lời đã trở nên xác thịt" - Gi 1:14','preacher'=>'CS. Trần Văn F','att'=>19,'income'=>850000,'expense'=>200000],
                    ['date'=>'2025-12-20','topic'=>'Giáng Sinh Yêu Thương','scripture'=>'Giăng 3:16','memory_verse'=>'"Vì Đức Chúa Trời yêu thương thế gian" - Gi 3:16','preacher'=>'CS. Lê Thị G','att'=>22,'income'=>1100000,'expense'=>300000],
                    ['date'=>'2025-12-27','topic'=>'Tổng Kết Cuối Năm — Tạ Ơn Chúa','scripture'=>'Thi-thiên 100:1-5','memory_verse'=>'"Hãy vào các cửa Chúa với sự cảm tạ" - Thi 100:4','preacher'=>'CS. Nguyễn Văn H','att'=>20,'income'=>950000,'expense'=>0],
                ],
                'visits' => [
                    ['date'=>'2025-12-03','reason'=>'ốm đau','status'=>'completed'],
                    ['date'=>'2025-12-17','reason'=>'khích lệ','status'=>'completed'],
                    ['date'=>'2025-12-24','reason'=>'mới tin Chúa','status'=>'completed'],
                ],
            ],

            // ── Tháng 01/2026 ─────────────────────────────────────
            [
                'label'  => 'Tháng 01/2026',
                'church' => [
                    // 4 Chủ Nhật: 4, 11, 18, 25/01
                    ['date'=>'2026-01-04','topic'=>'Năm Mới — Bước Đi Với Chúa','scripture'=>'Giô-suê 1:8-9','memory_verse'=>'"Chỉ hãy vững lòng bền chí" - Gs 1:9','preacher'=>'MS. Nguyễn Văn A','att'=>28],
                    ['date'=>'2026-01-11','topic'=>'Làm Mới Lại Trong Thánh Linh','scripture'=>'Ê-phê-sô 4:22-24','memory_verse'=>'"Hãy mặc lấy người mới" - Êph 4:24','preacher'=>'MS. Phạm Văn D','att'=>32],
                    ['date'=>'2026-01-18','topic'=>'Phục Vụ Bằng Ân Tứ','scripture'=>'1 Cô-rinh-tô 12:4-11','memory_verse'=>'"Mỗi người có ân tứ riêng của mình" - 1Cr 7:7','preacher'=>'MS. Trần Văn B','att'=>35],
                    ['date'=>'2026-01-25','topic'=>'Lời Chúa Là Nền Tảng','scripture'=>'Ma-thi-ơ 7:24-27','memory_verse'=>'"Ai nghe lời Ta... như người khôn" - Mt 7:24','preacher'=>'MS. Lê Văn C','att'=>30],
                ],
                'dept' => [
                    // 4 Thứ Bảy: 3, 10, 17, 24/01
                    ['date'=>'2026-01-03','topic'=>'Cầu Nguyện Đầu Năm','scripture'=>'Phi-líp 4:6-7','memory_verse'=>'"Chớ lo phiền chi hết" - Phi 4:6','preacher'=>'CS. Nguyễn Thị E','att'=>14,'income'=>620000,'expense'=>0],
                    ['date'=>'2026-01-10','topic'=>'Mục Tiêu Thuộc Linh Năm 2026','scripture'=>'Phi-líp 3:13-14','memory_verse'=>'"Tôi quên những sự ở đằng sau" - Phi 3:13','preacher'=>'CS. Trần Văn F','att'=>18,'income'=>780000,'expense'=>250000],
                    ['date'=>'2026-01-17','topic'=>'Sống Thánh Trong Thế Giới','scripture'=>'1 Phi-e-rơ 1:15-16','memory_verse'=>'"Hãy nên thánh vì Ta là thánh" - 1Phi 1:16','preacher'=>'CS. Nguyễn Văn H','att'=>21,'income'=>900000,'expense'=>180000],
                    ['date'=>'2026-01-24','topic'=>'Yêu Thương Nhau','scripture'=>'Giăng 13:34-35','memory_verse'=>'"Hãy yêu nhau như Ta đã yêu các ngươi" - Gi 13:34','preacher'=>'CS. Lê Thị G','att'=>19,'income'=>850000,'expense'=>200000],
                ],
                'visits' => [
                    ['date'=>'2026-01-07','reason'=>'khích lệ','status'=>'completed'],
                    ['date'=>'2026-01-21','reason'=>'ốm đau','status'=>'completed'],
                    ['date'=>'2026-01-28','reason'=>'khác','status'=>'planned'],
                ],
            ],

            // ── Tháng 02/2026 ─────────────────────────────────────
            [
                'label'  => 'Tháng 02/2026',
                'church' => [
                    // 4 Chủ Nhật: 1, 8, 15, 22/02
                    ['date'=>'2026-02-01','topic'=>'Đức Tin Di Chuyển Núi','scripture'=>'Ma-thi-ơ 17:20','memory_verse'=>'"Nếu các ngươi có đức tin" - Mt 17:20','preacher'=>'MS. Nguyễn Văn A','att'=>26],
                    ['date'=>'2026-02-08','topic'=>'Ân Điển Đủ Dùng','scripture'=>'2 Cô-rinh-tô 12:9','memory_verse'=>'"Ân điển Ta đủ cho ngươi" - 2Cr 12:9','preacher'=>'MS. Trần Văn B','att'=>31],
                    ['date'=>'2026-02-15','topic'=>'Thờ Phượng Trong Thần Linh','scripture'=>'Giăng 4:23-24','memory_verse'=>'"Thờ phượng trong tâm thần và lẽ thật" - Gi 4:24','preacher'=>'MS. Phạm Văn D','att'=>36],
                    ['date'=>'2026-02-22','topic'=>'Cộng Đồng Đức Tin','scripture'=>'Công Vụ 2:42-47','memory_verse'=>'"Họ bền lòng giữ lời dạy của các sứ đồ" - CV 2:42','preacher'=>'MS. Lê Văn C','att'=>33],
                ],
                'dept' => [
                    // 4 Thứ Bảy: 7, 14, 21, 28/02
                    ['date'=>'2026-02-07','topic'=>'Bài Học: Đa-vít Và Gô-li-át','scripture'=>'1 Sa-mu-ên 17:32-51','memory_verse'=>'"Trận chiến này thuộc về Đức Giê-hô-va" - 1Sm 17:47','preacher'=>'CS. Nguyễn Thị E','att'=>17,'income'=>750000,'expense'=>0],
                    ['date'=>'2026-02-14','topic'=>'Tình Yêu Trong Sáng','scripture'=>'1 Cô-rinh-tô 13:4-8','memory_verse'=>'"Tình yêu thương hay nhịn nhục" - 1Cr 13:4','preacher'=>'CS. Trần Văn F','att'=>23,'income'=>1050000,'expense'=>400000],
                    ['date'=>'2026-02-21','topic'=>'Sống Đúng Với Ơn Gọi','scripture'=>'Ê-phê-sô 4:1-6','memory_verse'=>'"Ăn ở cách xứng đáng với chức phận" - Êph 4:1','preacher'=>'CS. Lê Thị G','att'=>20,'income'=>880000,'expense'=>150000],
                    ['date'=>'2026-02-28','topic'=>'Kiên Trì Trong Thử Thách','scripture'=>'Gia-cơ 1:2-4','memory_verse'=>'"Sự thử thách đức tin anh em sanh ra lòng nhịn nhục" - Gc 1:3','preacher'=>'CS. Nguyễn Văn H','att'=>18,'income'=>800000,'expense'=>0],
                ],
                'visits' => [
                    ['date'=>'2026-02-04','reason'=>'ốm đau','status'=>'completed'],
                    ['date'=>'2026-02-11','reason'=>'mới tin Chúa','status'=>'completed'],
                    ['date'=>'2026-02-18','reason'=>'khích lệ','status'=>'completed'],
                    ['date'=>'2026-02-25','reason'=>'khác','status'=>'planned'],
                ],
            ],

            // ── Tháng 03/2026 ─────────────────────────────────────
            [
                'label'  => 'Tháng 03/2026',
                'church' => [
                    // 4 Chủ Nhật: 1, 8, 15, 22/03
                    ['date'=>'2026-03-01','topic'=>'Sống Theo Lời Chúa','scripture'=>'Thi-thiên 119:9-16','memory_verse'=>'"Lời Chúa là đèn cho chân tôi" - Thi 119:105','preacher'=>'MS. Nguyễn Văn A','att'=>32],
                    ['date'=>'2026-03-08','topic'=>'Tình Yêu Thương Của Đức Chúa Trời','scripture'=>'Giăng 3:16-21','memory_verse'=>'"Vì Đức Chúa Trời yêu thương thế gian" - Gi 3:16','preacher'=>'MS. Trần Văn B','att'=>35],
                    ['date'=>'2026-03-15','topic'=>'Đức Tin Và Việc Làm','scripture'=>'Gia-cơ 2:14-26','memory_verse'=>'"Đức tin không có việc làm là đức tin chết" - Gc 2:17','preacher'=>'MS. Lê Văn C','att'=>28],
                    ['date'=>'2026-03-22','topic'=>'Cầu Nguyện Trong Thánh Linh','scripture'=>'Ê-phê-sô 6:18-20','memory_verse'=>'"Hãy cầu nguyện không thôi" - 1Tê 5:17','preacher'=>'MS. Nguyễn Văn A','att'=>40],
                ],
                'dept' => [
                    // 4 Thứ Bảy: 7, 14, 21, 28/03
                    ['date'=>'2026-03-07','topic'=>'Bài Học: Giô-sép Và Lòng Kiên Nhẫn','scripture'=>'Sáng Thế Ký 39:1-23','memory_verse'=>'"Đức Chúa Trời định điều lành" - St 50:20','preacher'=>'CS. Nguyễn Thị E','att'=>18,'income'=>850000,'expense'=>200000],
                    ['date'=>'2026-03-14','topic'=>'Thanh Niên Sống Thánh Khiết','scripture'=>'1 Ti-mô-thê 4:12','memory_verse'=>'"Chớ ai khinh con vì trẻ tuổi" - 1Ti 4:12','preacher'=>'CS. Trần Văn F','att'=>22,'income'=>1200000,'expense'=>350000],
                    ['date'=>'2026-03-21','topic'=>'Dâng Hiến Và Phước Lành','scripture'=>'Ma-la-chi 3:10','memory_verse'=>'"Hãy đem hết thảy phần mười vào kho" - Mal 3:10','preacher'=>'CS. Lê Thị G','att'=>20,'income'=>950000,'expense'=>0],
                    ['date'=>'2026-03-28','topic'=>'Ôn Tập Quý I — Cùng Nhìn Lại Hành Trình','scripture'=>'Phi-líp 3:13-14','memory_verse'=>'"Tôi quên những sự ở đằng sau" - Phi 3:13','preacher'=>'CS. Nguyễn Văn H','att'=>25,'income'=>1050000,'expense'=>500000],
                ],
                'visits' => [
                    ['date'=>'2026-03-05','reason'=>'ốm đau','status'=>'completed'],
                    ['date'=>'2026-03-12','reason'=>'khích lệ','status'=>'completed'],
                    ['date'=>'2026-03-19','reason'=>'khích lệ','status'=>'completed'],
                    ['date'=>'2026-03-26','reason'=>'khác','status'=>'planned'],
                ],
            ],
        ];

        // Lấy member IDs cho attendance và visitation
        $memberIds = DB::table('members')->limit(5)->pluck('id');

        foreach ($months as $monthData) {
            $this->command->newLine();
            $this->command->info("📅 {$monthData['label']}");

            // ─ CHURCH MEETINGS ───────────────────────────────────
            foreach ($monthData['church'] as $m) {
                $meetingId = DB::table('meetings')->insertGetId([
                    'type'          => 'church',
                    'department_id' => null,
                    'date'          => $m['date'],
                    'time'          => '09:00:00',
                    'topic'         => $m['topic'],
                    'scripture'     => $m['scripture'],
                    'memory_verse'  => $m['memory_verse'],
                    'preacher'      => $m['preacher'],
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ]);
                DB::table('meeting_attendance_summaries')->insertOrIgnore([
                    'meeting_id'    => $meetingId,
                    'department_id' => $deptId,
                    'manual_count'  => $m['att'],
                    'notes'         => null,
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ]);
                $this->command->line("  🏛 [{$m['date']}] {$m['topic']} · HD: {$m['att']}");
            }

            // ─ DEPT MEETINGS ─────────────────────────────────────
            foreach ($monthData['dept'] as $m) {
                $meetingId = DB::table('meetings')->insertGetId([
                    'type'          => 'department',
                    'department_id' => $deptId,
                    'date'          => $m['date'],
                    'time'          => '18:00:00',
                    'topic'         => $m['topic'],
                    'scripture'     => $m['scripture'],
                    'memory_verse'  => $m['memory_verse'],
                    'preacher'      => $m['preacher'],
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ]);
                DB::table('meeting_attendance_summaries')->insertOrIgnore([
                    'meeting_id'    => $meetingId,
                    'department_id' => $deptId,
                    'manual_count'  => $m['att'],
                    'notes'         => null,
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ]);
                if ($m['income'] > 0) {
                    DB::table('meeting_finances')->insert([
                        'meeting_id' => $meetingId, 'amount' => $m['income'],
                        'type' => 'thu', 'category' => 'Tiền hộp tuần',
                        'status' => 'approved', 'created_at' => now(), 'updated_at' => now(),
                    ]);
                }
                if ($m['expense'] > 0) {
                    DB::table('meeting_finances')->insert([
                        'meeting_id' => $meetingId, 'amount' => $m['expense'],
                        'type' => 'chi', 'category' => 'Chi hoạt động',
                        'status' => 'approved', 'created_at' => now(), 'updated_at' => now(),
                    ]);
                }
                $this->command->line("  🏠 [{$m['date']}] {$m['topic']} · HD: {$m['att']} · Thu: " . number_format($m['income'], 0, ',', '.'));
            }

            // ─ VISITATIONS ───────────────────────────────────────
            if ($memberIds->count() > 0) {
                foreach ($monthData['visits'] as $i => $v) {
                    $mid = $memberIds[$i % $memberIds->count()];
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
                        $this->command->line("  🤝 [{$v['date']}] {$v['reason']} · {$v['status']}");
                    }
                }
            }
        }

        $this->command->newLine();
        $this->command->info('🎉 Hoàn tất! Tổng kết 4 tháng — Ban Thanh Tráng:');
        $this->command->table(
            ['Tháng', 'HT', 'Ban', 'Thăm viếng'],
            array_map(fn($m) => [
                $m['label'],
                count($m['church']) . ' buổi',
                count($m['dept'])   . ' buổi',
                count($m['visits']) . ' lần',
            ], $months)
        );
    }
}
