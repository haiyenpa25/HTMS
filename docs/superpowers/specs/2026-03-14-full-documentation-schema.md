# Full System Documentation Schema & Seeder Plan

Generated after analyzing `routes/web.php`.

## 1. Documentation Architecture (GUI Categories)

To provide an excellent user experience, the Documentation Hub will be reorganized into 6 main pillars, covering every single route group in the system:

### Pillar 1: Học Thuyết & Cấu Hình (Getting Started)
- **Cài đặt & Dữ liệu Mẫu:** (Already done)
- **Tổng quan Kiến trúc (MAC):** (Already done)

### Pillar 2: Quản Trị Hệ Thống (System Admin)
*Dành cho SuperAdmin.*
- **PhânQuyền & Bảo Mật:** Hướng dẫn trang `/users`, thẻ Tính năng Hệ thống `/admin/features`, cấp quyền ngang (MAC).
- **Cơ Cấu Tổ Chức:** `/departments` và `/roles`, tổ chức các nhóm nhỏ (Teams).
- **Truyền Thông:** `/admin/broadcasts` (Gửi Email/App Notification hàng loạt), `/admin/announcements` (Tin tức).
- **Kho Lưu Trữ & Log:** `/documents`, `/admin/activity-logs`.

### Pillar 3: Lãnh Đạo & Chăm Sóc (Leadership & Pastoral)
*Dành cho Mục sư, Chấp sự.*
- **Bảng Điều Khiển (Dashboard):** Tổng quan số liệu toàn Hội Thánh.
- **Thân Hữu & Diễn Giả (CRM):** `/admin/visitors` (Theo dõi thân hữu tin Chúa), `/speakers`.
- **Chăm Sóc & Yêu Cầu (Care Tickets):** `/care` (Quản lý các yêu cầu cầu nguyện, thăm viếng từ tín hữu).
- **Quản lý Tài Sản (Assets):** `/admin/assets` (Nhập kho, xuất mượn, thu hồi thiết bị).

### Pillar 4: Tài Chính Cấp Cao (Master Finance)
- **Quỹ & Dâng Hiến:** `/admin/donations` (Nhập dâng hiến hằng tuần), `/admin/funds` (Quỹ cấp Hội Thánh). (Trang Finance của Ban ngành đã viết xong).

### Pillar 5: Vận Hành Thường Nhật (Daily Operations)
- **Nhân sự & Hộ Gia đình:** `/members` (Đã viết xong). Bổ sung phần Hành Trình Đức Tin (Faith Journeys).
- **Lịch & Phân Công:** `/calendar`, `/duty-rooster` (Đã viết xong).

### Pillar 6: Trải Nghiệm Cổng (Portal Deep Dive)
- **Ban Sinh Hoạt:** (Đã viết xong)
- **Ban Mục Vụ (Ministry):** Tập trung vào khác biệt (Ví dụ: Không có tài chính).
- **Cơ Đốc Giáo Dục (Education Portal):** `/ministry/education` (Quản lý Lớp học, Điểm danh, Thu quỹ lớp, Báo cáo lớp).
- **Cổng Chấp Sự / Thư Ký (Deacon Board):** `/deacon` (Báo cáo sự cố Incident, Thống kê Hội Thánh).
- **Cổng Tín Hữu (Member App):** `/member` (Giao diện Mobile-first, nộp yêu cầu Cầu nguyện).

---

## 2. Screenshot Generation Plan (FullDemoSeeder)

To take good screenshots, we need rich data. I will generate a `FullDemoSeeder` that creates:
- 50 Members with relationships (Households), Faith Journeys.
- 10 Assets with some checked out (Loans).
- 20 Care Requests (Pending, In Progress, Resolved).
- 15 Visitors (Thân hữu) with follow-up logs.
- 100 Donation records with batch numbers.
- 5 Broadcasts and Announcements.
- 10 Documents.
- 3 Active Education Classes with 5 Sessions each.
