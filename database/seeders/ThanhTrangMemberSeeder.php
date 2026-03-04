<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Member;
use App\Models\Department;
use App\Models\Team;
use App\Models\OrgMembership;
use App\Models\OrgRole;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ThanhTrangMemberSeeder extends Seeder
{
    public function run()
    {
        $dept = Department::where('code', 'BTTR')->first();
        if (!$dept) {
            $dept = Department::create(['name' => 'Ban Thanh Tráng', 'code' => 'BTTR']);
        }

        // Teams
        $teams = [
            'bdh' => Team::updateOrCreate(['department_id' => $dept->id, 'name' => 'Ban điều Hành'], ['code' => 'BTTR_BDH']),
            'danien' => Team::updateOrCreate(['department_id' => $dept->id, 'name' => 'Tổ Đa-ni-ên'], ['code' => 'BTTR_DN']),
            'phalo' => Team::updateOrCreate(['department_id' => $dept->id, 'name' => 'Tổ Pha-lô'], ['code' => 'BTTR_PL']),
            'philip' => Team::updateOrCreate(['department_id' => $dept->id, 'name' => 'Tổ Phi-líp'], ['code' => 'BTTR_PP']),
            'giosue' => Team::updateOrCreate(['department_id' => $dept->id, 'name' => 'Tổ Giô-suê'], ['code' => 'BTTR_GS']),
        ];

        $roleMember = OrgRole::where('code', 'bv')->first();
        $roleLeader = OrgRole::where('code', 'tt')->first();

        $membersData = [
            // Tổ Phi-líp (1-31)
            ['name' => 'Ksor H\' Miram', 'dob' => '2000-04-12', 'address' => '', 'phone' => '', 'team' => 'philip'],
            ['name' => 'Rmah Toàn', 'dob' => '1999-10-15', 'address' => '', 'phone' => '', 'team' => 'philip'],
            ['name' => 'Trần Thị Thanh Thảo', 'dob' => '1995-06-01', 'address' => '', 'phone' => '0971520748', 'team' => 'philip'],
            ['name' => 'Nguyễn Thế Hải', 'dob' => '1993-03-05', 'address' => '407/5 Nguyễn Thị Định, Cát Lái, Tp. Thủ Đức', 'phone' => '0934987202', 'team' => 'philip'],
            ['name' => 'Lê Thị Hoàng Yến', 'dob' => '1993-05-28', 'address' => '407/5 Nguyễn Thị Định, Cát Lái, Tp. Thủ Đức', 'phone' => '0877115836', 'team' => 'philip'],
            ['name' => 'Nguyễn Quốc Khánh', 'dob' => '1993-01-26', 'address' => '', 'phone' => '0773777910', 'team' => 'philip'],
            ['name' => 'La Minh Hoàng', 'dob' => '1992-11-29', 'address' => '20 Đường 38, Bình Trưng Tây, Tp. Thủ Đức', 'phone' => '0934078190', 'team' => 'philip'],
            ['name' => 'Nguyễn Đặng Thảo Nguyên', 'dob' => '1992-04-17', 'address' => '', 'phone' => '0935092920', 'team' => 'philip'],
            ['name' => 'Phạm Kiều Trâm', 'dob' => '1992-05-18', 'address' => '', 'phone' => '0906860489', 'team' => 'philip'],
            ['name' => 'Cao Thiên Ngọc', 'dob' => '1992-04-04', 'address' => '', 'phone' => '0932166032', 'team' => 'philip'],
            ['name' => 'Y Đuen Êban', 'dob' => '1991-10-13', 'address' => '154 Đường 67, Cát Lái, Tp. Thủ Đức', 'phone' => '0903093041', 'team' => 'philip'],
            ['name' => 'Nguyễn Ngọc Hà Thi', 'dob' => '1991-05-19', 'address' => '154 Đường 67, Cát Lái, Tp. Thủ Đức', 'phone' => '0902659747', 'team' => 'philip'],
            ['name' => 'Thiên Nhân', 'dob' => '1991-12-14', 'address' => '', 'phone' => '0963577778', 'team' => 'philip'],
            ['name' => 'Nhật Thụy', 'dob' => '1991-07-21', 'address' => '', 'phone' => '0939926929', 'team' => 'philip'],
            ['name' => 'Nguyễn Thị Trà My', 'dob' => '1990-03-05', 'address' => '42 Thạnh Mỹ Lợi, Thạnh Mỹ Lợi, Tp. Thủ Đức', 'phone' => '0978550426', 'team' => 'philip'],
            ['name' => 'Phạm Anh Tuấn', 'dob' => '1990-09-12', 'address' => '42 Thạnh Mỹ Lợi, Thạnh Mỹ Lợi, Tp. Thủ Đức', 'phone' => '0399182821', 'team' => 'philip', 'role' => 'tt'],
            ['name' => 'Nguyễn Ngọc Tuệ', 'dob' => '1990-11-07', 'address' => '', 'phone' => '0905251525', 'team' => 'philip'],
            ['name' => 'Huỳnh Giang Duy Vũ', 'dob' => '1990-02-18', 'address' => '', 'phone' => '', 'team' => 'philip'],
            ['name' => 'Lê Kim Huệ', 'dob' => '1989-05-07', 'address' => '20 Đường 38, Bình Trưng Tây, Tp. Thủ Đức', 'phone' => '0978436182', 'team' => 'philip'],
            ['name' => 'Nguyễn Thị Nhung', 'dob' => '1988-02-29', 'address' => '42 Đường 28, Cát Lái, Tp. Thủ Đức', 'phone' => '0928220493', 'team' => 'philip'],
            ['name' => 'Trịnh Thế Hân', 'dob' => '1988-12-18', 'address' => '13 Vành đai Tây, An Khánh, Tp. Thủ Đức', 'phone' => '0919499857', 'team' => 'philip'],
            ['name' => 'Nguyễn Kim Long', 'dob' => '1988-09-05', 'address' => '', 'phone' => '0905673400', 'team' => 'philip'],
            ['name' => 'Nguyễn Văn Hưng', 'dob' => '1987-01-03', 'address' => '194 Hồ Văn Huê, Phường 9. Quận Phú Nhuận', 'phone' => '0972400688', 'team' => 'philip'],
            ['name' => 'Nguyễn Thanh Tuấn', 'dob' => '1987-04-25', 'address' => '42 Đường 28, Cát Lái, Tp. Thủ Đức', 'phone' => '0376258520', 'team' => 'philip'],
            ['name' => 'Hoàng Thị Giang', 'dob' => '1987-10-06', 'address' => 'Hommyland 3, ..', 'phone' => '0917555085', 'team' => 'philip'],
            ['name' => 'Nguyễn Thị Nhật Thiên', 'dob' => '1987-11-09', 'address' => '', 'phone' => '0902680274', 'team' => 'philip'],
            ['name' => 'Trương Thị Thanh Thảo', 'dob' => '1986-04-26', 'address' => '649/2 Nguyễn Thị Định, Cát Lái, Tp. Thủ Đức', 'phone' => '0901094521', 'team' => 'philip'],
            ['name' => 'Nguyễn Thị Thu Hiền', 'dob' => '1986-08-10', 'address' => '603 Nguyễn Thị Định, Cát Lái, Tp. Thủ Đức', 'phone' => '0933833967', 'team' => 'philip'],
            ['name' => 'Vũ Kiều Oanh', 'dob' => '1986-07-17', 'address' => '194 Hồ Văn Huê, Phường 9. Quận Phú Nhuận', 'phone' => '0904067880', 'team' => 'philip'],
            ['name' => 'Huỳnh Nhuận Tâm', 'dob' => '1986-11-07', 'address' => '301 Lô A1, Chung cư Thạnh Mỹ Lợi F, Thạnh Mỹ Lợi, Tp. Thủ Đức', 'phone' => '0906488319', 'team' => 'philip'],
            ['name' => 'Nguyễn Ngọc Tuấn', 'dob' => '1986-06-13', 'address' => '', 'phone' => '0905058053', 'team' => 'philip'],

            // Tổ Giô-suê (32-55)
            ['name' => 'Lê Quang Trung Tín', 'dob' => '1985-02-21', 'address' => 'Hommyland 3, ..', 'phone' => '0919762274', 'team' => 'giosue'],
            ['name' => 'Châu Thị Mai', 'dob' => '1985-12-04', 'address' => '', 'phone' => '', 'team' => 'giosue'],
            ['name' => 'Nguyễn Thị Mỹ Ngọc', 'dob' => '1984-11-13', 'address' => '45/9 , Đường 32, Thạnh Mỹ Lợi, Tp. Thủ Đức', 'phone' => '0938603284', 'team' => 'giosue', 'role' => 'tt'],
            ['name' => 'Lê Khắc Đại Lộc', 'dob' => '1984-05-23', 'address' => '30/5, Thạnh Mỹ Lợi, Thạnh Mỹ Lợi, Tp. Thủ Đức', 'phone' => '0947998847', 'team' => 'giosue'],
            ['name' => 'Tô Bích Trâm', 'dob' => '1984-07-24', 'address' => '', 'phone' => '', 'team' => 'giosue'],
            ['name' => 'Thái Nhựt Bình', 'dob' => '1983-03-06', 'address' => '31, Đường 18, Bình Trưng Tây, Tp. Thủ Đức', 'phone' => '0976181237', 'team' => 'giosue'],
            ['name' => 'Bùi Thị Thu Hiền', 'dob' => '1983-02-07', 'address' => '31, Đường 18, Bình Trưng Tây, Tp. Thủ Đức', 'phone' => '0362688466', 'team' => 'giosue'],
            ['name' => 'Huỳnh Nhật Kha My', 'dob' => '1982-03-17', 'address' => 'Ấp Cát, Xã Phú Hữu, . . Nhơn Trạch', 'phone' => '0908988913', 'team' => 'giosue'],
            ['name' => 'Hồ Thị Minh Nhựt', 'dob' => '1982-10-01', 'address' => '', 'phone' => '0705705474', 'team' => 'giosue'],
            ['name' => 'Hồ Thị Diễm Ngọc', 'dob' => '1981-12-19', 'address' => '', 'phone' => '0972682035', 'team' => 'giosue'],
            ['name' => 'Nguyễn Nguyên Bá', 'dob' => '1980-10-27', 'address' => 'CC HomyLand 2, Bình Trưng Tây, Tp. Thủ Đức', 'phone' => '0903101754', 'team' => 'giosue'],
            ['name' => 'Phan Văn Hoàng', 'dob' => '1978-01-01', 'address' => '', 'phone' => '', 'team' => 'giosue'],
            ['name' => 'Nguyễn Hoa Thiên Lý', 'dob' => '1978-03-17', 'address' => '54A, Trần Văn Giáp, Hiệp Tân, Quận Tân Phú', 'phone' => '0902888001', 'team' => 'giosue'],
            ['name' => 'Lưu Văn Minh', 'dob' => '1978-05-18', 'address' => '40/2, Đường 836, Phú Hữu, Tp. Thủ Đức', 'phone' => '0398687567', 'team' => 'giosue'],
            ['name' => 'Nguyễn Yến Thanh', 'dob' => '1978-08-27', 'address' => '', 'phone' => '0827708278', 'team' => 'giosue'],
            ['name' => 'Huỳnh Tấn Trực', 'dob' => '1978-11-16', 'address' => '', 'phone' => '', 'team' => 'giosue'],
            ['name' => 'Đặng Xuân Hương', 'dob' => '1977-11-30', 'address' => 'Căn 202 - Lô B2, CC Thạnh Mỹ Lợi , . Tp. Thủ Đức', 'phone' => '0908475364', 'team' => 'giosue'],
            ['name' => 'Quách Thanh Dũng', 'dob' => '1977-12-24', 'address' => '126, Lê Văn Thịnh, Bình Trưng Tây, Tp. Thủ Đức', 'phone' => '0935913441', 'team' => 'giosue'],
            ['name' => 'Trần Thị Sen', 'dob' => '1977-09-10', 'address' => '126, Lê Văn Thịnh, Bình Trưng Tây, Tp. Thủ Đức', 'phone' => '0965430060', 'team' => 'giosue'],
            ['name' => 'Nguyễn Thị Hồng Việt', 'dob' => '1976-01-17', 'address' => 'CC HomyLand 2, Bình Trưng Tây, Tp. Thủ Đức', 'phone' => '0908868909', 'team' => 'giosue'],
            ['name' => 'Huỳnh Thị Ngọc Lâm', 'dob' => '1975-10-02', 'address' => '84/5 , Bình Trưng, Bình Trưng Đông, Tp. Thủ Đức', 'phone' => '0898275113', 'team' => 'giosue'],
            ['name' => 'Nguyễn Sơn Đông', 'dob' => '1971-10-23', 'address' => '54A, Trần Văn Giáp, Hiệp Tân, Quận Tân Phú', 'phone' => '0903305029', 'team' => 'giosue'],
            ['name' => 'Nguyễn Thị Ngọc Mỹ', 'dob' => '1971-02-20', 'address' => '898/7, Nguyễn Thị Định, Thạnh Mỹ Lợi, Tp. Thủ Đức', 'phone' => '0905297004', 'team' => 'giosue'],
            ['name' => 'Nguyễn Thị Hạ Thương', 'dob' => '1970-01-01', 'address' => '', 'phone' => '', 'team' => 'giosue'],
        ];

        foreach ($membersData as $item) {
            $member = Member::updateOrCreate(
                ['full_name' => $item['name'], 'phone' => $item['phone']],
                [
                    'date_of_birth' => $item['dob'],
                    'address' => $item['address'],
                    'status' => 'Chính thức',
                    'member_type' => 'official'
                ]
            );

            // Membership in Department
            OrgMembership::updateOrCreate(
                [
                    'member_id' => $member->id,
                    'model_type' => Department::class,
                    'model_id' => $dept->id,
                ],
                [
                    'org_role_id' => $roleMember->id,
                    'is_active' => true
                ]
            );

            // Membership in Team
            $team = $teams[$item['team']];
            OrgMembership::updateOrCreate(
                [
                    'member_id' => $member->id,
                    'model_type' => Team::class,
                    'model_id' => $team->id,
                ],
                [
                    'org_role_id' => isset($item['role']) ? $roleLeader->id : $roleMember->id,
                    'is_active' => true
                ]
            );
        }
    }
}
