# CẨM NANG YÊU CẦU TÍNH NĂNG (PROMPT GUIDE)

Để tôi (Antigravity hoặc bất kỳ AI nào khác) có thể code chuẩn xác 100% theo kiến trúc của dự án, bạn hãy copy và sử dụng nguyên văn đoạn tin nhắn sau khi yêu cầu tính năng mới:

---

## ✂️ BẢN MẪU (COPY ĐOẠN DƯỚI ĐÂY) ✂️

```text
Tôi cần phát triển một tính năng mới trong hệ thống CMS. Hãy đọc kỹ các file quy chuẩn trước khi bắt tay làm:
1. `docs/01_ARCHITECTURE.md` (Hiểu cấu trúc Database và Portal).
2. `docs/02_UI_UX_STANDARDS.md` và `UI-UX.md` (Hiểu chuẩn thẻ UI, Animation, Mobile FAB).
3. `docs/03_MAC_PERMISSIONS.md` (Hiểu cơ chế cấp quyền theo khối Ban ngành thay vì User Role).

Sau khi đọc, hãy phân tích yêu cầu sau đây và đưa ra Implementation Plan (Kế hoạch thực thi) trước khi đổi file:

[ĐIỀN CHI TIẾT YÊU CẦU CỦA BẠN VÀO ĐÂY]
Ví dụ:
- Tính năng: Quản lý Quỹ Khuyến Học.
- Dành cho Portal: Mục vụ (Ministry).
- Chức năng: Thêm/Sửa/Xóa đơn xin học bổng.

Yêu cầu bắt buộc:
- Giao diện phải chuẩn "Premium Slate" và Tailwind.
- Nút bấm thêm mới trên Mobile phải dùng thẻ FAB (Fixed góc dưới phải).
- Form Thêm/Sửa phải dùng SlideOver component.
- Phải thêm icon `(i)` (Tooltip hướng dẫn) kế bên các tiêu đề tính năng khó hiểu để giải thích rõ cho người dùng.
```

---

## TẠI SAO BẠN NÊN DÙNG MẪU NÀY?
1. **Ép AI Đọc Luật:** AI đôi khi tự "quên" các luật thiết kế có sẵn. Việc yêu cầu đọc trực tiếp 3 file `docs/` sẽ nạp lại vào bộ nhớ ngữ cảnh chính xác của CSDL, phân quyền và Giao diện hệ thống bạn.
2. **Kế hoạch trước khi Code:** Đòi hỏi AI phải suy nghĩ luồng chạy (Tạo bảng DB -> Chỉnh Model -> Phân quyền MAC -> Controller -> Vue UI) trước khi thực sự phá code.
3. **Giảm thiểu lỗi vặt:** Ràng buộc chặt các thành phần bắt buộc (SlideOver, FAB icon, Tooltip).
