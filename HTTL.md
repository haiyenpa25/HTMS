# Kiến Trúc Multi-Tenant CMS (Dữ Liệu Cách Ly 100%)

Biên bản lưu trữ phương án thiết kế kiến trúc hệ thống phục vụ nhiều Hội Thánh (Tenants).

## 1. Phương Án Lựa Chọn: Custom Domain + Shared Source Code (stancl/tenancy)

**Mô hình hoạt động:**
- **Source Code (Mã nguồn):** Duy trì duy nhất 1 bộ Source Code (Laravel + Vue Inertia + Tailwind) cài đặt trên Server (CyberPanel).
- **Database (Cơ sở dữ liệu):** Phân chia thành 2 tầng:
  1. **Landlord DB (DB Trung Tâm):** Chứa thông tin quản lý các Hội Thánh (Tenants) và Tên miền (Domains). Không chứa dữ liệu nghiệp vụ.
  2. **Tenant DB (DB Hội Thánh):** Mỗi Hội Thánh một Database riêng biệt (vd: `tml_db`, `thanhtuyen_db`). Cấu trúc các bảng giống hệt nhau (`members`, `meetings`, `departments`, `features`).
- **Tên miền (Domain):** Mỗi Hội Thánh có thể sử dụng tên miền riêng (ví dụ: `httlthanhtuyen.org`) hoặc Subdomain (`thanhtuyen.cms.com`). Tất cả đều trỏ về (Alias / Addon Domain) chung thư mục mã nguồn gốc trên CyberPanel.

## 2. Ưu Điểm Nổi Bật (Tại sao đây là phương án tốt nhất?)

1. **Cách ly dữ liệu (Tenant Isolation) 100%:** Dữ liệu vật lý ở các DB khác nhau. Tuyệt đối không có rủi ro Hội Thánh này tiếp cận được dữ liệu của Hội Thánh khác do lỗi câu lệnh truy vấn.
2. **Không phá vỡ logic nghiệp vụ hiện tại (Zero Code Rewrite):** Các truy vấn như `Member::all()` trong Controller, hay cơ chế MAC (Matrix Access Control) đang chạy trong `Configs` hoàn toàn không cần viết lại. Thư viện `stancl/tenancy` sẽ tự động chuyển đổi kết nối DB (Switch Context) dựa trên tên miền truy cập trước khi chạm tới Controller.
3. **White-labeling Chuyên Nghiệp:** Các Hội Thánh tham gia sẽ thấy hệ thống chạy trên tên miền riêng của họ, mang lại cảm giác sở hữu một phần mềm độc lập, gia tăng uy tín và dễ dàng thuyết phục triển khai.
4. **Bảo trì nhàn hạ (Single Source of Truth):** Khi cập nhật tính năng mới (chỉ cần chạy `git pull`), tất cả các Hội Thánh (Dù chạy tên miền nào) đều nhận được bản cập nhật ngay lập tức.
5. **Scale (Mở rộng) linh hoạt:** Các HT lớn có thể sử dụng Server/DB mạnh hơn, tác rời khỏi cụm DB của HT nhỏ dễ dàng. Back-up và Restore dữ liệu chỉ giới hạn và khoanh vùng cho từng HT.

## 3. Thách Thức và Yêu Cầu Triển Khai (To-do List)

1. **CyberPanel Configuration (Server):** 
   - Phải thiết lập Alias Domain hoặc Addon Domain trỏ về thư mục `public_html/cms_master`.
   - Cấp phát chứng chỉ SSL (Let's Encrypt) thủ công cho từng Domain/Subdomain mới thêm vào để website có `https://`.
2. **Database Management (Migration):**
   - Lệnh `php artisan tenants:migrate` sẽ là lệnh mới thay cho lệnh cũ để cập nhật DB structure cho đồng loạt tất cả các hệ thống (Loop qua các DB).
3. **Quản trị trung tâm (Landlord Dashboard):**
   - Cần phát triển thêm một trang Admin cấp tổng quản lý (Super Admin của hệ thống, không phải Super Admin của HT) để thực hiện tác vụ: Tạo HT mới, Gán Domain, Quét Migration.

---
*Ghi chú: Đã chốt phương án này. Sẽ tiến hành bàn bạc chi tiết kế hoạch các bước triển khai kỹ thuật sau.*
