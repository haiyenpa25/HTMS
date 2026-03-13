<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RestoreFeaturesSeeder extends Seeder
{
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('feature_department')->truncate();
        DB::table('features')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        DB::table('features')->insert([
            ['id' => 1, 'name' => 'Điểm Danh', 'slug' => 'attendance', 'icon' => '✅', 'description' => 'Điểm danh tại các buổi nhóm sinh hoạt', 'portal_type' => 'activities'],
            ['id' => 2, 'name' => 'Thăm Viếng', 'slug' => 'visitation', 'icon' => '💚', 'description' => 'Ghi nhận và theo dõi hoạt động thăm viếng', 'portal_type' => 'activities'],
            ['id' => 3, 'name' => 'Thành Viên', 'slug' => 'members', 'icon' => '👥', 'description' => 'Quản lý danh sách thành viên ban ngành', 'portal_type' => 'activities'],
            ['id' => 4, 'name' => 'Phân Công', 'slug' => 'assignments', 'icon' => '📋', 'description' => 'Phân công trực nhật, chức vụ trong buổi nhóm', 'portal_type' => 'activities'],
            ['id' => 5, 'name' => 'Báo Cáo', 'slug' => 'reports', 'icon' => '📊', 'description' => 'Xem và xuất báo cáo theo tháng/quý', 'portal_type' => 'activities'],
            ['id' => 6, 'name' => 'Tài Chính', 'slug' => 'finance', 'icon' => '💰', 'description' => 'Quản lý thu chi, quỹ ban ngành', 'portal_type' => 'activities'],
            ['id' => 7, 'name' => 'Lớp Học', 'slug' => 'education-classes', 'icon' => '🏫', 'description' => 'Quản lý danh sách lớp học Cơ Đốc Giáo Dục', 'portal_type' => 'ministry'],
            ['id' => 8, 'name' => 'Điểm Danh Lớp', 'slug' => 'education-attendance', 'icon' => '📝', 'description' => 'Điểm danh và chấm điểm theo buổi học', 'portal_type' => 'ministry'],
            ['id' => 9, 'name' => 'Tiền Dâng Lớp', 'slug' => 'education-offering', 'icon' => '💵', 'description' => 'Theo dõi tiền dâng theo lớp và buổi học', 'portal_type' => 'ministry'],
            ['id' => 10, 'name' => 'Báo Cáo Giáo Dục', 'slug' => 'education-report', 'icon' => '📈', 'description' => 'Báo cáo tổng hợp theo tháng cho cơ đốc giáo dục', 'portal_type' => 'ministry']
        ]);

        DB::table('feature_department')->insert([
            ['id' => 1, 'feature_id' => 10, 'block_type' => 'ministry', 'scope' => 'specific', 'department_id' => 8, 'is_active' => 1],
            ['id' => 2, 'feature_id' => 9, 'block_type' => 'ministry', 'scope' => 'specific', 'department_id' => 8, 'is_active' => 1],
            ['id' => 3, 'feature_id' => 7, 'block_type' => 'ministry', 'scope' => 'specific', 'department_id' => 8, 'is_active' => 1],
            ['id' => 4, 'feature_id' => 8, 'block_type' => 'ministry', 'scope' => 'specific', 'department_id' => 8, 'is_active' => 1],
            ['id' => 5, 'feature_id' => 1, 'block_type' => 'activities', 'scope' => 'block', 'department_id' => null, 'is_active' => 1],
            ['id' => 6, 'feature_id' => 1, 'block_type' => null, 'scope' => 'global', 'department_id' => null, 'is_active' => 0],
            ['id' => 7, 'feature_id' => 2, 'block_type' => null, 'scope' => 'global', 'department_id' => null, 'is_active' => 0],
            ['id' => 8, 'feature_id' => 3, 'block_type' => null, 'scope' => 'global', 'department_id' => null, 'is_active' => 1],
            ['id' => 9, 'feature_id' => 4, 'block_type' => null, 'scope' => 'global', 'department_id' => null, 'is_active' => 0],
            ['id' => 10, 'feature_id' => 5, 'block_type' => null, 'scope' => 'global', 'department_id' => null, 'is_active' => 0],
            ['id' => 11, 'feature_id' => 6, 'block_type' => null, 'scope' => 'global', 'department_id' => null, 'is_active' => 0],
            ['id' => 12, 'feature_id' => 7, 'block_type' => null, 'scope' => 'global', 'department_id' => null, 'is_active' => 0],
            ['id' => 13, 'feature_id' => 8, 'block_type' => null, 'scope' => 'global', 'department_id' => null, 'is_active' => 0],
            ['id' => 14, 'feature_id' => 9, 'block_type' => null, 'scope' => 'global', 'department_id' => null, 'is_active' => 0],
            ['id' => 15, 'feature_id' => 10, 'block_type' => null, 'scope' => 'global', 'department_id' => null, 'is_active' => 0],
            ['id' => 16, 'feature_id' => 2, 'block_type' => 'activities', 'scope' => 'block', 'department_id' => null, 'is_active' => 1],
            ['id' => 17, 'feature_id' => 4, 'block_type' => 'activities', 'scope' => 'block', 'department_id' => null, 'is_active' => 1],
            ['id' => 18, 'feature_id' => 5, 'block_type' => 'activities', 'scope' => 'block', 'department_id' => null, 'is_active' => 1],
            ['id' => 19, 'feature_id' => 6, 'block_type' => 'activities', 'scope' => 'block', 'department_id' => null, 'is_active' => 1],
        ]);
    }
}
