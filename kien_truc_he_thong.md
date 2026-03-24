# HTMS - Kiến Trúc Hệ Thống (System Architecture Map)
> Cập nhật: 2026-03-24 | Version: MAC V2

---

## 1. Tổng Quan Kiến Trúc

```
Laravel 11 (Inertia.js + Vue 3) — XAMPP Local / CyberPanel VPS
├── Backend:  PHP 8.2 · Laravel 11 · MySQL
├── Frontend: Vue 3 · Inertia.js · TailwindCSS
└── Auth:     Spatie Permission · Custom MAC V2
```

---

## 2. Cổng Vào Hệ Thống (Portal Entry Points)

| Portal | URL | Middleware | Controller |
|--------|-----|-----------|------------|
| Bảng điều khiển Admin | `/dashboard` | `auth` | `DashboardController` |
| Portal Sinh Hoạt (Activities) | `/portal` | `CheckPortalAccess:activities` | `DepartmentPortalController` |
| Portal Mục Vụ (Ministry) | `/ministry` | `EnsureMinistryContext` | `MinistryPortalController` |
| Portal Tài Chính (Finance) | `/finance-portal` | `EnsureFinanceContext` | `Portal/FinancePortalController` |
| Portal Chấp Sự (Deacon) | `/deacon` | `EnsureDeaconContext` | `Portal/DeaconPortalController` |
| Portal Tín Hữu (Member) | `/member` | `auth` | `MemberPortalController` |
| Đăng nhập / Đặt lại MK | `/login` | `guest` | `Auth/AuthController` |

---

## 3. Sơ Đồ Phân Quyền MAC V2 (Matrix Access Control)

```
┌─────────────────────────────────────────────────────────┐
│  LEVEL 0 — God Mode: isSuperAdmin() → bypass tất cả    │
├─────────────────────────────────────────────────────────┤
│  LEVEL 1 — System Config (feature_departments table)    │
│  Quyết định: Feature nào được kích hoạt cho Dept nào   │
│  Admin cấu hình từ: /admin/features (SystemFeature)    │
│  Đọc bởi: FeatureAssignmentService::getAvailable...()  │
├─────────────────────────────────────────────────────────┤
│  LEVEL 2 — User Override (user_department_features)     │
│  Ghi đè Level 1 cho từng user cụ thể (tăng hoặc giảm) │
│  Admin cấu hình từ: /users (UserPermissionController)  │
│  Không có record = kế thừa Level 1 (inherit)           │
└─────────────────────────────────────────────────────────┘
```

### Flow Kiểm Tra Quyền (per request)

```
Request vào route portal.access:attendance,activities
    ↓
PortalAccessMiddleware::handle()
    ↓
PortalService::canAccess($user, $deptId, 'attendance')
    ├─ user.isSuperAdmin()? → pass
    ├─ Level 1: FeatureAssignmentService::isFeatureEnabled(dept, 'attendance')
    │   └─ false? → 403 (dept không có feature này)
    └─ Level 2: user_department_features WHERE user+dept+feature
        ├─ Không có record → kế thừa Level 1 (ALLOW)
        ├─ record.is_enabled = true  → ALLOW
        └─ record.is_enabled = false → DENY (explicit revoke)
```

---

## 4. Mapping: Feature → Route → Middleware → Controller → Frontend

### 4A. Portal Sinh Hoạt (`/portal/*`, block = `activities`)

| Feature Slug | Route | Middleware | Controller | Vue Page |
|---|---|---|---|---|
| *(portal root)* | `/portal` | `CheckPortalAccess:activities` | `DepartmentPortalController@index` | `Portal/Dashboard.vue` |
| `attendance` | `/portal/attendance` | `portal.access:attendance,activities` | `Portal/AttendanceController` | `Portal/Attendance/*.vue` |
| `members` | `/portal/members` | `portal.access:members,activities` | `Portal/PortalMemberController` | `MemberPortal/*.vue` |
| `assignments` | `/portal/duty-rooster` | `portal.access:assignments,activities` | `DutyRosterController` | `DutyRoster/*.vue` |
| `reports` | `/portal/reports` | `portal.access:reports,activities` | `Portal/DeptReportController` | *(inline)* |
| `finance` | `/portal/finance` | `portal.access:finance,activities` | `Portal/DeptFinanceController` | `Finance/*.vue` |
| `visitation` | `/portal/visitation` | `portal.access:visitation,activities` | `Portal/ActivitiesVisitationController` | `Care/*.vue` |
| `chronicles` | `/portal/chronicles` | `portal.access:chronicles,activities` | `Portal/ChronicleController` | *(Sổ Tay)* |
| `documents` | `/portal/documents` | `portal.access:documents,activities` | `DocumentController` | `Documents/*.vue` |
| `care` | `/portal/care` | `portal.access:care,activities` | `CareController` | `Care/*.vue` |
| *(logs)* | `/portal/logs` | *(none - public in portal)* | `DepartmentPortalController@logs` | *(inline)* |

### 4B. Portal Mục Vụ (`/ministry/*`, block = `ministry`)

| Feature Slug | Route | Middleware | Controller | Vue Page |
|---|---|---|---|---|
| *(root)* | `/ministry` | `EnsureMinistryContext` | `MinistryPortalController` | `Ministry/*.vue` |
| `members` | `/ministry/members` | *(none, context only)* | `Portal/PortalMemberController` | `MemberPortal/*.vue` |
| `assignments` | `/ministry/duty-rooster` | `portal.access:assignments,ministry` | `DutyRosterController` | `DutyRoster/*.vue` |
| `chronicles` | `/ministry/chronicles` | `portal.access:chronicles,ministry` | `Portal/ChronicleController` | *(Sổ Tay)* |
| `documents` | `/ministry/documents` | `portal.access:documents,ministry` | `DocumentController` | `Documents/*.vue` |
| `care` | `/ministry/care` | `portal.access:care,ministry` | `CareController` | `Care/*.vue` |
| `education-classes` | `/ministry/education/classes` | `portal.access:education-classes,ministry` | `Portal/EducationController` | `Ministry/Education/*.vue` |
| `education-attendance` | `/ministry/education/{class}/sessions` | `portal.access:education-attendance,ministry` | `Portal/EducationController` | `Ministry/Education/*.vue` |
| `education-offering` | `/ministry/education/...offering` | `portal.access:education-offering,ministry` | `Portal/EducationController` | - |
| `education-report` | `/ministry/education/report` | `portal.access:education-report,ministry` | `Portal/EducationController` | - |

### 4C. Admin Routes (`/admin/*`, middleware = `EnsureSuperAdmin`)

| Feature/Function | URL | Controller | Vue Page |
|---|---|---|---|
| Quản lý người dùng | `/users` | `UserController` | `Users/Index.vue` |
| Phân quyền MAC | `/admin/users/permissions` | `Admin/UserPermissionController` | *(trong Users/Index.vue)* |
| Cấu hình tính năng | `/admin/features` | `Admin/SystemFeatureController` | `Docs/Admin/Features.vue` |
| Biên niên sử admin | `/admin/chronicles` | `Admin/ChronicleController` | *(inline)* |
| Activity logs | `/admin/activity-logs` | `Admin/ActivityLogController` | *(inline)* |
| Tài sản/thiết bị | `/admin/assets` | `Admin/AssetController` | *(inline)* |
| Thân hữu CRM | `/admin/visitors` | `Admin/VisitorController` | *(inline)* |
| Dâng hiến | `/admin/donations` | `Admin/DonationController` | *(inline)* |
| Thông báo | `/admin/broadcasts` | `Admin/BroadcastController` | *(inline)* |
| Biểu mẫu | `/admin/forms-manager` | `Admin/FormTemplateController` | *(inline)* |

---

## 5. Models & Relationships

### Core Permission Models
```
User
├── hasMany: UserDepartmentFeature (pivot user ↔ dept ↔ feature)
├── belongsTo: Member (linked profile)
└── hasRoles: Spatie Permission

Department
├── hasMany: FeatureDepartment (Level 1 config)
├── hasMany: OrgMembership (membership records)
└── block: 'activities' | 'ministry' | 'leadership'

Feature (17 records)
├── portal_type: 'activities' | 'ministry' | 'global'
├── hasMany: FeatureDepartment (Level 1)
└── hasMany: UserDepartmentFeature (Level 2)

FeatureDepartment (Level 1 config table)
├── feature_id, department_id, scope, block_type
├── is_active: bool
└── data_scope: 'dept' | 'block' | 'global'

UserDepartmentFeature (Level 2 override table)
├── user_id, department_id, feature_id
├── is_enabled: bool (explicit enable/disable)
├── access_level: 'view' | 'manage'
└── dept_type: 'activities' | 'ministry' | 'leadership'
```

### Domain Models (Business Logic)
```
Member ─── OrgMembership ─── Department (has teams via Team)
Member ─── Household (head + members)
Member ─── FaithJourney (baptism, ordination etc.)
Meeting ─── MeetingAttendance ─── Member
Meeting ─── DutyAssignment ─── DepartmentRole
DepartmentFund ─── DepartmentTransaction
CareRequest ─── CareLog (pastoral care ticketing)
ChronicleEntry (org diary/notebook)
Visitation ─── VisitationReason
Document (file storage)
```

---

## 6. Services Layer

| Service | Purpose | Phụ thuộc |
|---------|---------|-----------|
| `PortalService` | Feature access check, dept list, allowed features | `FeatureAssignmentService`, `UserDepartmentFeature` |
| `FeatureAssignmentService` | Level 1 resolution (dept+block+global config) | `FeatureDepartment`, `Feature` |
| `MeetingService` | Meeting logic helper | `Meeting`, `DepartmentMeeting` |
| `ScopeResolver` | Resolve data scope for actions | `Department` |

---

## 7. Middleware Chain (theo portal)

### Portal Sinh Hoạt (`/portal`)
```
auth → CheckPortalAccess:activities → (route-level) portal.access:slug,activities
```
### Portal Mục Vụ (`/ministry`)
```
auth → EnsureMinistryContext → (route-level) portal.access:slug,ministry
```
### Portal Chấp Sự (`/deacon`)
```
auth → EnsureDeaconContext → (route-level) portal.access:slug,leadership
```
### Admin Routes
```
auth → EnsureSuperAdmin
```
### Global Middleware (mọi request)
```
HandleInertiaRequests → inject: auth.user, auth.allowed_features, allAvailableDepts
```

---

## 8. Features — 17 Tính Năng Chuẩn

| Slug | Tên | portal_type | Dùng ở Portal |
|------|-----|------------|----------------|
| `attendance` | Điểm Danh | `activities` | portal |
| `visitation` | Thăm Viếng | `activities` | portal |
| `members` | Thành Viên | `activities` | portal, ministry, deacon |
| `assignments` | Phân Công | `activities` | portal, ministry, deacon |
| `reports` | Báo Cáo | `activities` | portal |
| `finance` | Tài Chính | `activities` | portal |
| `education-classes` | Lớp Học | `ministry` | ministry/education |
| `education-attendance` | Điểm Danh Lớp | `ministry` | ministry/education |
| `education-offering` | Tiền Dâng Lớp | `ministry` | ministry/education |
| `education-report` | Báo Cáo Giáo Dục | `ministry` | ministry/education |
| `chronicles` | Sổ Tay HT | `global` ⭐ | portal, ministry, admin |
| `activity-logs` | Nhật Ký | `global` ⭐ | admin |
| `documents` | Tài Liệu | `global` ⭐ | portal, ministry |
| `assets` | Thiết Bị | `global` ⭐ | admin |
| `users-manager` | Người Dùng | `global` ⭐ | admin |
| `forms-manager` | Biểu Mẫu | `global` ⭐ | admin |
| `care` | Chăm Sóc | `global` ⭐ | portal, ministry |

> ⭐ = vừa được chuẩn hóa từ 'admin'/'activities' → `global` (2026-03-24)

---

## 9. Sidebar Nav Items → Permission Key Mapping (PortalLayout.vue)

| Sidebar Item | Permission Key (`p[key]`) | Fallback |
|---|---|---|
| Tổng Quan | *(luôn hiển thị)* | - |
| Điểm Danh | `attendance` | disabled nếu false |
| Thăm Viếng | `visitation` | disabled |
| Thành Viên | `members` | disabled |
| Phân Công | `assignments` | disabled |
| Báo Cáo | `reports` | disabled |
| Tài Chính | `finance` | disabled |
| Chăm Sóc | `care` | disabled |
| Sổ Tay HT | `chronicles` | disabled |
| Tài Liệu | `documents` | disabled |
| Nhật Ký | `activity-logs` | disabled |

---

## 10. Dashboard Cards → Feature Mapping (Portal/Dashboard.vue)

Các card trên dashboard lọc theo `auth.allowed_features` (mảng slugs từ `PortalService::getAllowedFeaturesForDept()`).

---

## 11. Thư Mục Quan Trọng

```
cms/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Admin/          ← Quản trị (SuperAdmin only)
│   │   │   ├── Portal/         ← Portal-specific controllers
│   │   │   ├── Auth/           ← Login/Logout/Password reset
│   │   │   └── User/           ← User self-service (Donation)
│   │   ├── Middleware/
│   │   │   ├── CheckPortalAccess.php   ← Portal entry gate (block-level)
│   │   │   ├── PortalAccessMiddleware.php ← Feature-level gate (portal.access)
│   │   │   ├── EnsureMinistryContext.php  ← Ministry portal gate
│   │   │   ├── EnsureDeaconContext.php    ← Deacon portal gate
│   │   │   ├── EnsureFinanceContext.php   ← Finance portal gate
│   │   │   ├── EnsureSuperAdmin.php       ← Admin routes gate
│   │   │   └── HandleInertiaRequests.php  ← Shared props (allowed_features)
│   ├── Models/                 ← 66 Eloquent models
│   └── Services/
│       ├── PortalService.php           ← canAccess, getAllowedFeaturesForDept
│       └── FeatureAssignmentService.php ← Level 1 resolution
├── database/
│   ├── migrations/             ← Schema
│   └── seeders/
│       ├── FeatureSeeder.php           ← 17 features chuẩn
│       └── PermissionSampleDataSeeder.php ← Dữ liệu mẫu Level 1+2
├── resources/js/
│   ├── Layouts/
│   │   └── PortalLayout.vue    ← Sidebar nav (visibleNavItems)
│   └── Pages/
│       ├── Portal/Dashboard.vue ← Feature cards
│       └── Users/Index.vue     ← Quản lý user + phân quyền
└── routes/
    └── web.php                 ← 578 lines, 5 portals + admin
```

---

## 12. Artisan Commands Hữu Ích

```bash
# Sync features (sau khi thêm/sửa FeatureSeeder)
php artisan db:seed --class=FeatureSeeder

# Apply sample permissions
php artisan db:seed --class=PermissionSampleDataSeeder

# Clear tất cả cache
php artisan optimize:clear

# Xem tất cả routes portal
php artisan route:list --path=portal

# Xem middleware của một route
php artisan route:list --name=portal.attendance.index
```

---
*Tài liệu được tạo tự động bởi AI System Audit — 2026-03-24*
