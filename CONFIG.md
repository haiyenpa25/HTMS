# CONFIG — Tài Liệu Hệ Thống CMS HT Thanh Mỹ Lợi

> **Hướng dẫn sử dụng:** Khi bắt đầu một yêu cầu mới, hãy đọc file này và nói:
> `"Đọc CONFIG.md và thực hiện [mô tả yêu cầu]. Áp dụng đúng thiết kế, màu sắc, và pattern đã định nghĩa trong CONFIG cho portal [tên portal]."`

---

## 1. THÔNG TIN CHUNG

- **Framework:** Laravel 11 + Inertia.js + Vue 3
- **CSS:** TailwindCSS
- **Database:** MySQL (production), SQLite (local dev)
- **Build tool:** Vite
- **Server:** `~/public_html` | SSH user: `quanl3363@conasihosting...`
- **URL Production:** https://quanly.httlthanhmyloi.com
- **URL Local:** http://127.0.0.1:8000

---

## 2. QUY TẮC DEPLOY

### 🪟 Windows (Local → GitHub)
> ❌ Không dùng `&&` để ghép lệnh trên PowerShell. Chạy từng lệnh riêng lẻ.

```powershell
# Bước 1: Stage thay đổi
git add -A

# Bước 2: Commit
git commit -m "feat: mô tả tính năng"

# Bước 3: Build Vite (bắt buộc khi có thay đổi Vue/JS/CSS)
npm run build

# Bước 4: Stage và commit bản build
git add public/build
git commit -m "deploy: update vite assets"

# Bước 5: Push
git push origin main
```

### 🐧 Linux (Server — Sau khi push)
```bash
cd ~/public_html

# Pull code mới
git pull origin main

# Nếu có migration mới
php artisan migrate --force

# Clear tất cả cache
php artisan optimize

# Nếu font/giao diện vẫn sai: Xóa cache trình duyệt hoặc mở Incognito
```

---

## 3. HỆ THỐNG PORTAL BAN NGÀNH

### 3.1 Layout Chung
- **File:** `resources/js/Layouts/PortalLayout.vue`
- **Tất cả portal đều dùng chung `PortalLayout`**, phân biệt bằng prop `portalType`.
- Header luôn có màu nền `bg-blue-600`.
- Tab navigation nằm dưới header, scroll ngang trên mobile.

### 3.2 Ba Portal Chính

| Portal | URL | `portalType` | Màu accent | Block DB |
|---|---|---|---|---|
| Ban Ngành Sinh Hoạt | `/portal` | `activities` | Emerald `#10b981` | `activities` |
| Ban Ngành Mục Vụ | `/ministry` | `ministry` | Blue `#3b82f6` | `ministry` |
| Ban Chấp Sự | `/deacon` | `deacon` | Amber `#f59e0b` | `leadership` |

### 3.3 Cấu trúc Pages theo Portal

**Ban Sinh Hoạt** (`resources/js/Pages/Portal/`):
- `Dashboard.vue` → Tab: Bảng điều khiển
- `Attendance/` → Tab: Điểm danh
  - `Index.vue` (danh sách buổi nhóm)
  - `Show.vue` (nhập điểm danh)
- `Visitation/Index.vue` → Tab: Thăm viếng
- `Members/Index.vue` → Tab: Thành viên
- `Reports/Index.vue` → Tab: Báo cáo
- `Finance/Index.vue` → Tab: Tài chính
- `Education/` → CĐGD

**Ban Mục Vụ** (`resources/js/Pages/Ministry/`):
- `Dashboard.vue` → Tab: Bảng điều khiển
- `Visitation/Index.vue` → Tab: Thăm viếng (chỉ Ban Thăm Viếng `BTV`)

**Ban Chấp Sự** (`resources/js/Pages/Deacon/`):
- `Index.vue` → Dashboard, chọn vai trò (Thư ký / Thủ quỹ)
- `Attendance.vue` → Tab: Điểm danh (chỉ Thư ký)
- `AttendanceShow.vue` → Nhập điểm danh buổi nhóm HT
- `Report.vue` → Tab: Báo cáo (YouTube, biểu đồ, AI phân tích)
- `DeaconContent.vue` → Component phụ

### 3.4 Controllers Tương ứng

```
app/Http/Controllers/Portal/
├── AttendanceController.php     ← Portal/Sinh hoạt: Điểm danh
├── DeptReportController.php     ← Portal/Ministry: Báo cáo ban ngành
├── DeptFinanceController.php    ← Tài chính ban ngành
├── VisitationController.php     ← Sinh hoạt: Thăm viếng
├── ActivitiesVisitationController.php ← Mục vụ: Thăm viếng
├── DeaconPortalController.php   ← Toàn bộ Ban Chấp Sự
├── PortalMemberController.php   ← Quản lý thành viên
└── EducationController.php      ← CĐGD
```

---

## 4. THIẾT KẾ UI/UX — NGUYÊN TẮC ĐỒNG NHẤT

### 4.1 Portal Reference (Activities = Chuẩn vàng)
Ban Sinh Hoạt (`/portal`) là **portal gốc**, các portal khác thiết kế đồng bộ với nó.

### 4.2 Nguyên tắc Card chính
```html
<!-- Card tiêu chuẩn dùng cho danh sách items -->
<div class="bg-white rounded-2xl p-4 sm:p-5 border border-gray-100 shadow-sm 
            hover:shadow-md hover:border-[color]-200 transition-all group cursor-pointer">
```
- Rounded: `rounded-2xl` hoặc `rounded-[1.5rem]`
- Background card: `bg-white`
- Border resting: `border-gray-100`
- Shadow resting: `shadow-sm`
- Shadow hover: `hover:shadow-md`

### 4.3 Màu sắc theo Portal
| Portal | Primary color | Button | Accent icon |
|---|---|---|---|
| Sinh Hoạt | `emerald-600` | `bg-emerald-600 hover:bg-emerald-700` | `text-emerald-500` |
| Mục Vụ | `blue-600` | `bg-blue-600 hover:bg-blue-700` | `text-blue-500` |
| Chấp Sự | `amber-500` | `bg-amber-500 hover:bg-amber-600` | `text-amber-600` |

### 4.4 Form nhập liệu (Điểm danh/Báo cáo)
- Dùng `useForm` của Inertia thay vì `reactive + axios`.
- Input type number lớn: `text-4xl font-black text-[color]-600 border-2 rounded-[2rem] py-6`.
- Nút Submit: **Floating bottom bar** `fixed bottom-0 ... bg-white/80 backdrop-blur-md`.

### 4.5 Biểu đồ
- Dùng **ApexCharts** (`vue3-apexcharts`).
- Area chart cho dữ liệu theo tuần.
- Bar chart cho so sánh 6 tháng.
- KHÔNG dùng Chart.js.

### 4.6 SlideOver
- Component: `@/Components/SlideOver.vue`
- **Dùng `v-model`** để mở/đóng: `<SlideOver v-model="isOpen">`.
- ❌ **Không dùng** `:show="isOpen" @close="isOpen = false"` — đây là bug cũ đã fix.

### 4.7 Typography & Font
- Font: `font-sans` (hệ thống)
- **File Vue phải lưu dạng UTF-8** — nếu mở bằng editor sai encoding sẽ ra ký tự lạ.
- `npm run build` sẽ compile đúng UTF-8 vào file JS.
- ❌ Không dùng `->after('notes')` trong migration khi cột `notes` chưa tồn tại.

---

## 5. CƠ SỞ DỮ LIỆU — CÁC BẢNG CHÍNH

### 5.1 Bảng dùng chung
| Bảng | Mô tả |
|---|---|
| `meetings` | Buổi nhóm (type: `church` / `department`) |
| `meeting_attendance_summaries` | Điểm danh thủ công theo ban |
| `meeting_finances` | Thu/Chi theo buổi nhóm |
| `departments` | Các ban ngành |
| `members` | Tín hữu |
| `department_reports` | Báo cáo tháng ban ngành |
| `visitations` | Thăm viếng |
| `visitation_visitors` | Ai đi thăm (pivot) |

### 5.2 Bảng chuyên biệt
| Bảng | Dành cho |
|---|---|
| `deacon_attendance_records` | Ban Chấp Sự: Điểm danh HT cấp tổng |
| `deacon_monthly_reports` | Ban Chấp Sự: Báo cáo tháng + YouTube stats |
| `deacon_report_incidents` | Ban Chấp Sự: Sự cố trong tháng |
| `department_meetings` | Tài chính ban ngành |
| `department_transactions` | Giao dịch tài chính |
| `department_funds` | Quỹ |
| `edu_sessions` | CĐGD: Buổi học |
| `edu_session_attendances` | CĐGD: Điểm danh lớp học |

### 5.3 Enum quan trọng (phải đúng tuyệt đối)
- `meetings.type`: `['church', 'department']`
- `visitations.reason`: `['ốm đau', 'mới tin Chúa', 'khích lệ', 'khác']`
- `visitations.visitation_type`: `['church', 'department']`
- `departments.block`: `['activities', 'ministry', 'leadership', ...]`

---

## 6. LỖI ĐÃ PHÁT HIỆN VÀ CÁCH SỬA

| # | Lỗi | Nguyên nhân | Cách sửa |
|---|---|---|---|
| 1 | Font chữ tiếng Việt bị lỗi ký tự trên server | `public/build` cũ bị compile sai encoding | Chạy `npm run build` và commit `public/build` |
| 2 | `SlideOver` không đóng được | Dùng `:show` + `@close` thay vì `v-model` | Dùng `v-model="isSwitchOpen"` |
| 3 | Migration lỗi "Column not found: notes" | Dùng `->after('notes')` khi cột không tồn tại | Xóa `->after(...)`, để cột thêm cuối bảng |
| 4 | Lưu YouTube stats xóa trắng dữ liệu báo cáo | `reportSave` overwrite toàn bộ fields kể cả null | Dùng `array_filter` + `array_key_exists` kiểm tra trước khi update |
| 5 | `CHECK constraint failed: reason` khi seed | Giá trị enum không khớp | Dùng đúng enum: `'khích lệ'`, không phải `'Thăm viếng khích lệ'` |

---

## 7. CÁC LỆNH HỮU ÍCH

```bash
# Seed dữ liệu test cho tất cả portal (6 tháng gần nhất)
php artisan portal:seed-test-data

# Seed dữ liệu điểm danh cho Ban Chấp Sự
php artisan seed:deacon:attendance

# Tạo link storage
php artisan storage:link

# Clear toàn bộ cache
php artisan optimize

# Rollback 1 migration
php artisan migrate:rollback

# Rollback migration cụ thể
php artisan migrate:rollback --path=database/migrations/[tên_file].php --force
```

---

## 8. CẤU TRÚC ROUTES PORTAL

```php
// Ban Sinh Hoạt (Activities)
Route::prefix('portal')->name('portal.')->group(...)
  portal.index | portal.attendance.* | portal.visitation.* | portal.members.* | portal.reports.* | portal.finance.*

// Ban Mục Vụ (Ministry)  
Route::prefix('ministry')->name('ministry.')->group(...)
  ministry.index | ministry.members.* | ministry.visitation.* | education.*

// Ban Chấp Sự (Deacon)
Route::prefix('deacon')->name('deacon.')->group(...)
  deacon.index | deacon.attendance | deacon.attendance.show | deacon.attendance.store | deacon.report | deacon.switch-role
```

---

## 9. LƯU Ý KHI THÊM TÍNH NĂNG MỚI

1. **Trước khi làm:** Hỏi "Tính năng này thuộc portal nào?" → Dùng màu sắc và pattern tương ứng.
2. **Màu sắc:** Không random màu — áp dụng bảng màu ở Mục 4.3.
3. **Form submit:** Ưu tiên `useForm` (Inertia) — không dùng `axios` thủ công.
4. **Migration mới:** Không dùng `->after('column')` vì server MySQL có thể chưa có cột đó.
5. **Build:** Mỗi lần sửa file `.vue`, `.js`, `.css` → phải `npm run build` trước khi push.
6. **Seeder:** Luôn kiểm tra enum values khớp với migration trước khi `create()`.
7. **Chart:** Dùng `vue3-apexcharts` — import trong `<script setup>` của từng page.
8. **Thiết kế đồng nhất:** Xem `/portal` (Activities) làm chuẩn → nhân ra cho Ministry và Deacon.
9. **Phân quyền (MAC):** Mọi tính năng mới AI code **PHẢI** nhắc người dùng thêm danh mục vào bảng `features` và cấu hình phân quyền trong Tab Hệ Thống (User Matrix).
10. **Safe Prop Access (Bắt buộc):** Để tránh lỗi "White Screen", **KHÔNG** dùng `$page.props` hoặc `router.page.props` trực tiếp trong template. Luôn dùng `const page = usePage()` và khai báo `computed` để truy cập props một cách an toàn. (VD: `const userPermissions = computed(() => page.props.userPermissions)`).
11. **Smart Redirect:** `AuthController` tự động điều hướng user về đúng portal theo Ban/Role. Không được thay đổi logic này thành redirect cứng về `/portal`.

---

*File này được cập nhật lần cuối: 2026-03-07*
