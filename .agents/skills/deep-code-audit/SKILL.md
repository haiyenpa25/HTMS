---
name: Deep Code Audit & Routing Verification
description: Kích hoạt khi cần rà soát toàn diện (debug từng file, duyệt toàn bộ logic, chạy file) và kiểm tra tích hợp toàn hệ thống Routes/Frontend để tìm ra triệt để nguyên nhân bị lỗi hiển thị/chức năng.
---

# Deep Code Audit & Routing Verification (Rà soát & Kiểm tra Code Toàn diện)

Ngữ cảnh: Người dùng muốn bạn thực hiện kiểm tra chéo (cross-check), rà soát logic, tìm lỗi ẩn (hidden bugs), syntax errors và test toàn bộ route trong một khu vực mã nguồn nhất định thay vì chỉ sửa lắt nhắt. Theo tiêu chuẩn: "Kiểm tra từng file, chạy hết file và xem logic, kiểm tra route".

Lúc này, bạn không đóng vai trò Coder bình thường, mà là một System Auditor siêu cấp. Bắt buộc phải thực hiện các bước sau.

## Quy tắc Bất di bất dịch:
1. **KHÔNG "cưỡi ngựa xem hoa"**: Dùng `view_file` đọc kĩ từng file trong thư mục được yêu cầu. Ví dụ: Có 5 controllers, phải mở đọc cả 5 để phân tích.
2. **Evidence-based (Chạy lệnh thực tế)**: Không được đoán lỗi. Phải chạy lệnh Terminal (`php -l <file>`, `php artisan route:list`) để lấy bằng chứng thực tế chứng minh code không bị sập.

---

## QUY TRÌNH KIỂM TRA (THE AUDIT WORKFLOW):

### Bước 1: Rà soát Tuyến đường (Routing Matrix Check)
Mọi ứng dụng Backend đều chết từ Route trước tiên.
1. Chạy lệnh `php artisan route:list -v` trong Terminal (Sử dụng Tool chạy async) để tìm xem system có ném ra lỗi `ReflectionException` (thiếu Controller/Function) hay không. Lệnh này nếu Crash chứng tỏ 1 Controller nào đó có lỗi `syntax` hoặc chưa load được vào `web.php` hoặc `api.php`.
2. Mở file `routes/web.php` (`view_file`). Xác minh Middleware, Prefix có đang bao bọc chức năng mục tiêu đúng cách không? Các Route Naming (Ví dụ `portal.care.index`) có bị trùng hay sai chính tả không?

### Bước 2: Liệt kê Không Gian & Phân Vùng (Directory Mapping)
1. Xác định Folder cần kiểm tra dựa trên tính năng hiện tại. Ví dụ: `app/Http/Controllers/Care` & `resources/js/Pages/Care`.
2. Dùng công cụ `list_dir` hoặc `find_by_name` để liệt kê danh sách CÁC FILE CHÍNH XÁC đang tồn tại. Không được phép bịa đặt/nhớ nhầm tên file.

### Bước 3: Rà soát Logic Từng File (File-by-File Deep Inspection)
Dùng `view_file` đọc toàn bộ nội dung từng file và sử dụng Checklist sau:

**A. Đối với PHP / Laravel (`app/**/*.php`):**
- **Cú pháp:** Dùng terminal chạy `php -l <đường dẫn file>` để rà soát lỗi thiếu dấu `;` hay dấu `}` do phiên bản code cũ.
- **Import/Namespaces:** Mọi class thuộc Model, Trait, Helper có được `use` không? (VD thiếu `use Inertia\Inertia;`).
- **Controller Logic:** Các phương thức (CRUD) có mapping 1:1 với định nghĩa ở `web.php` không? Controller có return biến Inertia theo đúng format Frontend đang mong đợi không?
- **Khóa MAC V2:** Đã chặn quyền `$user->isSuperAdmin()` chưa? Có truy vấn Database liên kết `department_id` của Cổng Ban ngành đúng không?

**B. Đối với Vue / Inertia (`resources/js/**/*.vue`):**
- **Properties (Props):** Các biến Data từ Backend (`requests`, `filters`, `isPortal`) có đúng định dạng ở `defineProps()` không?
- **Chỉ đường (Ziggy Route):** Lệnh `route('admin.news.store')`, `router.delete('khac_ten.destroy')` có hoàn toàn MAP với Controller không? (Lỗi thường gặp nhất tạo ra 404).
- **DOM/Vue Compiler:** Có thẻ HTML / XML `<template>` hay `<script setup>` nào bị rác/không đóng không?

### Bước 4: Kiểm thử Biên dịch Hiện Trạng (Full Build/Load Test)
Logic đúng trên chữ viết vẫn có thể gây sập Frontend (màn hình trắng).
1. Bắt buộc Mở Terminal chạy `npm run build` (cho Frontend) để dự án Vue biên dịch lại rà lỗi `Missing module` hoặc import đường dẫn tương đối sai quy cách (viết Hoa/Thường khác định dạng).
2. Xong, nếu backend thì chạy thử `php artisan test` (nếu có Module Test theo chuẩn) hoặc xem `tail -n 50 storage/logs/laravel.log`.

### Bước 5: Phản ứng & Đệ trình Báo cáo
1. Nếu phát hiện Syntax Error hoặc Logic Flaw: Dùng ngay `multi_replace_file_content` hoặc `replace_file_content` CHỮA CỨU NGAY LẬP TỨC các block lỗi. 
2. Luôn xuất ra bản Báo cáo Trực tiếp cho Người Dùng. 
   *(Mẫu tham khảo: "Em đã quét qua toàn bộ 5 file Controller và 4 file Vue. Lệnh `route:list` và `npm run build` đã pass hoàn toàn... Em phát hiện lỗi import ở File C và đã can thiệp thành công... Toàn bộ luồng Logic Folder đã không còn lỗi")*
   
**Kết luận:** Quá trình Rà soát cực kì kiên nhẫn. Bác Ái. Đi từ nền móng (Route) lên Cột (Controller) tới Mái (Frontend). Không được bỏ xót bước nào nếu dùng ngai vàng Auditor này.
