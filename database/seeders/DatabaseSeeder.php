<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * ==============================================================
     *  FRESH DEPLOYMENT — Server mới / DB trống:
     *
     *    php artisan migrate --seed
     *    → Chạy FoundationSeeder: Church + Roles + 17 Features +
     *      20 Departments + OrgRoles + Level 1 MAC config + SuperAdmin
     *
     *  THÊM TÀI KHOẢN ĐẠI DIỆN CÁC BAN (tuỳ chọn):
     *    php artisan db:seed --class=OrgStructureSeeder
     *
     *  DỮ LIỆU MẪU / DEMO (tuỳ chọn):
     *    php artisan db:seed --class=DemoDataSeeder
     * ==============================================================
     */
    public function run(): void
    {
        $this->call([
            FoundationSeeder::class,   // Must run first — sets up everything
        ]);
    }
}
