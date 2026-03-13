---
description: HTMS Vue 3 & Inertia.js Development Guidelines
---

# HTMS Frontend Guidelines (Vue 3 + Inertia)

Mỗi khi bạn được yêu cầu sửa, tạo mới, hoặc review code frontend cho HTMS, hãy áp dụng các nguyên tắc sau:

## 1. Safety Checks (Kiểm tra an toàn)
- **Role Checking:** Dữ liệu người dùng từ `usePage().props.auth.user` có thể thiếu trường `roles`. Không bao giờ được viết trực tiếp:
  `if (user.roles.includes('admin'))`
  Thay vào đó, LUÔN dùng computed properties an toàn với toán tử `?.` hoặc `|| []`:
  ```javascript
  const userRoles = computed(() => usePage().props.auth.user?.roles || []);
  const isAdmin = computed(() => userRoles.value.includes('admin'));
  ```

## 2. Dynamic Layouts (Portal vs Standard)
- Nếu trang (Page Vue Component) được gọi từ một Portal Controller, nó phải nhận prop `isPortal` và tự động switch layout tương ứng.
- **Quy tắc template gốc:**
  ```vue
  <template>
      <component :is="isPortal ? PortalLayout : DefaultGroupedLayout" 
                 :department="department" :availableDepartments="availableDepartments">
          <!-- Nội dung -->
      </component>
  </template>
  ```

## 3. UI System & TailwindCSS
- Dự án sử dụng TailwindCSS. Luôn ưu tiên dùng class của Tailwind thay vì viết CSS tùy chỉnh (custom CSS).
- Bảng thiết kế theo chuẩn Indigo (màu chủ đạo của ứng dụng). Giữ thiết kế gọn gàng, có khoảng trống (padding/margin hợp lý).
- Không tự ý xóa bỏ các block code cũ chưa hiểu rõ mục đích, đặc biệt là các Component con. Nếu cần tối ưu, hãy comment lại hoặc giải thích rõ ràng.

## Triggers
Kích hoạt khi: `vue`, `inertia`, `component`, `frontend`, `ui`, `tailwind`, `layout`, `props`, `template`.
