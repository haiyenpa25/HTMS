# Lịch Sự Kiện Hội Thánh - Thiết Kế Nâng Cấp (Design Spec)

## 1. Tổng quan
Dựa trên những ưu điểm trong trải nghiệm ứng dụng tại Cổng Tín Hữu (`/member`), dự án tiến hành nâng cấp toàn diện Cổng Lịch Sự Kiện dành cho Quản Trị Viên (`/calendar`). 
Mục tiêu: Đạt chuẩn UX/UI đồng bộ, làm giàu tính năng phân loại sự kiện (Event Categories) và thiết lập đối tượng khán giả mục tiêu (Visibility Scopes) chặt chẽ.

## 2. Nâng Cấp Giao Diện (UI/UX)
Sẽ không loại bỏ thư viện `FullCalendar` (vì Admin cần kéo-thả và xem theo Grid nhiều dữ liệu). Thay vào đó, ghi đè CSS theo phương thức Deep Overrides (Option B) để biến `FullCalendar` trở nên thanh lịch y hệt Custom Calendar ở Member Portal:
- Ẩn bỏ các đường viền gạch cứng nhắc rườm rà.
- Thiết lập bo góc (`rounded-xl`) cho các ô ngày tháng và hover hiệu ứng bóng đổ (`shadow-sm`).
- Thiết kế thanh Toolbar (Header Lịch) đồng màu, nút bấm bo góc 8px (Tương tự Member Portal button).
- Làm lại Form *"Thêm/Sửa Sự Kiện"* trên nền tảng **Slide Over** (kéo thanh bên phải từ ngoài vào) thay thế cho Modal truyền thống, đảm bảo không gian thao tác rộng rãi.

## 3. Kiến trúc Phân loại Sự Kiện (Event Taxonomy)
Loại bỏ kiểu chọn mã màu tùy tiện (`#ff0000`). Hệ thống sẽ ép buộc Sự kiện rơi theo 4 Trục Logic cố định. Controller sẽ cấu hình màu chuẩn theo trục:
1. **Thờ Phượng & Đại Lễ (Worship & Ceremonies)**
   - Lễ Chúa Nhật, Phục Sinh, Truyền Giảng,... 
   - Mã màu: `bg-purple-100 text-purple-700` (Tím).
2. **Học Kinh Thánh & Sinh Hoạt (Fellowship & Ministry)**
   - Lớp TCĐ, Buổi Nhóm Phụ Nữ/Thanh Niên,... 
   - Mã màu: `bg-blue-100 text-blue-700` (Xanh Dương).
3. **Công Tác Lãnh Đạo (Administration & Meetings)**
   - Họp Ban Trị Sự, Lãnh đạo, Cầu nguyện Đặc biệt... 
   - Mã màu: `bg-orange-100 text-orange-700` (Cam).
4. **Trực Sự Vụ (Duty & Assignments)** (Auto-generated)
   - Lịch trực từ bảng `duty_rosters`.
   - Mã màu: `bg-emerald-100 text-emerald-700` (Xanh Lá).

## 4. Kiểm Soát Tầm Nhìn (Audience/Visibility Scopes)
Khi tạo sự kiện mới, Admin bắt buộc phải chọn "Khán Giả":
- `global` (Toàn hệ thống): Toàn bộ HT thấy được. Tín hữu và Thân Hữu.
- `department` (Ban Ngành Cụ Thể): Chỉ các Account thuộc Ban (VD: Thanh Niên) mới thấy. Yêu cầu thêm UI "Chọn Ban Ngành" (Select Dropdown) nếu chọn Scope này.
- `internal` (Tín hữu có tải khoản): Chỉ những ai đã đăng nhập vào hệ thống mới thấy (loại bỏ vai trò Thân Hữu bên ngoài).
- `leadership` (Ban Lãnh Đạo): Chỉ cấp Mục Sư/Chấp Sự cấu hình theo Matrix Access Control được thấy các lịch nhạy cảm này.

## 5. Dữ liệu (Database Migration)
Cần cập nhật Migration/Model `Event`:
- Đổi kiểu enum/cấu hình của cột `type` để tuân thủ 4 loại (như trên).
- Đổi cột `visibility` thành `scope_type` (Enum: `global`, `department`, `internal`, `leadership`).
- Thêm cột `scope_id` (UnsignedBigInteger, nullable) chứa ID của Department (Ban ngành) nếu `scope_type = department`.

## 6. Lộ trình triển khai (Roadmap)
- Step 1: Chạy Migration database mới.
- Step 2: Ghi đè Custom CSS FullCalendar vào `/Calendar/Index.vue`.
- Step 3: Tạo UI chọn Scope_Type và Scope_ID logic hiển thị linh hoạt trên Slide Over.
- Step 4: Viết lại backend Controller Filtering Events theo Phân quyền MAC và User Departments.
