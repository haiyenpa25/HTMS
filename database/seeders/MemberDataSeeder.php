<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Member;
use App\Models\MemberSensitive;
use App\Models\Household;
use App\Models\Relationship;
use App\Models\CareLog;
use App\Models\Talent;
use App\Models\Course;
use App\Models\User;

class MemberDataSeeder extends Seeder
{
    public function run()
    {
        // 1. Fetch or create the main pastor member
        $pastorUser = User::where('email', 'superadmin@httlthanhmyloi.com')->first();
        $pastorMember = Member::firstOrCreate(['user_id' => $pastorUser->id], [
            'full_name' => 'Mục sư Quản nhiệm',
            'member_code' => 'MS-001',
            'email' => $pastorUser->email,
            'status' => 'active'
        ]);

        // 2. Create a Household
        $household = Household::create([
            'name' => 'Nhà Mục sư Quản nhiệm',
            'address' => '123 Đường số 4, Thạnh Mỹ Lợi, Thủ Đức',
            'notes' => 'Khu vực gần nhà thờ'
        ]);

        // 3. Update Pastor profile with rich data
        $pastorMember->update([
            'household_id' => $household->id,
            'address' => '123 Đường số 4, Thạnh Mỹ Lợi, Thủ Đức',
            'date_of_birth' => '1980-05-15',
            'gender' => 'male',
            'member_type' => 'Mục sư',
            'faith_date' => '1995-12-25',
            'is_baptized' => true,
            'baptism_date' => '1996-01-10',
            'joined_date' => '2010-06-01',
            'attendance_status' => 'Đều đặn',
            'general_notes' => 'Rất nhiệt thành trong công tác gây dựng Hội Thánh.'
        ]);

        MemberSensitive::updateOrCreate(['member_id' => $pastorMember->id], [
            'prayer_concerns' => 'Cầu nguyện cho sức khỏe của Mục sư và gia đình.',
            'pastoral_notes' => 'Mục sư có khát vọng mở rộng quy mô Hội Thánh lên gấp đôi trong 5 năm tới.',
            'occupation' => 'Mục sư Quản nhiệm',
            'marital_status' => 'Đã kết hôn'
        ]);

        // 4. Create family members
        $wife = Member::create([
            'full_name' => 'Bà Mục sư',
            'member_code' => 'BMS-001',
            'gender' => 'female',
            'household_id' => $household->id,
            'status' => 'active'
        ]);

        $son = Member::create([
            'full_name' => 'Nguyễn Văn Con',
            'member_code' => 'C-001',
            'gender' => 'male',
            'household_id' => $household->id,
            'status' => 'active'
        ]);

        // 5. Create Relationships
        Relationship::create(['member_id' => $pastorMember->id, 'related_member_id' => $wife->id, 'type' => 'spouse']);
        Relationship::create(['member_id' => $pastorMember->id, 'related_member_id' => $son->id, 'type' => 'child']);
        Relationship::create(['member_id' => $wife->id, 'related_member_id' => $son->id, 'type' => 'child']);

        // 6. Support history (Care Logs)
        CareLog::create([
            'member_id' => $pastorMember->id,
            'caregiver_id' => $pastorMember->id, // Self or other lead
            'visit_date' => '2026-02-20',
            'summary' => 'Thăm viếng định kỳ đầu năm',
            'notes' => 'Mục sư và gia đình bình an.',
            'is_sensitive' => false
        ]);

        CareLog::create([
            'member_id' => $pastorMember->id,
            'caregiver_id' => $pastorMember->id,
            'visit_date' => '2026-02-25',
            'summary' => 'Trao đổi về kế hoạch Mục vụ nhạy cảm',
            'notes' => 'Chi tiết về việc đào tạo nhân sự cốt cán phục vụ âm thầm.',
            'is_sensitive' => true // Only pastor see this
        ]);

        // 7. Talents & Courses
        $talentMus = Talent::create(['name' => 'Âm nhạc (Đàn Piano)']);
        $talentPre = Talent::create(['name' => 'Giảng dạy']);
        
        $pastorMember->talents()->attach($talentMus->id, ['notes' => 'Đánh đàn lâu năm']);
        $pastorMember->talents()->attach($talentPre->id);

        $courseMDH = Course::create(['name' => 'Môn đồ hóa căn bản']);
        $pastorMember->courses()->attach($courseMDH->id, ['status' => 'completed', 'completion_date' => '2024-12-30']);

        $this->command->info('Dữ liệu mẫu tín hữu phong phú đã được tạo!');
    }
}
