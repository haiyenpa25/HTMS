<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\Department;
use App\Models\OrgRole;
use App\Models\OrgMembership;
use App\Models\Member;

class DepartmentUserSeeder extends Seeder
{
    public function run()
    {
        // 1. Chuẩn bị các Chức Vụ (Org Roles)
        $roleMap = [
            'tb' => OrgRole::firstOrCreate(['code' => 'dept_lead'], ['name' => 'Trưởng ban', 'level' => 50]),
            'pb' => OrgRole::firstOrCreate(['code' => 'deputy'], ['name' => 'Phó ban', 'level' => 48]),
            'tk' => OrgRole::firstOrCreate(['code' => 'secretary'], ['name' => 'Thư ký', 'level' => 45]),
            'tq' => OrgRole::firstOrCreate(['code' => 'treasurer'], ['name' => 'Thủ quỹ', 'level' => 45]),
            'tt' => OrgRole::firstOrCreate(['code' => 'team_lead'], ['name' => 'Tổ trưởng', 'level' => 30]),
            'uv' => OrgRole::firstOrCreate(['code' => 'team_member'], ['name' => 'Uỷ viên', 'level' => 10]),
        ];

        // 2. Chạy qua tất cả Ban Ngành hiện có
        $departments = Department::all();
        $password = Hash::make('Abc.1234');

        // Phân loại đơn giản bằng từ khóa (Tên có chữ Mục vụ, Truyền thông, Âm nhạc, Thăm viếng... là Ban Mục Vụ)
        $ministryKeywords = ['mục vụ', 'truyền thông', 'âm nhạc', 'y tế', 'thăm viếng', 'chăm sóc', 'cầu nguyện'];

        foreach ($departments as $dept) {
            $isMinistry = false;
            foreach ($ministryKeywords as $kw) {
                if (str_contains(mb_strtolower($dept->name), $kw)) {
                    $isMinistry = true;
                    break;
                }
            }

            // Lấy Code để cho email ngắn (VD: BMV -> bmv). Nếu không có code thì dùng tắt chữ cái đầu (Ban Thanh Tráng -> btt)
            $deptSlug = strtolower(trim($dept->code ?? ''));
            if (empty($deptSlug)) {
                $words = explode(' ', $dept->name);
                $deptSlug = '';
                foreach ($words as $w) {
                    $deptSlug .= strtolower(substr($w, 0, 1));
                }
            }

            // Nếu là Mục vụ -> chỉ tạo TB. Nếu Sinh hoạt -> tạo 6 role.
            $rolesToCreate = $isMinistry ? ['tb'] : ['tb', 'pb', 'tk', 'tq', 'tt', 'uv'];

            foreach ($rolesToCreate as $prefix) {
                $email = "{$prefix}.{$deptSlug}@httlthanhmyloi.com";
                $name  = "{$roleMap[$prefix]->name} {$dept->name}";

                // Tạo User
                $user = User::firstOrCreate(
                    ['email' => $email],
                    [
                        'name'     => $name,
                        'password' => $password,
                        // Bỏ qua phone nếu không require.
                    ]
                );

                // Tạo Thành Viên (Member) tương ứng để liên kết
                $member = Member::firstOrCreate(
                    ['user_id' => $user->id],
                    [
                        'full_name' => $name,
                        'gender'    => 'male', // default
                    ]
                );

                // Gắn User/Member vào Ban này với Quyền này
                OrgMembership::firstOrCreate([
                    'member_id'   => $member->id,
                    'org_role_id' => $roleMap[$prefix]->id,
                    'model_type'  => Department::class,
                    'model_id'    => $dept->id,
                ]);
            }
            
            $typeLabel = $isMinistry ? 'Mục Vụ' : 'Sinh Hoạt';
            $this->command->info("Đã tạo accounts ($typeLabel) cho: " . $dept->name);
        }

        // 3. Tạo 2 tài khoản Ban Chấp Sự đặc biệt
        $deaconRoles = [
            'tk.chapsu' => OrgRole::firstOrCreate(['code' => 'deacon_secretary'], ['name' => 'Thư ký Hội Thánh', 'level' => 80]),
            'tq.chapsu' => OrgRole::firstOrCreate(['code' => 'deacon_treasurer'], ['name' => 'Thủ quỹ Hội Thánh', 'level' => 80]),
        ];

        foreach ($deaconRoles as $prefix => $orgRole) {
            $email = "{$prefix}@httlthanhmyloi.com";
            $name  = $orgRole->name;

            $user = User::firstOrCreate(['email' => $email], ['name' => $name, 'password' => $password]);
            $member = Member::firstOrCreate(['user_id' => $user->id], ['full_name' => $name, 'gender' => 'male']);

            // Cấp quyền Ban Chấp Sự (không nhét vào department cụ thể nào, ta sẽ đánh dấu qua role)
            OrgMembership::firstOrCreate([
                'member_id'   => $member->id,
                'org_role_id' => $orgRole->id,
                'model_type'  => \App\Models\Church::class, // Gắn ở cấp độ Church luôn
                'model_id'    => 1, // Mặc định Hội thánh lõi
            ]);
        }
        $this->command->info("Đã tạo 2 accounts cho Ban Chấp Sự (Thư ký, Thủ quỹ).");
    }
}
