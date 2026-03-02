<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Member;

class MemberSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Xóa dữ liệu cũ nếu chạy re-seed
        // Member::truncate();
        
        // Tạo 200 tín hữu (hồ sơ quản lý) không nhất thiết có User Account
        // Member::factory()->count(200)->create();
    }
}
