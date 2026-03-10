<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Department;
use App\Models\DepartmentRole;
use App\Models\RosterTemplate;
use App\Models\RosterTemplateRole;

class DutyRosterSeeder extends Seeder
{
    public function run(): void
    {
        // Danh sách roles đầy đủ theo section và department
        // Cấu trúc: ['section', 'name', 'max_count', 'sort_order', 'dept_keyword']
        // Map: [section, name, max_count, sort_order, dept_keyword]
        // Keywords matched against actual DB departments:
        // Ban Chấp sự, Ban Trị sự, Ban Trung Lão, Ban Trung Niên, Ban Thanh Tráng,
        // Ban Thanh Niên, Ban Thiếu Nhi, Ban Cơ Đốc Giáo Dục, Ban Truyền Giảng,
        // Ban Chứng Đạo – Chăm Sóc TTH, Ban Kỹ Thuật, Ban Nhạc Cụ, Ban Kết Nối,
        // Ban Khánh Tiết, Ban Hậu Cần, Ban Cầu Nguyện, Ban Tiếp Tân – Trật Tự,
        // Ban Tương Trợ, Ban Thăm Viếng, Ban Hát Thờ Phượng
        $rolesConfig = [
            // ── I. Chương Trình Lễ (→ Ban Chấp sự) ──
            ['Chương Trình Lễ', 'Diễn giả',                  1, 1, 'Chấp sự'],
            ['Chương Trình Lễ', 'Hướng dẫn chương trình',  1, 2, 'Chấp sự'],
            ['Chương Trình Lễ', 'Nhận tiền dâng',           2, 3, 'Chấp sự'],
            ['Chương Trình Lễ', 'Hỗ trợ Tiệc Thánh',       2, 4, 'Chấp sự'],
            ['Chương Trình Lễ', 'Thông báo / Thư Ký',      1, 5, 'Chấp sự'],

            // ── Ban Hát Thờ Phượng (→ Ban Hát Thờ Phượng) ──
            ['Ban Hát',       'Hướng dẫn hát',      1, 1, 'Hát Thờ Phượng'],
            ['Ban Hát',       'Tiết mục hát 1',     1, 2, 'Hát Thờ Phượng'],
            ['Ban Hát',       'Tiết mục hát 2',     1, 3, 'Hát Thờ Phượng'],

            // ── Ban Nhạc Cụ (→ Ban Nhạc Cụ) ──
            ['Ban Nhạc',     'Piano / Keyboard', 1, 1, 'Nhạc Cụ'],
            ['Ban Nhạc',     'Guitar',           1, 2, 'Nhạc Cụ'],
            ['Ban Nhạc',     'Bass',             1, 3, 'Nhạc Cụ'],
            ['Ban Nhạc',     'Trống',            1, 4, 'Nhạc Cụ'],

            // ── Ban Kỹ Thuật (→ Ban Kỹ Thuật) ──
            ['Ban Kỹ Thuật', 'Âm thanh',      1, 1, 'Kỹ Thuật'],
            ['Ban Kỹ Thuật', 'Mix / Monitor', 1, 2, 'Kỹ Thuật'],
            ['Ban Kỹ Thuật', 'Trình chiếu',   1, 3, 'Kỹ Thuật'],
            ['Ban Kỹ Thuật', 'Livestream',    1, 4, 'Kỹ Thuật'],
            ['Ban Kỹ Thuật', 'Quay phim',     1, 5, 'Kỹ Thuật'],
            ['Ban Kỹ Thuật', 'Chụp ảnh',      1, 6, 'Kỹ Thuật'],

            // ── Ban Tiếp Tân – Trật Tự (→ Ban Tiếp Tân – Trật Tự) ──
            ['Ban Tiếp Tân',  'Tiếp đón cổng',               3, 1, 'Tiếp Tân'],
            ['Ban Tiếp Tân',  'Hướng dẫn chỗ ngồi',          3, 2, 'Tiếp Tân'],
            ['Ban Tiếp Tân',  'Phát tài liệu',               2, 3, 'Tiếp Tân'],
            ['Ban Trật Tự',  'Giữ xe',                     4, 4, 'Tiếp Tân'],
            ['Ban Trật Tự',  'Trật tự trong hội trường',  3, 5, 'Tiếp Tân'],

            // ── Ban Khánh Tiết (→ Ban Khánh Tiết) ──
            ['Ban Khánh Tiết', 'Trang trí sân khấu',  2, 1, 'Khánh Tiết'],
            ['Ban Khánh Tiết', 'Chuẩn bị Tiệc Thánh', 2, 2, 'Khánh Tiết'],
        ];

        foreach ($rolesConfig as [$section, $name, $maxCount, $sortOrder, $deptKeyword]) {
            $dept = Department::where('name', 'LIKE', "%{$deptKeyword}%")->first();
            if (!$dept) continue;

            DepartmentRole::updateOrCreate(
                ['department_id' => $dept->id, 'name' => $name],
                [
                    'section'    => $section,
                    'sort_order' => $sortOrder,
                    'max_count'  => $maxCount,
                    'is_active'  => true,
                ]
            );
        }

        // ── Template: Lễ Thờ Phượng Chúa Nhật ──
        $tplHT = RosterTemplate::firstOrCreate(['name' => 'Lễ Thờ Phượng Chúa Nhật']);
        $htRoleNames = ['Diễn giả', 'Hướng dẫn chương trình', 'Nhận tiền dâng', 'Hỗ trợ Tiệc Thánh', 'Thông báo / Thư Ký',
                        'Hướng dẫn hát', 'Tiết mục hát 1', 'Âm thanh', 'Mix / Monitor', 'Trình chiếu', 'Livestream',
                        'Tiếp đón cổng', 'Giữ xe'];
        $htRoles = DepartmentRole::whereIn('name', $htRoleNames)->get();
        foreach ($htRoles as $r) {
            RosterTemplateRole::firstOrCreate(['roster_template_id' => $tplHT->id, 'department_role_id' => $r->id]);
        }

        // ── Template: Nhóm Ban Ngành ──
        $tplBan = RosterTemplate::firstOrCreate(['name' => 'Nhóm Ban Ngành']);
        $banRoleNames = ['Hướng dẫn chương trình', 'Hướng dẫn hát', 'Âm thanh', 'Trình chiếu'];
        $banRoles = DepartmentRole::whereIn('name', $banRoleNames)->get();
        foreach ($banRoles as $r) {
            RosterTemplateRole::firstOrCreate(['roster_template_id' => $tplBan->id, 'department_role_id' => $r->id]);
        }
    }
}
