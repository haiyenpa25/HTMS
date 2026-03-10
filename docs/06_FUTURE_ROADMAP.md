# 06. Lộ Trình Phát Triển & Tối Ưu (Future Roadmap)

Chào mừng bạn đến với lộ trình phát triển. Tài liệu này vạch ra các định hướng để nâng cấp hệ thống CMS từ mức "hoạt động tốt" (Working Prototype) lên "tiêu chuẩn doanh nghiệp" (Enterprise Standard).

---

## MỤC TIÊU 1: TỐI ƯU HỆ THỐNG PHÂN QUYỀN (MAC CACHING)
**Vấn đề:** Hiện tại, mỗi lần chuyển đổi màn hình trên Inertia, Middleware (`CheckPortalAccess.php`) phải gọi lại database lấy danh sách `FeatureDepartment::all()` và `Feature::all()`. Việc này tốn tài nguyên kết nối (N+1 connection type).
**Giải pháp:**
- Cần đắp thêm một lớp Cache (`Cache::rememberForever('system_features', ...)`) trong `FeatureDepartment`.
- Khi SuperAdmin thay đổi cấu trúc bảng chức năng, gọi lệnh `Cache::forget('system_features')`.

## MỤC TIÊU 2: BẢN VÁ TÍNH NĂNG ĐANG DỞ DANG (WIP FEATURES)
1. **Hoàn thiện "Quên Mật Khẩu" (Forgot Password):**
   - Viết route backend `password.email` và `password.reset`.
   - Kết nối với thẻ `<a href="#">` đang bị bỏ dở trong màn hình Login.
   - Setup SMTP (Mailgun/Postmark) vào `.env.example`.
2. **Auto-Assignment (Lịch phân công tự động):**
   - Mở file `app/Services/MeetingService.php`, dòng 44.
   - Cài đặt thuật toán Round-Robin hoặc pick_random để tự động rải người hỗ trợ khi tạo Buổi nhóm mới, đúng như lời hứa trong `TODO`.

## MỤC TIÊU 3: TÍCH HỢP "DUTY ROSTER" VÀO MAIN DASHBOARD
**Hiện trạng:** Hệ thống có tính năng Lịch Sinh Hoạt (Duty Roster) mạnh mẽ bao gồm CRUD mẫu (Templates) và xuất dữ liệu in ấn. Tuy nhiên, luồng đi bị chìm và chưa có tính nhắc nhở tự động. Hiện tại chỉ có SuperAdmin hoặc một số quyền hạn mới tương tác tới nó thay vì mọi Thành viên đều thấy.
**Giải pháp:**
- **Widget Thông Báo:** Cần tích hợp một list nhỏ trên `Activities/Dashboard` hoặc `Ministry/Dashboard` thông báo: `Tuần này bạn có lịch trực Thăm viếng!`.
- **Link Rõ Ràng:** Bổ sung thẳng menu `Lịch Phân Công` ra phía trước hệ thống Portal Layout chính.

## MỤC TIÊU 4: DỌN DẸP Vue "HARD PROPS"
**Vấn đề:** Do lịch sử nâng cấp, một phần code cũ (có thể lác đác ở các modal form) vẫn gọi tĩnh theo kiểu `$page.props.errors` hoặc `$page.props.flash`. Nếu Inertia chưa Load xong sẽ gây lỗi `White Screen TypeError: Cannot read property of undefined`.
**Giải pháp:**
- Quét các cụm `$page.props` nằm thẳng trên template `.vue` và chuyển sạch sang `computed(() => usePage().props...)`. Cần thực hiện để rủi ro màn hình trắng không trở lại.

## MỤC TIÊU 5: EXPORT/IMPORT EXCEL
- Mở rộng chức năng In ấn Cực tốt hiện có bằng việc ghép nối thư viện `Laravel-Excel` (`maatwebsite/excel`).
- Cấp quyền cho hệ thống Điểm Danh khả năng: Import file Excel điểm danh từ máy chấm vân tay, hoặc Upload danh sách thành viên mới hàng loạt.
