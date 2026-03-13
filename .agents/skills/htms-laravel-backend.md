---
description: HTMS Laravel Backend & Controllers Guidelines
---

# HTMS Backend Logic (Laravel Controllers)

Khi bạn viết mới hoặc sửa một Controller liên quan đến hệ thống Portal (như `MinistryPortalController`, `FinancePortalController`, `DutyRosterController`, v.v.), hãy tuân thủ NGHIÊM NGẶT các quy định sau:

## 1. Portal Route Detection
- Để xác định request có đến từ portal hay không, LUÔN dùng regex kiểm tra `request()->is('portal*')`.
- Nếu vào `isPortal`, bạn BẮT BUỘC phải lấy `active_portal_dept_id` từ session.

## 2. Global Admin Fallback
- Khi `active_portal_dept_id` bị null hoặc mảng `departments` của user trống, LUÔN kiểm tra xem user có phải *Global Admin* hay không (dựa vào `$user->hasRole('admin')`). Bỏ qua lỗi 403 nếu họ là Admin.

## 3. Inertia Standard Props
- Đối với **bất kỳ hàm nào trả về Inertia::render** bên trong giao diện Portal (`show`, `index`, `edit`), bạn PHẢI pass ít nhất 5 props chuẩn này:
  ```php
  'isPortal' => true,
  'portalType' => session('active_portal_type', 'ministry'),
  'department' => $currentDepartment, // Model instance hoặc mảng rút gọn
  'availableDepartments' => $availableDepartments, // Danh sách để switch
  'isGlobalAdmin' => $isAdmin // Tránh phải tính toán lại ở frontend
  ```
- Nếu truyền thiếu 1 trong 5 biến này, Frontend `PortalLayout` có thể gây ra lỗi trắng trang hoặc mất Sidebar.

## Triggers
Kích hoạt khi: `controller`, `laravel`, `php`, `backend`, `inertia`, `routing`, `props`, `session`, `portal`.
