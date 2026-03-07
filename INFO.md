# INFO: CMS System Architecture & MAC Documentation

This file serves as a comprehensive overview of the Church Management System's architecture, specifically outlining how user permissions interact with different operational branches (Deacon, Ministry, Activities) via the Matrix Access Control (MAC) system.

## 1. System Topology

The system comprises three independent operational portals intended for end-users, managed globally by a central super-admin panel:
1. **Activities Portal (`/portal`)**: Targeted for Demographics groups (Thanh Niên, Tráng Niên...). Controlled by `DepartmentPortalController`. Uses `PortalLayout.vue`.
2. **Ministry Portal (`/ministry`)**: Dedicated to operational ministries (Cầu Nguyện, Cơ Đốc Giáo Dục...). Controlled by `MinistryPortalController`. Uses `PortalLayout.vue`.
3. **Deacon Portal (`/deacon`)**: Restricted to church leadership (Ban Chấp Sự, Thư Ký, Mục Sư). Controlled by `DeaconPortalController`.
4. **Admin Dashboard (`/admin` / `/users`)**: SuperAdmin control area for system configuration, user management, and MAC mapping.

## 2. Core Entities

### Department (`App\Models\Department`)
Groups are segmented by `block`:
- `activities`: Ban Thanh Niên, Ban Tráng Niên, v.v.
- `ministry`: Ban Cơ Đốc Giáo Dục, Ban Thăm Viếng, Ban Truyền Giảng, v.v.
- `leadership`: Ban Chấp Sự, Ban Trị Sự.

### Matrix Access Control (MAC)
The MAC maps system functionalities dynamically to departments, meaning we no longer hardcode "Ban X has Feature Y". Instead, we assign Feature scopes:
- **Tất Cả Ban Ngành (Global)**: Standard features available universally to everyone (e.g. Thành Viên, Lịch Sinh Hoạt). 
- **Loại Ban Ngành (Block)**: Features assigned specifically to one category. Ex: `Lớp Học` only applies to `ministry` block, preventing noise in `activities`.
- **Ban Ngành Cụ Thể (Specific)**: Pinpointing a single department. Ex: Only mapping `Lớp Học` to exactly `Ban Cơ Đốc Giáo Dục`.

### Features Model (`App\Models\Feature` & `App\Models\FeatureDepartment`)
Stored in the database and manageable via the Admin Panel -> "Cấu Hình Tính Năng" (`/users`). This dynamic system pushes frontend visibility mappings efficiently.

## 3. The Access & Visibility Pipeline

Here is how a user arrives at a Portal dashboard and only sees the cards they have permission for:

1. **Authentication Redirect (`AuthController.php`)**:
   - Upon login, the system detects the user's primary "Sphere of Influence".
   - `SuperAdmin/Pastor` → Redirected to `/admin`.
   - `Deacon` role owners → Redirected to `/deacon`.
   - Users with `Ministry` memberships/features → Redirected to `/ministry`.
   - Users with `Activities` memberships/features → Redirected to `/portal` (Standard).
2. **Context Resolution Middleware (`CheckPortalAccess.php`, `EnsureMinistryContext.php`)**:
   - Evaluates the user's active session ID for the portal.
   - Generates `$userPermissions` and `$departmentFeatures` mapping.
   - **Error Handling**: Instead of `abort(403)` (which causes a blank screen in Inertia), the middleware redirects to `/login` with an error message in the session.
3. **Inertia Frontend (`Dashboard.vue` / `PortalLayout.vue`)**:
   - Extracts permissions natively directly from `usePage().props`.
   - Uses `v-if` directives to conditionally lock/unlock UI tabs and feature cards seamlessly.

## 4. Notable Tech Debt & Refactors
- **Removal of Legacy Role Calculation**: Previously, arrays comparing generic OrgRoles inside individual controllers caused bugs. Refactoring centralized logic entirely to `CheckPortalAccess.php`.
- **Safe Prop Access**: We use `usePage().props` (Inertia Hook) instead of direct `$page.props` or `router.page.props` to prevent "Cannot read properties of undefined" during initial hydration.
- **SuperAdmin Override**: System overrides allow `SuperAdmins` unfettered access across all features without explicitly creating `FeatureDepartment` mapping records for them.

*Last Updated: 2026-03-07 (Portal Access Stability Fixes)*
