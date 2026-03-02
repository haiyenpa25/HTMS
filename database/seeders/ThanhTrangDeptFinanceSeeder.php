<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Seed DepartmentFund + DepartmentMeeting + DepartmentTransaction
 * cho Ban Thanh Tráng — 4 tháng (12/2025 → 03/2026)
 *
 * Đây là dữ liệu cho Portal Finance (/portal/finance)
 * Chạy: php artisan db:seed --class=ThanhTrangDeptFinanceSeeder
 */
class ThanhTrangDeptFinanceSeeder extends Seeder
{
    public function run(): void
    {
        // ── Tìm Ban Thanh Tráng ──────────────────────────────────
        $dept = DB::table('departments')
            ->where('block', 'activities')
            ->where(function ($q) {
                $q->where('name', 'like', '%Thanh Tráng%')
                  ->orWhere('name', 'like', '%Thanh Trang%');
            })->first();

        if (!$dept) {
            $this->command->error('❌ Không tìm thấy Ban Thanh Tráng!');
            return;
        }
        $deptId = $dept->id;
        $this->command->info("✅ Ban: [{$deptId}] {$dept->name}");

        // ── Dọn dữ liệu cũ ───────────────────────────────────────
        $testMonths = ['2025-12', '2026-01', '2026-02', '2026-03'];
        foreach ($testMonths as $ym) {
            $mIds = DB::table('department_meetings')
                ->where('department_id', $deptId)
                ->where('meeting_date', 'like', "$ym%")
                ->pluck('id');
            if ($mIds->count()) {
                DB::table('department_transactions')->whereIn('department_meeting_id', $mIds)->delete();
                DB::table('department_meetings')->whereIn('id', $mIds)->delete();
            }
        }
        // Xóa standalone transactions
        $fundIds = DB::table('department_funds')->where('department_id', $deptId)->pluck('id');
        if ($fundIds->count()) {
            foreach ($testMonths as $ym) {
                DB::table('department_transactions')
                    ->whereIn('department_fund_id', $fundIds)
                    ->where('transaction_date', 'like', "$ym%")
                    ->delete();
            }
        }
        $this->command->info('✅ Dọn dữ liệu cũ xong.');

        // ── Tạo Quỹ (nếu chưa có) ────────────────────────────────
        $fund = DB::table('department_funds')
            ->where('department_id', $deptId)
            ->where('name', 'Quỹ Thường Xuyên')
            ->first();

        if (!$fund) {
            $fundId = DB::table('department_funds')->insertGetId([
                'department_id' => $deptId,
                'name'          => 'Quỹ Thường Xuyên',
                'description'   => 'Quỹ tiền dâng và chi phí hoạt động Ban Thanh Tráng',
                'created_at'    => now(),
                'updated_at'    => now(),
            ]);
            $this->command->info("💰 Tạo quỹ mới ID: $fundId");
        } else {
            $fundId = $fund->id;
            $this->command->info("💰 Dùng quỹ hiện có ID: $fundId");
        }

        // ════════════════════════════════════════════════════════
        // DỮ LIỆU 4 THÁNG
        // department_meetings có: attendance_morning, attendance_afternoon
        // Dùng sáng=buổi nhóm chính, chiều=sinh hoạt thêm (hoặc 0 nếu ko có)
        // ════════════════════════════════════════════════════════
        $months = [
            [
                'label' => 'Tháng 12/2025',
                'meetings' => [
                    // [date, morning, afternoon, note, income, expense]
                    ['2025-12-06', 16, 0,  'Chuẩn bị Giáng Sinh',      700000,  150000],
                    ['2025-12-13', 19, 8,  'Ánh sáng trong bóng tối',  850000,  200000],
                    ['2025-12-20', 22, 10, 'Giáng Sinh yêu thương',   1100000,  300000],
                    ['2025-12-27', 20, 0,  'Tổng kết cuối năm',         950000,  0],
                ],
            ],
            [
                'label' => 'Tháng 01/2026',
                'meetings' => [
                    ['2026-01-03', 14, 0,  'Cầu nguyện đầu năm',        620000,  0],
                    ['2026-01-10', 18, 7,  'Mục tiêu thuộc linh 2026', 780000,  250000],
                    ['2026-01-17', 21, 9,  'Sống thánh trong thế giới', 900000,  180000],
                    ['2026-01-24', 19, 0,  'Yêu thương nhau',           850000,  200000],
                ],
            ],
            [
                'label' => 'Tháng 02/2026',
                'meetings' => [
                    ['2026-02-07', 17, 0,  'Đa-vít và Gô-li-át',        750000,  0],
                    ['2026-02-14', 23, 11, 'Tình yêu trong sáng',      1050000,  400000],
                    ['2026-02-21', 20, 8,  'Sống đúng với ơn gọi',      880000,  150000],
                    ['2026-02-28', 18, 0,  'Kiên trì trong thử thách',  800000,  0],
                ],
            ],
            [
                'label' => 'Tháng 03/2026',
                'meetings' => [
                    ['2026-03-07', 18, 0,  'Giô-sép và lòng kiên nhẫn', 850000, 200000],
                    ['2026-03-14', 22, 10, 'Thanh niên sống thánh khiết',1200000,350000],
                    ['2026-03-21', 20, 7,  'Dâng hiến và phước lành',   950000,  0],
                    ['2026-03-28', 25, 0,  'Ôn tập Quý I',              1050000, 500000],
                ],
            ],
        ];

        foreach ($months as $monthData) {
            $this->command->newLine();
            $this->command->info("📅 {$monthData['label']}");

            foreach ($monthData['meetings'] as [$date, $morning, $afternoon, $note, $income, $expense]) {
                // Insert department meeting
                $meetingId = DB::table('department_meetings')->insertGetId([
                    'department_id'        => $deptId,
                    'meeting_date'         => $date,
                    'attendance_morning'   => $morning,
                    'attendance_afternoon' => $afternoon,
                    'note'                 => $note,
                    'created_at'           => now(),
                    'updated_at'           => now(),
                ]);

                // Income transaction
                if ($income > 0) {
                    DB::table('department_transactions')->insert([
                        'department_fund_id'    => $fundId,
                        'department_meeting_id' => $meetingId,
                        'type'                  => 'income',
                        'amount'                => $income,
                        'category'              => 'Tiền hộp tuần',
                        'description'           => "Tiền dâng buổi nhóm {$date}",
                        'transaction_date'      => $date,
                        'status'                => 'approved',
                        'created_at'            => now(),
                        'updated_at'            => now(),
                    ]);
                }

                // Expense transaction
                if ($expense > 0) {
                    DB::table('department_transactions')->insert([
                        'department_fund_id'    => $fundId,
                        'department_meeting_id' => $meetingId,
                        'type'                  => 'expense',
                        'amount'                => $expense,
                        'category'              => 'Chi hoạt động',
                        'description'           => "Chi phí buổi nhóm {$date}",
                        'transaction_date'      => $date,
                        'status'                => 'approved',
                        'created_at'            => now(),
                        'updated_at'            => now(),
                    ]);
                }

                $totalAtt = max($morning, $afternoon);
                $this->command->line(
                    "  ✅ [{$date}] HD Sáng:{$morning}/Chiều:{$afternoon} · Thu:" . number_format($income, 0, ',', '.') .
                    ($expense > 0 ? ' · Chi:' . number_format($expense, 0, ',', '.') : '')
                );
            }
        }

        $this->command->newLine();
        $this->command->info('🎉 Xong! DepartmentFund + DepartmentMeeting + DepartmentTransaction cho Ban Thanh Tráng');
        $this->command->table(
            ['Tháng', 'Số buổi', 'Tổng Thu', 'Tổng Chi'],
            array_map(fn($m) => [
                $m['label'],
                count($m['meetings']),
                number_format(array_sum(array_column($m['meetings'], 4)), 0, ',', '.') . ' đ',
                number_format(array_sum(array_column($m['meetings'], 5)), 0, ',', '.') . ' đ',
            ], $months)
        );
    }
}
