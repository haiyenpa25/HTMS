<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Carbon\Carbon;

// Models
use App\Models\User;
use App\Models\Member;
use App\Models\Visitor;
use App\Models\VisitorFollowup;
use App\Models\Asset;
use App\Models\AssetLoan;
use App\Models\CareRequest;
use App\Models\EmailBroadcast;
use App\Models\Announcement;
use App\Models\Donation;
use App\Models\Fund;
use App\Models\Document;
use App\Models\EduClass;
use App\Models\EduSession;
use App\Models\FinanceFund;
use App\Models\FinanceTransaction;
use App\Models\ChronicleEntry;

class FullDemoSeeder extends Seeder
{
    public function run()
    {
        $this->command->info('🚀 Bắt đầu tạo dữ liệu mẫu (Full UI Screenshots Data)...');
        
        $admin = User::first() ?? User::factory()->create();

        // 1. Members
        $this->command->info('Tufting 50 Members...');
        if (Member::count() < 50) {
            Member::factory(50)->create();
        }
        $members = Member::all();

        // 2. Visitors (Thân Hữu)
        $this->command->info('Tufting 15 Visitors...');
        for ($i = 1; $i <= 15; $i++) {
            $visitor = Visitor::create([
                'name' => 'Thân Hữu Demo ' . $i,
                'phone' => '090' . rand(1000000, 9999999),
                'email' => 'visitor' . $i . '@demo.com',
                'status' => collect(['new', 'contacted', 'studying', 'baptized', 'lost'])->random(),
                'first_visit_date' => Carbon::now()->subDays(rand(1, 60)),
                'prayer_requests' => 'Thân hữu được mời đến từ chương trình truyền giảng.'
            ]);

            // Add Followup
            VisitorFollowup::create([
                'visitor_id' => $visitor->id,
                'user_id' => $admin->id,
                'type' => collect(['call', 'visit', 'message'])->random(),
                'contact_date' => Carbon::now()->subDays(rand(1, 5)),
                'notes' => 'Đã gọi điện hỏi thăm sức khỏe. Mời đi nhóm tuần sau.',
                'outcome' => collect(['positive', 'neutral'])->random()
            ]);
        }

        // 3. Assets
        $this->command->info('Tufting 20 Assets...');
        for ($i = 1; $i <= 20; $i++) {
            $asset = Asset::create([
                'code' => 'TS-' . str_pad($i, 4, '0', STR_PAD_LEFT) . '-' . rand(10,99),
                'name' => 'Thiết bị ' . collect(['Âm thanh', 'Máy chiếu', 'Nhạc cụ', 'Bàn ghế'])->random() . ' ' . $i,
                'category' => collect(['electronics', 'furniture', 'musical', 'other'])->random(),
                'status' => collect(['new', 'in_use', 'maintenance', 'broken', 'lost', 'liquidated'])->random(),
                'purchase_date' => Carbon::now()->subMonths(rand(1, 24)),
                'purchase_price' => rand(1000000, 20000000),
                'notes' => 'Kho định kỳ bảo dưỡng',
            ]);
        }

        // 4. Care Requests
        $this->command->info('Tufting 10 Care Requests...');
        for ($i = 1; $i <= 10; $i++) {
            CareRequest::create([
                'user_id' => $members->random()->user_id ?? $admin->id,
                'category' => collect(['prayer', 'counseling', 'feedback', 'support'])->random(),
                'title' => 'Yêu cầu cầu nguyện số ' . $i,
                'content' => 'Xin thêm lời cầu nguyện cho gia đình anh/chị do công việc kinh doanh gặp khó khăn.',
                'status' => collect(['pending', 'in_progress', 'resolved', 'closed'])->random(),
                'priority' => collect(['low', 'normal', 'high', 'urgent'])->random(),
                'assigned_to' => $admin->id,
            ]);
        }

        // 5. Broadcasts & Announcements
        $this->command->info('Tufting EmailBroadcasts & Announcements...');
        for ($i = 1; $i <= 5; $i++) {
            EmailBroadcast::create([
                'subject' => 'Thông báo quan trọng số ' . $i,
                'content' => 'Nội dung thông báo hàng loạt về việc thay đổi giờ nhóm hoặc sự kiện đặc biệt. Kính mong quý tín hữu lưu ý.',
                'status' => 'completed',
                'target_roles' => [],
                'target_departments' => [],
                'sent_at' => Carbon::now()->subDays(rand(1, 10)),
                'created_by' => $admin->id
            ]);

            Announcement::create([
                'title' => 'Bản tin Hội Thánh Hằng Tuần - Tuần ' . rand(1, 52),
                'content' => 'Ghi nhận các diễn biến sinh hoạt và học lời Chúa trong tuần. Mục sư thông báo kế hoạch tháng tới...',
                'author_id' => $admin->id,
                'scope_type' => 'global',
                'scope_id' => null,
            ]);
        }

        // 6. Documents
        $this->command->info('Tufting 10 Documents...');
        for ($i = 1; $i <= 10; $i++) {
            Document::create([
                'title' => 'Tài liệu Huấn Luyện ' . $i,
                'description' => 'Tài liệu hướng dẫn dùng cho các lớp học trường Chúa Nhật.',
                'file_path' => 'docs/dummy-' . $i . '.pdf',
                'file_type' => 'application/pdf',
                'file_size' => rand(100000, 5000000),
                'uploaded_by' => $admin->id,
            ]);
        }

        // 7. EduClasses & Finance
        $this->command->info('Tufting Education Classes & Finance...');
        for ($i = 1; $i <= 3; $i++) {
            $class = EduClass::create([
                'department_id' => rand(1, 2),
                'name' => 'Lớp Giáo Lý Báp-Têm K' . $i,
                'description' => 'Lớp học giáo lý chuẩn bị báp têm năm 2026',
            ]);

            for ($j = 1; $j <= 5; $j++) {
                EduSession::create([
                    'edu_class_id' => $class->id,
                    'topic' => 'Bài học số ' . $j,
                    'session_date' => Carbon::now()->subDays(30 - ($j * 7)),
                    'lesson_number' => $j,
                ]);
            }
        }

        // 8. Sổ Tay Hội Thánh (Chronicle Entries)
        $this->command->info('Tufting 15 Chronicle Entries...');
        $chronicleCategories = ['history', 'leadership', 'wedding', 'funeral', 'custom'];
        
        // Auto generated past leaderships
        for ($i = 1; $i <= 5; $i++) {
            ChronicleEntry::create([
                'type' => 'auto',
                'category' => 'leadership',
                'title' => 'Kết thúc nhiệm kỳ: Trưởng Ban - Ban Tráng Niên',
                'description' => 'Tín hữu Demo ' . $i . ' đã hoàn thành trách nhiệm phục vụ.',
                'occurred_at' => Carbon::now()->subYears(rand(1, 4))->subMonths(rand(1, 11)),
                'ended_at' => Carbon::now()->subMonths(rand(1, 6)),
                'subject_type' => 'App\Models\Member',
                'subject_id' => rand(1, 10),
                'meta_data' => ['days_served' => rand(365, 730)],
                'created_by' => null
            ]);
        }

        // Manual recorded events
        for ($i = 1; $i <= 10; $i++) {
            $cat = collect($chronicleCategories)->random();
            $titles = [
                'history' => ['Kỷ niệm 10 năm thành lập Hội Thánh', 'Cung hiến cơ sở mới', 'Thành lập Ban Phụ Lão'],
                'leadership' => ['Lễ Bổ nhiệm Mục sư Quản nhiệm', 'Bầu cử Ban Chấp Sự nhiệm kỳ 2024-2026'],
                'wedding' => ['Lễ Thành Hôn: Anh A & Chị B', 'Lễ Kỷ Niệm 50 Năm Ngày Cưới Ông C'],
                'funeral' => ['Lễ Tang: Cụ Bà D', 'Lễ Tang: Cụ Ông E'],
                'custom' => ['Chương trình Truyền Giảng Giáng Sinh đặc biệt', 'Chuyến truyền giáo y tế vùng sâu']
            ];

            ChronicleEntry::create([
                'type' => 'manual',
                'category' => $cat,
                'title' => collect($titles[$cat])->random(),
                'description' => 'Nội dung chi tiết sự kiện được ghi nhận lại trong cuốn sổ lưu trữ nội bộ của Hội Thánh. Sự kiện diễn ra rất phước hạnh.',
                'occurred_at' => Carbon::now()->subDays(rand(10, 1000)),
                'ended_at' => null,
                'subject_type' => null,
                'subject_id' => null,
                'created_by' => $admin->id
            ]);
        }

        $this->command->info('🎉 Hoàn tất tạo dữ liệu mẫu!');
    }
}
