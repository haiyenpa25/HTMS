# 01. Hệ Thống & Kiến Trúc Tổng Thể (Architecture)

Tài liệu này tổng hợp cấu trúc hệ thống CMS HT Thanh Mỹ Lợi, bao gồm kiến trúc Multi-Tenant, phân tầng Portal (Topology), cấu trúc Database và các thông tin cấu hình gốc.

---

## 1. THÔNG TIN CHUNG HỆ THỐNG
- **Framework:** Laravel 11 + Inertia.js + Vue 3
- **CSS:** TailwindCSS
- **Database:** MySQL (Local & Production giống nhau)
- **Build tool:** Vite
- **Thiết kế Đa miền (Multi-Tenant):** `stancl/tenancy`

## 2. KIẾN TRÚC MULTI-TENANT (Dữ Liệu Cách Ly 100%)
Phương án thiết kế: **Custom Domain + Shared Source Code**
- **Source Code (Mã nguồn):** Duy trì duy nhất 1 bộ Source Code gốc.
- **Database (Cơ sở dữ liệu):**
  1. **Landlord DB (DB Trung Tâm):** Chứa thông tin quản lý các Hội Thánh (Tenants) và Tên miền (Domains). Không chứa dữ liệu nghiệp vụ.
  2. **Tenant DB (DB Hội Thánh):** Mỗi DB riêng biệt cho 1 HT (vd: `tml_db`), cấu trúc giống hệt nhau, cô lập 100% rủi ro bảo mật chéo.
- **Switch Context:** Thư viện `stancl/tenancy` tự chuyển đổi DB dựa vào Domain/Subdomain trước khi chạm vào logic (Zero Code Rewrite). Mọi truy vấn cũ (ví dụ `Member::all()`) tiếp tục hoạt động nguyên bản.
- **Migration Đa DB:** `php artisan tenants:migrate` thay cho lệnh migrate cũ để cập nhật tập trung toàn cõi.

## 3. CẤU TRÚC PORTAL (System Topology)
Hệ thống gồm ba Portal độc lập hướng tới Người dùng và một Dashboard cho Quản trị viên:
1. **Activities Portal (`/portal`):** Dành cho các ban (Thanh Niên, Tráng Niên...). Controlled by `DepartmentPortalController`. UI dùng `PortalLayout.vue`. Khối: `activities`.
2. **Ministry Portal (`/ministry`):** Dành cho các hệ thống nghiệp vụ (Ban Cầu Nguyện, Ban Cơ Đốc Giáo Dục, Thăm viếng...). Khối: `ministry`.
3. **Deacon Portal (`/deacon`):** Dành riêng cho Lãnh đạo Hội thánh (Ban Chấp Sự, Thư Ký, Mục Sư). Khối: `leadership`.
4. **Admin Dashboard (`/admin` / `/users`):** Vùng SuperAdmin quản lý người dùng, domain, MAC mapping...

### Cấu trúc Pages theo Portal
- **Ban Sinh Hoạt (`resources/js/Pages/Portal/`):** Dashboard, Bảng Điểm danh (Attendance), Thăm viếng (Visitation), Thành viên (Members), Báo cáo (Reports), Tài chính (Finance).
- **Ban Mục Vụ (`resources/js/Pages/Ministry/`):** Dashboard, Thăm viếng (Visitation), Cơ Đốc Giáo Dục (Education).
- **Ban Chấp Sự (`resources/js/Pages/Deacon/`):** Chọn vai trò (Role Switch), Điểm danh HT gốc, Báo cáo Tổng hợp (YouTube/AI).

## 4. CẤU TRÚC CƠ SỞ DỮ LIỆU
### Các Bảng Dùng Chung Toàn Hệ Thống
| Bảng | Mô tả |
|---|---|
| `meetings` | Buổi nhóm (type: `church` / `department`) |
| `meeting_attendance_summaries` | Điểm danh thủ công theo ban |
| `meeting_finances` | Thu/Chi theo buổi nhóm |
| `departments` | Các ban ngành (`block` xác định không gian) |
| `members` | Tín hữu |
| `department_reports` | Báo cáo tháng ban ngành |
| `visitations` | Thăm viếng |
| `visitation_visitors` | Cá nhân đi thăm (pivot) |

### Bảng Chuyên Biệt
| Bảng | Dành cho |
|---|---|
| `deacon_*` | Ban Chấp Sự (Điểm danh HT cấp tổng, Báo cáo tháng + YouTube, Sự cố tháng) |
| `department_meetings`, `department_transactions`, `department_funds` | Tài chính ban ngành |
| `edu_sessions`, `edu_session_attendances` | CĐGD: Buổi học và điểm danh lớp học |

### Enum quan trọng (Cần tuyệt đối tuân thủ)
- `meetings.type`: `['church', 'department']`
- `visitations.reason`: `['ốm đau', 'mới tin Chúa', 'khích lệ', 'khác']`
- `visitations.visitation_type`: `['church', 'department']`
- `departments.block`: `['activities', 'ministry', 'leadership']`
