<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Member;
use App\Models\Team;
use App\Models\OrgMembership;
use App\Models\OrgRole;
use App\Models\Department;

class BanThanhTrangSeeder extends Seeder
{
    /**
     * Xóa sạch dữ liệu hoạt động và seed lại Members + Teams cho Ban Thanh Tráng
     */
    public function run(): void
    {
        $this->command->info('🗑️  Bắt đầu xóa dữ liệu cũ...');

        // ── 1. XÓA DỮ LIỆU HOẠT ĐỘNG ──────────────────────────────────────
        // Tắt foreign key check (tương thích SQLite + MySQL)
        $driver = DB::getDriverName();
        if ($driver === 'sqlite') {
            DB::unprepared('PRAGMA foreign_keys = OFF');
        } else {
            DB::statement('SET FOREIGN_KEY_CHECKS=0');
        }

        // Điểm danh
        DB::table('meeting_attendances')->delete();
        DB::table('meeting_attendance_summaries')->delete();

        // Buổi nhóm & báo cáo
        DB::table('meeting_personnels')->delete();
        DB::table('meeting_finances')->delete();
        DB::table('meeting_reports')->delete();
        DB::table('meetings')->delete();

        // Báo cáo ban ngành
        DB::table('department_reports')->delete();
        DB::table('department_transactions')->delete();
        DB::table('department_meetings')->delete();

        // Thăm viếng
        DB::table('visitation_visitors')->delete();
        DB::table('visitations')->delete();

        // Tài chính (member-level)
        DB::table('member_contributions')->delete();

        // Org memberships (ban ngành, tổ)
        DB::table('org_memberships')->delete();

        // Teams (tổ) — chỉ xóa các tổ chứ KHÔNG xóa ban ngành
        DB::table('teams')->delete();

        // Members — xóa sạch (kể cả soft deleted)
        DB::table('member_sensitives')->delete();
        DB::table('members')->delete();

        // Bật lại foreign key check
        if ($driver === 'sqlite') {
            DB::unprepared('PRAGMA foreign_keys = ON');
        } else {
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
        }

        $this->command->info('✅ Đã xóa sạch dữ liệu cũ.');

        // ── 2. TÌM BAN THANH TRÁNG ─────────────────────────────────────────
        $dept = Department::where('name', 'like', '%Thanh Tráng%')->first();
        if (!$dept) {
            $this->command->error('❌ Không tìm thấy ban Thanh Tráng!');
            return;
        }
        $this->command->info("📌 Ban: {$dept->name} (ID={$dept->id})");

        // ── 3. TẠO 2 TỔ ────────────────────────────────────────────────────
        $teamPhaolo = Team::create([
            'department_id' => $dept->id,
            'name'          => 'Phao-lô',
            'code'          => 'BTT-PL',
            'description'   => 'Tổ Phao-lô - Ban Thanh Tráng',
            'is_active'     => true,
        ]);

        $teamDanien = Team::create([
            'department_id' => $dept->id,
            'name'          => 'Đa-ni-ên',
            'code'          => 'BTT-DN',
            'description'   => 'Tổ Đa-ni-ên - Ban Thanh Tráng',
            'is_active'     => true,
        ]);

        $this->command->info("✅ Đã tạo: Tổ Phao-lô (ID={$teamPhaolo->id}), Tổ Đa-ni-ên (ID={$teamDanien->id})");

        // ── 4. ORG ROLES ────────────────────────────────────────────────────
        // Lấy role 'bv' (Ban viên / thành viên thường)
        $roleBanVien = OrgRole::where('code', 'bv')->first();
        if (!$roleBanVien) {
            // Tạo nếu chưa có
            $roleBanVien = OrgRole::create(['code' => 'bv', 'name' => 'Ban viên']);
        }

        // ── 5. DỮ LIỆU MEMBERS ─────────────────────────────────────────────
        $data = [
            // ── TỔ ĐA-NI-ÊN ─────────────────────────────────────────────
            ['name' => 'Hoàng Thị Giang',         'team' => 'Đa-ni-ên'],
            ['name' => 'Châu Thị Mai',             'team' => 'Đa-ni-ên'],
            ['name' => 'Lê Khắc Đại Lộc',          'team' => 'Đa-ni-ên'],
            ['name' => 'Thái Nhựt Bình',           'team' => 'Đa-ni-ên'],
            ['name' => 'Đặng Xuân Hương',          'team' => 'Đa-ni-ên'],
            ['name' => 'Nguyễn Thị Hạ Thương',     'team' => 'Đa-ni-ên'],
            ['name' => "Y Eban Duel",              'team' => 'Đa-ni-ên'],
            ['name' => 'Rmah Toàn',               'team' => 'Đa-ni-ên'],
            ['name' => 'Trương Nhật Thuy',         'team' => 'Đa-ni-ên'],
            ['name' => 'Nguyễn Thị Trà My',        'team' => 'Đa-ni-ên'],
            ['name' => 'Nguyễn Thị Yến Thanh',     'team' => 'Đa-ni-ên'],
            ['name' => 'Nguyễn Thị Hồng Việt',     'team' => 'Đa-ni-ên'],
            ['name' => 'Nguyễn Thị Nhung',         'team' => 'Đa-ni-ên'],
            ['name' => 'Nguyễn Thế Hải',           'team' => 'Đa-ni-ên'],
            ['name' => 'Trương Mỹ Tú',             'team' => 'Đa-ni-ên'],
            ['name' => 'Nguyễn Văn Hưng',          'team' => 'Đa-ni-ên'],
            ['name' => 'Nguyễn Trọng Huân',        'team' => 'Đa-ni-ên'],

            // ── TỔ PHAO-LÔ ──────────────────────────────────────────────
            ["name" => "Ksor H' Miram",            'team' => 'Phao-lô'],
            ['name' => 'Tăng Khắc Thiên Nhân',     'team' => 'Phao-lô'],
            ['name' => 'Lê Thị Hoàng Yến',         'team' => 'Phao-lô'],
            ['name' => 'Nguyễn Thị Ngọc Lâm',      'team' => 'Phao-lô'],
            ['name' => 'Nguyễn Ngọc Hà Thi',       'team' => 'Phao-lô'],
            ['name' => 'Bùi Thị Thu Hiền',         'team' => 'Phao-lô'],
            ['name' => 'Nguyễn Thị Ngọc Mỹ',       'team' => 'Phao-lô'],
            ['name' => 'Nguyễn Thị Thu Hiền',      'team' => 'Phao-lô'],
            ['name' => 'Phạm Anh Tuấn',            'team' => 'Phao-lô'],
            ['name' => 'Lê Quang Trung Tín',        'team' => 'Phao-lô'],
            ['name' => 'Huỳnh Giang Duy Vũ',       'team' => 'Phao-lô'],
            ['name' => 'Nguyễn Thanh Tuấn',        'team' => 'Phao-lô'],
            ['name' => 'Lù Duy Nguyên',            'team' => 'Phao-lô'],
            ['name' => 'Nguyễn Thị Sen',           'team' => 'Phao-lô'],
            ['name' => 'Nguyễn Thị Mỹ Ngọc',       'team' => 'Phao-lô'],
            ['name' => 'Chung Nguyên',             'team' => 'Phao-lô'],
            ['name' => 'Lê Thị Thu Vân',           'team' => 'Phao-lô'],

            // ── CHƯA CÓ TỔ (Khác) ───────────────────────────────────────
            ['name' => 'Trương Thị Thanh Thảo',    'team' => null],
            ['name' => 'Trần Thị Thanh Thảo',      'team' => null],
            ['name' => 'La Minh Hoàng',            'team' => null],
            ['name' => 'Nguyễn Đặng Thảo Nguyên',  'team' => null],
            ['name' => 'Phạm Kiều Trâm',           'team' => null],
            ['name' => 'Cao Thiên Ngọc',           'team' => null],
            ['name' => 'Nguyễn Ngọc Tuệ',          'team' => null],
            ['name' => 'Lê Kim Huệ',               'team' => null],
            ['name' => 'Trịnh Thế Hân',            'team' => null],
            ['name' => 'Nguyễn Kim Long',           'team' => null],
            ['name' => 'Nguyễn Thị Nhật Thiên',    'team' => null],
            ['name' => 'Vũ Kiều Oanh',             'team' => null],
            ['name' => 'Huỳnh Nhuận Tâm',          'team' => null],
            ['name' => 'Nguyễn Ngọc Tuân',         'team' => null],
            ['name' => 'Tô Bích Trâm',             'team' => null],
            ['name' => 'Huỳnh Nhật Kha My',        'team' => null],
            ['name' => 'Hồ Thị Minh Nhựt',         'team' => null],
            ['name' => 'Hồ Thị Diễm Ngọc',         'team' => null],
            ['name' => 'Nguyễn Nguyên Bá',         'team' => null],
            ['name' => 'Phan Văn Hoàng',           'team' => null],
            ['name' => 'Nguyễn Hoa Thiên Lý',      'team' => null],
            ['name' => 'Lưu Văn Minh',             'team' => null],
            ['name' => 'Huỳnh Tấn Trực',           'team' => null],
            ['name' => 'Quách Thanh Dũng',         'team' => null],
            ['name' => 'Nguyễn Sơn Đông',          'team' => null],
        ];

        // Map tên tổ → team object
        $teamMap = [
            'Phao-lô'  => $teamPhaolo,
            'Đa-ni-ên' => $teamDanien,
        ];

        $this->command->info("👥 Đang tạo " . count($data) . " members...");

        $created = 0;
        foreach ($data as $row) {
            // Tạo member
            $member = Member::create([
                'full_name'  => $row['name'],
                'status'     => 'Chính thức',
                'gender'     => null,
            ]);

            // Gán vào Ban Thanh Tráng
            OrgMembership::create([
                'model_type'  => Department::class,
                'model_id'    => $dept->id,
                'member_id'   => $member->id,
                'org_role_id' => $roleBanVien->id,
                'join_date'   => now(),
            ]);

            // Gán vào tổ (nếu có)
            if (!empty($row['team']) && isset($teamMap[$row['team']])) {
                $team = $teamMap[$row['team']];
                OrgMembership::create([
                    'model_type'  => Team::class,
                    'model_id'    => $team->id,
                    'member_id'   => $member->id,
                    'org_role_id' => $roleBanVien->id,
                    'join_date'   => now(),
                ]);
            }

            $created++;
        }

        $this->command->info("✅ Đã tạo {$created} members cho Ban Thanh Tráng.");
        $this->command->info("   - Tổ Phao-lô:  " . collect($data)->where('team', 'Phao-lô')->count() . " người");
        $this->command->info("   - Tổ Đa-ni-ên: " . collect($data)->where('team', 'Đa-ni-ên')->count() . " người");
        $this->command->info("   - Chưa có tổ:  " . collect($data)->whereNull('team')->count() . " người");
    }
}
