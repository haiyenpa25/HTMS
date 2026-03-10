# 03. Hệ Thống Phân Quyền (Matrix Access Control - MAC)

Hệ thống Role & Permission không dựa trên user-role cứng ngắc, mà áp dụng **Matrix Access Control (MAC)** phân tầng theo đặc thù tổ chức Hội Thánh. Định danh truy cập kết hợp giữa Vai trò cá nhân và Môi trường/Ban Ngành đang làm việc.

---

## 1. MÔ HÌNH MATRIX ACCESS CONTROL
Lõi hệ thống bao gồm `App\Models\Feature` & `App\Models\FeatureDepartment`. Các thẻ bài Tính Năng (Feature) được gán (map) linh hoạt cho các Ban Ngành theo cấu trúc 3 cấp độ trong CSDL:
- **Global:** Cấp phát có hiệu lực cho Tất cả Ban Ngành trên toàn bộ môi trường (Ví dụ: Tra cứu Thành viên). Block Type = Null.
- **Block (Bộ tứ môi trường):** Cấp phát riêng cho một Khối. (Ví dụ: Tính năng Lớp Học chỉ thuộc về `ministry`, ngăn các ban thuộc `activities` truy cập hay thấy tính năng này).
- **Specific (Ban Cụ Thể):** Áp dụng đơn lẻ (Ví dụ: Chỉ Ban Cơ Đốc Giáo Dục mới có Tính Năng này).

*SuperAdmin sở hữu override ngầm định cho toàn bộ tính năng và không cần xin phép/map.*

## 2. QUY TRÌNH LOGIN & REDIRECT (SMART ACCESS PIPELINE)
Luồng truy cập của User tránh xa việc lộ diện Error 403 (màn hình trắng).
1. **Authentication (`AuthController.php`):** User Login -> Hệ thống check "Sphere of Influence".
   - `SuperAdmin` -> Về `/users` (Admin).
   - `Chấp Sự / Mục Sư` -> Về `/deacon`.
   - Có Ban/Nhóm hệ `ministry` -> Về `/ministry`.
   - Các User thông thường -> Về `/portal`.
2. **Context Middlewares (`CheckPortalAccess.php`, `EnsureMinistryContext.php`):**
   - Lọc Request, cấp thẻ `$userPermissions` & `$departmentFeatures`.
   - Trả permission về frontend thông qua `UsePage().props`. 
   - Nếu Access Denied -> Middleware Redirect thay vì ném ra Exception, giúp SPA Load mượt mà và hiện tin nhắn báo lỗi tử tế.

## 3. CHECKLIST AN TOÀN TRONG ROLE & MODULE SỬA CODE
> **[RULE QUAN TRỌNG NHẤT]**
> Khi tạo mới hoặc can thiệp một Module trên CMS, Coder/Agent buộc phải thêm module quyền truy cập đó vào `app/Console/Commands/SyncPermissions.php` và chạy lệnh này để cấp quyền cho User quản trị: `php artisan permissions:sync`. Việc quên không làm điều này sẽ cô lập tài khoản Quản trị viên khỏi nhóm chức năng mới làm.

## 4. TÍCH HỢP VỚI FRONTEND (INERTIA)
Trên Vue, không được giữ prop local để truyền cho hệ thống check quyền. Luôn luôn extract thẳng từ Server Context.
Sử dụng:
```javascript
const page = usePage()
// Props sẽ phản ánh trực tiếp quyền hạn truy cập của User theo MAC
const systemUserPermissions = computed(() => page.props.userPermissions || []);
```
Và dùng `<template v-if="systemUserPermissions.includes('my_feature')">` để khóa/mở thẻ UI.
