---
description: HTMS Architecture & Matrix Access Control
---

# HTMS Architecture Guidelines

Bạn đang làm việc trên hệ thống quản lý hội thánh HTMS (Laravel Inertia + Vue 3).
Dự án này sử dụng kiến trúc Portal đa lớp và hệ thống phân quyền Matrix Access Control đặc thù. Bất cứ khi nào bạn viết code Controller hoặc Vue Component cho dự án này, hãy tuân thủ nghiêm ngặt các quy tắc dưới đây.

## 1. Matrix Access Control (MAC)
Hệ thống phân quyền của HTMS dựa trên cấu trúc lưới 2 chiều:
- **Tier 1 (Departments - Ban ngành):** Cấp độ gốc. Người dùng thuộc về ban ngành nào sẽ chỉ thấy dữ liệu của ban ngành đó (Trừ Global Admin).
- **Tier 2 (Roles - Vai trò):** Kế thừa từ Tier 1. 

**Quy tắc:**
- Luôn kiểm tra quyền dựa trên computed properties an toàn trong Vue: `isAdmin`, `isLeader`, v.v. TUYỆT ĐỐI KHÔNG dùng trực tiếp `auth.user.roles.includes(...)` có thể gây lỗi undefined nếu mảng rỗng.
- Các route quản trị hệ thống (`/users`, `/features`) chỉ dành cho Global Admin.

## 2. Portal Segregation (Phân tách Cổng thông tin)
Hệ thống chia làm nhiều Portals riêng biệt cho các ban ngành:
- **`/portal` (Portal Mode):** Điểm truy cập chung. Khi người dùng vào đây, Controller PHẢI đọc `active_portal_dept_id` từ session để biết họ đang xem dữ liệu của ban nào.
- Dữ liệu trả về (inertia props) cho các route `/portal/*` LUÔN PHẢI bao gồm: 
  `isPortal` (boolean), `department` (object), `availableDepartments` (array), `isGlobalAdmin` (boolean), `portalType` (string).

## 3. The `PortalLayout` Requirement
Mọi component nằm trong thư mục `resources/js/Pages` mà được render từ các controller của Portal (có prefix `/portal/`) ĐỀU PHẢI sử dụng `PortalLayout` khi `isPortal = true`.
- Sử dụng cú pháp Component động: `<component :is="isPortal ? PortalLayout : DefaultLayout">` ở ngoài cùng template.

## Triggers
Kích hoạt skill này khi tác vụ liên quan đến: `portal`, `layout`, `permission`, `matrix`, `controller`, `auth`, `role`, `department`.
