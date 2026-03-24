# HTMS Code Knowledge Graph
> Generated: 2026-03-24 14:45 | Run `php artisan htms:index` to refresh

## Navigation Guide

| File | Dùng khi nào |
|------|-------------|
| [01_routes.md](01_routes.md) | Cần biết URL nào → Controller nào, middleware gì |
| [02_controllers.md](02_controllers.md) | Cần biết Controller dùng Model nào, render View gì |
| [03_models.md](03_models.md) | Cần biết Model → Table, relationships |
| [04_vue_pages.md](04_vue_pages.md) | Cần biết Vue page nhận Props gì, gọi API nào |
| [05_feature_graph.md](05_feature_graph.md) | Cần trace feature slug → toàn bộ execution chain |
| [06_middleware.md](06_middleware.md) | Cần hiểu chuỗi middleware của từng portal |

## Quick Reference

### Portal Entry Points
| URL | Auth Gate | Controller |
|-----|-----------|------------|
| `/portal` | `CheckPortalAccess:activities` | `DepartmentPortalController` |
| `/ministry` | `EnsureMinistryContext` | `MinistryPortalController` |
| `/finance-portal` | `EnsureFinanceContext` | `Portal/FinancePortalController` |
| `/deacon` | `EnsureDeaconContext` | `Portal/DeaconPortalController` |
| `/member` | `auth` | `MemberPortalController` |
| `/admin/*` | `EnsureSuperAdmin` | Various |

### Key Service Files
```
PortalService.php            — canAccess(user, deptId, slug)   → bool (view)
                               canManage(user, deptId, slug)   → bool (create/edit/delete)
                               getAllowedFeaturesForDept(user, deptId) → string[]
FeatureAssignmentService.php — isFeatureEnabled(dept, slug)    → bool (Level 1)
                               getAvailableFeatures(dept)      → array (DEFAULT DENY)
```

### Permission System — MAC V2 (Unified, Single-Source)
```
SuperAdmin → bypass all (is_superadmin = true)
    ↓
Level 1: feature_departments  (block config — FeatureAssignmentService)
    DEFAULT DENY: feature chưa config → không ai dùng được
    ↓
Level 2: user_department_features  (user override — PortalService)
    is_enabled = false                         → DENY
    is_enabled = true + access_level = 'view'  → canAccess() = true
    is_enabled = true + access_level = 'manage'→ canManage() = true
    no record                                  → inherit Level 1
    ↓
Controller:
    $this->authorizeFeature('slug')   → xem
    $this->authorizeManage('slug')    → tạo/sửa/xóa
```

### Fresh Deployment (Server Mới / DB Trống)
```bash
# 1. Setup
cp .env.example .env && php artisan key:generate

# 2. Migrate + seed foundation
php artisan migrate --seed
# Tạo: Church, Roles, 17 Features, 20 Departments,
#       OrgRoles, Level 1 MAC config, SuperAdmin account

# 3. Optional: tài khoản đại diện các ban
php artisan db:seed --class=OrgStructureSeeder

# Login: superadmin@{SYSTEM_DOMAIN} / Abc.1234
```

### .env Keys
```
SYSTEM_DOMAIN=httlthanhmyloi.com
CHURCH_NAME="Hội Thánh Tin Lành"
CHURCH_EMAIL=contact@httlthanhmyloi.com
CHURCH_ADDRESS="..."
CHURCH_PHONE=0123456789
SUPERADMIN_PASSWORD=Abc.1234
```

### Stats
- **Routes parsed:** 290
- **Controllers indexed:** 51
- **Models indexed:** 66
- **Vue pages indexed:** 106
