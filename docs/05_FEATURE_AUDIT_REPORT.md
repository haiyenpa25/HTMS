# 05. Báo Cáo Rà Soát Tính Năng & Tối Ưu Code (Feature Audit Report)

Tài liệu này tổng hợp kết quả Rà soát toàn bộ Codebase và UI/UX với góc nhìn của BA & Tester.

---

## 1. NÚT BẤM VÀ LUỒNG HOẠT ĐỘNG BỊ LỖI (Dead Links/Buttons)
Qua việc quét toàn bộ file `.vue` và `.php`:
- **Quên mật khẩu (Forgot Password):** Tại `resources/js/Pages/Auth/Login.vue` dòng 80, thẻ `<a href="#">` Quên mật khẩu hiện đang là dead link (chưa được gắn logic reset mật khẩu hay route backend).
- **Tổng quan Nút Bấm:** Gần như 100% nút bấm nội bộ (Submit form, Tab switch, Edit/Delete modal) đều được bind `@click` hoặc dùng `<Link>` hợp lệ. Không có nút giả mốc. Đánh giá: Rất tốt.

## 2. TÍNH NĂNG ẨN / ĐANG LÀM DỞ (WIP Features)
- **Tự động phân công nhân sự (Auto Assignment):** Tại `app/Services/MeetingService.php` dòng 44 có để lại Todo: `// TODO: Generate default personnel assignments here if applicable`. Nghĩa là luồng tạo Buổi nhóm vẫn chưa tự động gợi ý nhân sự dựa trên Template (như Lịch phân công tổng). Hệ thống phân công nhân sự đang dừng ở mức thủ công.
- **Trang Hướng Dẫn (Help/Documentation):** Trong `routes/web.php` có khai báo route `/huong-dan` trả về Inertia render `Help/Installation`. Tính năng này chạy được nhưng lại không hề có nút truy cập công khai ở màn hình Login hay Dashboard chính.

## 3. TÍNH NĂNG TỐT NHƯNG CHƯA ĐƯỢC QUAN TÂM ĐÚNG MỨC
- **Phân Công Tổng Thể (Duty Roster):** Hệ thống có toàn bộ UI/UX rất xịn (`resources/js/Pages/DutyRoster/HolisticView.vue`, Templates CRUD...) quản lý kéo thả/chọn lịch cho toàn ban ngành. Tuy nhiên, luồng truy cập vào từ các Portal đôi khi bị khuất và chưa được liên kết mạch lạc với Lịch Sinh Hoạt của từng cá nhân (User Dashboard chưa nhắc nhở "Tuần này bạn có lịch trực").
- **Tính năng In ấn / Xuất PDF:** Đã được thiết kế rất hoàn thiện với `window.print()` trên đủ 4 mặt trận báo cáo (Activities, Ministry, Finance, Deacon), có Custom CSS Print. Đây là điểm sáng cực mạnh báo cáo cho các Hội Thánh. Tính năng này nên được giới thiệu mạnh làm Key Selling Point.

## 4. TỐI ƯU HOÁ CODE (Code Optimization - N+1 Queries)
- Phát hiện Controller lạm dụng truy vấn `::all()` ở:
  - `UserController.php`
  - `DepartmentController.php`
  - `UserPermissionController.php`
  - `SystemFeatureController.php`
  > **Khuyến nghị:** Đối với `FeatureDepartment::all()` hoặc `Feature::all()`, do tính năng ít khi thay đổi nhưng load liên tục ở mọi Request cấp quyền, CẦN được bọc trong `Cache::rememberForever()`. Sẽ tiết kiệm đáng kể Database Connection.
- **Inertia Props Access Pattern:** Một số template cũ vẫn còn thói quen `$page.props.flash` thay vì dùng computed của `usePage()`. Cần quét dọn lại một đợt để xoá bỏ lỗi White Screen triệt để.
