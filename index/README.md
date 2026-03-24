# HTMS Code Knowledge Graph
> Generated: 2026-03-24 03:46 | Run `php artisan htms:index` to refresh

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
PortalService.php            — canAccess(), getAllowedFeaturesForDept()
FeatureAssignmentService.php — isFeatureEnabled(), getAvailableFeatures()
```

### Stats
- **Routes parsed:** 290
- **Controllers indexed:** 51
- **Models indexed:** 66
- **Vue pages indexed:** 106
