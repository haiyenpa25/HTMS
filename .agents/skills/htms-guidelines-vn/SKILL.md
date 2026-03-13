---
description: HTMS General Safety, Format & Encoding Rules
---

# HTMS Safe Code & Encoding Principles

Dự án HTMS rất nhạy cảm với mã hóa (Encoding) tiếng Việt và việc vô tình xóa code cũ. Bạn (AI) bắt buộc phải luôn ghi nhớ các rule này:

## 1. Encoding (Mã hóa UTF-8)
- BẤT KỲ khi nào bạn dùng lệnh `echo`, `cat`, hoặc PowerShell `Set-Content` thay đổi nội dung file, BẮT BUỘC sử dụng công cụ sửa code nội bộ của bạn (code edit tools) hoặc ghi đè file với chuẩn encoding UTF-8 gốc của framework Laravel.
- **TUYỆT ĐỐI KHÔNG** để xảy ra lỗi hiển thị charset làm các chuỗi văn bản bị biến thành ký tự lạ (VD: "LiÌ£ch phân công"). Nếu phát hiện lỗi này, bạn PHẢI tự rà soát và sửa lại file (như `Show.vue` bị lỗi trước đó).

## 2. Tiếng Việt trong Giao diện (UI)
- Code Variable, Function, Table Name, Routing Name: BẮT BUỘC bằng Tiếng Anh (vd: `calendar`, `duty-roster`, `fetchData`).
- Code Template, Component Text hiển thị trên màn hình: BẮT BUỘC bằng Tiếng Việt có dấu, dịch chuẩn xác theo ngữ cảnh của Hội Thánh. (VD: "Attendance" -> "Điểm danh"; "Feature" -> "Tính năng").
- KHÔNG để text tiếng Anh lọt ra ngoài giao diện trừ khi đó là các thuật ngữ không dịch được.

## 3. Không xóa Code đang chạy (Do Not Delete Old Code Unnecessarily)
- Thay vì xóa hẳn code của các file lớn, hãy ưu tiên dùng `v-if` Vue để ẩn đi nếu nó chưa dùng tới hoặc Refactor nó gọn lại. 
- Mọi nội dung CSS/Tailwind phải tương tự nguyên gốc (màu nền, padding, icon), chỉ thay đổi cho đúng chuẩn Indigo của toàn HTMS. Không được tự ý đưa ra 1 layout hoàn toàn lệch tông.

## Triggers
Kích hoạt khi: `text`, `ui`, `vietnamese`, `encoding`, `error`, `garbled`, `style`, `refactor`, `format`.
