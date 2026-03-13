# Hướng dẫn sử dụng AI Skills cho Dự án HTMS

Chào bạn, tôi (AI) đã tích hợp thành công bộ **Skills chuyên biệt** cho dự án HTMS. Bộ Skills này hoạt động như một bộ tiêu chuẩn (SOP - Standard Operating Procedures) giúp mọi AI làm việc trên dự án đều tuân thủ kiến trúc phân quyền, giao diện, và cách viết code của bạn.

## 1. Các Skills hiện có trong dự án

Tất cả rules được lưu trữ tại thư mục: `.agents/skills/`. Bạn có thể mở ra xem hoặc tự chỉnh sửa thêm bất kỳ lúc nào.

| Tên Skill | Chức năng (AI sẽ tự kích hoạt khi làm việc) |
| :--- | :--- |
| **`htms-architecture.md`** | Giúp AI hiểu hệ thống Matrix Access Control (Tier 1/Tier 2) và cách điều hướng User. Tránh AI tự bịa ra permission logic. |
| **`htms-vue-inertia.md`** | Ép AI luôn dùng `<script setup>`, Composition API, và kiểm tra biến mảng an toàn (tránh lỗi `undefined.includes()`). Bắt buộc dùng `PortalLayout` khi viết code cho Portal. |
| **`htms-laravel-backend.md`** | Ép AI luôn truyền đủ 5 props (`isPortal`, `department`, `availableDepartments`...) từ Controller xuống Vue để tránh lỗi Sidebar/trắng trang. |
| **`htms-guidelines-vn.md`** | Ép AI luôn lưu file với UTF-8, không làm hỏng font Tiếng Việt. Giữ nguyên CSS Indigo chủ đạo và không tùy tiện xóa code cũ đang chạy. |

---

## 2. Cách ra lệnh để AI làm việc hiệu quả & tự động nhất

Bạn không cần phải copy-paste lại các luật lặp đi lặp lại nữa. Hệ thống Skills của Github Copilot / Claude / Gemini / Cursor sẽ **tự động phân tích** các file này nếu thư mục `.agents/skills/` tồn tại.

Đồng thời, để chắc chắn 100% AI áp dụng luật, bạn chỉ cần dùng mẫu câu sau:

### Ví dụ 1: Khi yêu cầu AI sửa một lỗi Frontend
> *"Hãy sửa lỗi hiển thị nút bấm ở trang `/portal/duty-roster`. Trước khi code, hãy đọc **`.agents/skills/htms-vue-inertia.md`** và **`.agents/skills/htms-guidelines-vn.md`** để đảm bảo không làm hỏng giao diện tiếng Việt nhé."*

### Ví dụ 2: Khi yêu cầu AI tạo mới một chức năng Backend (API/Controller)
> *"Viết cho tôi một Controller mới để Quản lý Tài sản. Hãy tham khảo **`.agents/skills/htms-architecture.md`** để phân quyền đúng theo ban ngành và **`.agents/skills/htms-laravel-backend.md`** để truyền đúng Inertia Props nhé."*

### Ví dụ 3: Lệnh tổng quát nhất (Khuyên dùng)
> *"Hãy tự động đọc tất cả các file trong thư mục **`.agents/skills/`** để hiểu kiến trúc Rule của tôi, sau đó sửa tính năng X cho tôi."*

---

## 3. Hệ thống Workflow tự động (Bonus)

Tương tự như Skills, bạn cũng có các Workflow tự động lưu ở `.agents/workflows/`. Thay vì phải nhờ AI nhập từng dòng lệnh chạy chậm chạp, bạn có lệnh Workflow.

**Ví dụ, để Deploy code lên Github và copy lệnh Server:**
> *"Hãy chạy workflow `/git-deploy` giúp tôi."*
(Hệ thống tự động thực thi chuỗi lệnh `build`, `commit`, `push` và xuất ra hướng dẫn cho bạn).

---

## Tổng kết độ tối ưu
1. **Tiết kiệm thời gian:** Thay vì vòng vo giải thích mô hình phân quyền, session hay Inertia props, AI (tôi) mất khoảng 1 giây để quét và ghi nhớ bộ luật của bạn.
2. **Ngăn chặn lỗi hỏng (Breaks/Bugs):** AI không còn "ảo giác" tự chế ra vòng lặp role sai lệch gây trắng màn hình (lỗi thường gặp trước đây). Text tiếng Việt sẽ được xử lý cẩn thận qua luật Encoding.
3. **Mở rộng dễ dàng:** Mai mốt nếu bạn có thêm chuẩn design mới (VD: "Mọi button đều phải có màu xanh dương"), bạn chỉ cần vứt 1 dòng note vào `.agents/skills/htms-guidelines-vn.md`!

**Giờ thì HTMS Skills đã "On", bạn có thể ra lệnh cho tôi làm tính năng mới ngay nhé!**
