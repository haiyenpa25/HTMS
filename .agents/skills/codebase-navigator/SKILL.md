---
name: codebase-navigator
description: |
  Điều hướng HTMS codebase hiệu quả dùng Knowledge Graph Index.
  Dùng khi cần tìm file liên quan đến một tính năng/lỗi, trước khi đọc code thực sự.
  Tiết kiệm 60-80% token so với grep/scan toàn bộ codebase.
---

# SKILL: HTMS Codebase Navigator

## Khi Nào Dùng Skill Này

Dùng TRƯỚC KHI đọc bất kỳ file code nào khi:
- Cần tìm file xử lý một tính năng cụ thể (attendance, finance, permissions...)
- Cần rà soát lỗi và chưa biết lỗi ở file nào
- Cần hiểu flow của một request từ route → middleware → controller → vue
- Cần biết file nào bị ảnh hưởng khi sửa một file khác

## Quy Trình (Bắt Buộc Theo Thứ Tự)

### Bước 1 — Đọc Knowledge Graph Index

Luôn đọc file này ĐẦU TIÊN (khoảng 3k token, thay vì scan 500+ files):

```
d:\Xampp\htdocs\cms\docs\KNOWLEDGE_GRAPH.md
```

Từ file này bạn có thể tìm ngay:
- **Theo domain**: Tìm section `ATTENDANCE`, `FINANCE`, `PERMISSIONS`... để biết file nào liên quan
- **Theo layer**: Tìm section `Middleware`, `Service`, `Model`... để biết kiến trúc
- **Theo MAC V2**: Xem section "Files cốt lõi MAC V2" cho các vấn đề phân quyền

### Bước 2 — Kiểm Tra Enrichment Data (nếu cần)

Nếu cần thêm chi tiết (domain, keywords, summary), đọc:
```
d:\Xampp\htdocs\cms\vibecode_enrichment.json
```

Tìm theo key là đường dẫn file. Ví dụ:
```json
"app/Http/Controllers/Portal/AttendanceController.php": {
  "layer": "PortalController",
  "domain": "attendance",
  "summary_vi": "Controller điểm danh sinh hoạt",
  "keywords": ["attendance", "meeting", "diem_danh"]
}
```

### Bước 3 — Đọc Architecture Reference

Nếu cần hiểu routing/middleware chain, đọc:
```
d:\Xampp\htdocs\cms\kien_truc_he_thong.md
```

### Bước 4 — Đọc File Cụ Thể

Chỉ SAU KHI đã xác định đúng file từ bước 1-3, mới đọc file đó.
Ưu tiên đọc có chọn lọc (StartLine/EndLine) thay vì đọc cả file.

## Map Nhanh: Intent → Domain → Files

| Bạn muốn làm gì | Domain | Files thường liên quan |
|-----------------|--------|------------------------|
| Lỗi điểm danh | attendance | `Portal/AttendanceController`, `Meeting`, `MeetingAttendance` |
| Lỗi phân quyền | permissions | `PortalService`, `FeatureAssignmentService`, `Middleware/` |
| Lỗi thành viên | members | `PortalMemberController`, `Member`, `OrgMembership` |
| Lỗi tài chính | finance | `DeptFinanceController`, `DepartmentFund`, `DepartmentTransaction` |
| Lỗi phân công | assignments | `DutyRosterController`, `DutyAssignment` |
| Lỗi Vue page không load | portal | `Pages/Portal/`, `PortalLayout.vue`, `HandleInertiaRequests` |
| Lỗi login/auth | auth | `Auth/AuthController`, `Middleware/CheckPortalAccess` |
| Lỗi sidebar không hiện | permissions | `PortalLayout.vue` (visibleNavItems), `HandleInertiaRequests` |
| Thêm tính năng mới | admin | `Admin/SystemFeatureController`, `FeatureSeeder`, `feature_departments` |
| Lỗi sổ tay / chronicles | chronicles | `ChronicleController`, `ChronicleEntry` |
| Lỗi tài liệu | documents | `DocumentController`, `Document` |
| Lỗi chăm sóc | care | `CareController`, `CareRequest`, `CareLog` |

## MAC V2 Quick Reference

```
Request → CheckPortalAccess (block-level) → PortalAccessMiddleware (feature-level)
       → PortalService::canAccess(user, dept, feature)
           ├─ isSuperAdmin()? → pass
           ├─ Level 1: FeatureAssignmentService::isFeatureEnabled(dept, feature)
           └─ Level 2: UserDepartmentFeature WHERE user+dept+feature
               ├─ No record → inherit Level 1
               ├─ is_enabled=true → ALLOW
               └─ is_enabled=false → DENY
```

**5 Files Cốt Lõi MAC V2:**
1. `app/Http/Middleware/CheckPortalAccess.php`
2. `app/Http/Middleware/PortalAccessMiddleware.php`
3. `app/Services/PortalService.php`
4. `app/Services/FeatureAssignmentService.php`
5. `app/Models/UserDepartmentFeature.php`

## Ví Dụ Sử Dụng

### Ví dụ 1: Rà soát lỗi "Tính năng điểm danh không hiển thị"

```
1. Đọc KNOWLEDGE_GRAPH.md → tìm section ATTENDANCE
2. Xác định: AttendanceController, Meeting, Portal/Attendance/*.vue
3. Tìm section PERMISSIONS → xác định: PortalService, FeatureAssignmentService
4. Đọc PortalService::canAccess() (chỉ phần liên quan, ~50 lines)
5. Đọc HandleInertiaRequests (phần share allowed_features)
→ Tổng: ~5 file đọc có chọn lọc = ~500 lines = ~2k token
   So với scan cả codebase = ~50k+ token
```

### Ví dụ 2: Thêm tính năng mới "Báo cáo tổng hợp"

```
1. Đọc KNOWLEDGE_GRAPH.md → section ADMIN + section REPORTS
2. Xem kien_truc_he_thong.md → Feature mapping table
3. Files cần sửa: FeatureSeeder (thêm feature), web.php (route mới),
   Admin/SystemFeatureController (nếu cần), Vue page mới
→ Plan rõ ràng trước khi viết 1 dòng code
```

## Ghi Chú

- Chạy `python -m vibecode enrich` để cập nhật enrichment data sau khi thêm file mới
- Chạy `python -m vibecode scan` + `python -m vibecode ui` để cập nhật graph
- File `vibecode_enrichment.json` tự động được refresh mỗi khi chạy enrich
- `docs/KNOWLEDGE_GRAPH.md` là file chính để agent đọc — luôn ưu tiên file này
