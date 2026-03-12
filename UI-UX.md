# QUY CHUẨN THIẾT KẾ GIAO DIỆN CMS HỘI THÁNH
## UI/UX Design System & Rules Guide

> **Phiên bản:** 2.0 | **Cập nhật:** 2026-03-10  
> Tài liệu này là nguồn chân lý duy nhất (Single Source of Truth) về thiết kế giao diện toàn hệ thống.

---

## 1. TRIẾT LÝ THIẾT KẾ

- **Premium SaaS Feel** – Mọi trang đều phải tạo cảm giác như sản phẩm hàng đầu (Linear, Vercel, Stripe).
- **Micro-interactions** – Hover, Focus, Transition mượt mà trên mọi button/card/row.
- **Mobile First** – Mọi layout phải hoạt động hoàn hảo trên điện thoại 375px trước, rồi mở rộng lên desktop.
- **Consistent Design Language** – Một trang mới bất kỳ phải nhất quán 100% với các trang đã có.

---

## 2. BẢNG MÀU (COLOR SYSTEM)

| Màu sắc | Class Tailwind | Dùng cho |
|---|---|---|
| **Primary** | `indigo-600` → `indigo-800` | Buttons chính, Navigation active, Header trang |
| **Success** | `emerald-500` → `emerald-700` | Tài chính, Thành công, Hoàn thành |
| **Warning** | `amber-500`, `orange-500` | Cảnh báo, Chờ xử lý, Nháp |
| **Danger** | `red-500`, `rose-600` | Lỗi, Xóa, Từ chối |
| **Neutral BG** | `gray-50` (`#f9fafb`) | Nền tổng trang |
| **Card BG** | `white`, `border-gray-100` | Thẻ nội dung |
| **Text Primary** | `gray-900`, `gray-800` | Tiêu đề, dữ liệu quan trọng |
| **Text Secondary** | `gray-500`, `gray-400` | Mô tả, text nhỏ |

---

## 3. TYPOGRAPHY (CHỮ VIẾT)

```css
/* Font Family */
font-family: 'Inter', 'Roboto', system-ui, sans-serif;

/* Tiêu đề trang (H1) */
.page-title { @apply text-xl font-black text-gray-900 tracking-tight; }

/* Tiêu đề section */
.section-title { @apply text-sm font-black text-gray-700 uppercase tracking-widest; }

/* Phụ đề / Mô tả */
.subtitle { @apply text-sm text-gray-500 font-medium; }

/* Badge/Label */
.badge { @apply text-[10px] font-bold uppercase tracking-wider; }
```

---

## 4. COMPONENTS CHUẨN

### A. PAGE HEADER (Bắt buộc trên mọi trang)
```html
<!-- Hero Banner với Gradient -->
<div class="rounded-2xl bg-gradient-to-br from-indigo-600 to-indigo-800 p-6 sm:p-8 text-white relative overflow-hidden shadow-lg mb-6">
  <div class="absolute inset-0 opacity-10 pointer-events-none">
    <!-- Icon SVG to background decoration -->
  </div>
  <div class="relative z-10">
    <p class="text-xs font-bold uppercase tracking-[0.2em] text-indigo-200 mb-2">MÔ-ĐUN × CHỨ NĂNG</p>
    <h1 class="text-2xl sm:text-3xl font-black tracking-tight">Tiêu đề Trang</h1>
    <p class="mt-2 text-sm text-indigo-200">Mô tả ngắn gọn tính năng này.</p>
  </div>
  <!-- Action Button (optional) -->
  <div class="absolute top-4 right-4 sm:top-6 sm:right-6 z-10">
    <button class="...">Action</button>
  </div>
</div>
```

### B. STATS CARDS (KPI Row)
```html
<div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
  <div class="bg-white rounded-2xl p-4 sm:p-5 border border-gray-100 shadow-sm">
    <p class="text-xs font-bold text-gray-500 uppercase tracking-widest">Nhãn</p>
    <p class="text-2xl sm:text-3xl font-black text-gray-900 mt-1">999</p>
    <p class="text-xs text-gray-400 font-medium mt-1">Mô tả thêm</p>
  </div>
</div>
```

### C. TOOLTIP / HELPER ICON (Bắt buộc cho các tính năng phức tạp)
Dùng để giải thích tính năng khi người dùng hover/nhấp vào nút `(i)`.
```html
<div class="flex items-center gap-2">
  <h2 class="text-xl font-black text-gray-900">Tên Tính Năng</h2>
  <div class="relative group cursor-help">
    <!-- Icon (i) -->
    <svg class="w-5 h-5 text-gray-400 group-hover:text-indigo-500 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
    </svg>
    <!-- Tooltip Box -->
    <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 w-64 p-3 bg-gray-900 text-white text-xs font-medium rounded-xl opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all z-20 shadow-xl pointer-events-none">
      Giải thích chi tiết về tính năng này ở đây...
      <!-- Mũi tên chỉ xuống -->
      <div class="absolute top-full left-1/2 -translate-x-1/2 border-4 border-transparent border-t-gray-900"></div>
    </div>
  </div>
</div>
```

### D. DATA TABLE (Bảng dữ liệu)
```html
<div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
  <!-- Table Header với Actions -->
  <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
    <h3 class="text-sm font-black text-gray-900">Tiêu đề Bảng</h3>
    <input placeholder="Tìm kiếm..." class="..." />
  </div>
  <!-- Always wrap in overflow-x-auto -->
  <div class="overflow-x-auto">
    <table class="min-w-full">
      <thead class="bg-gray-50">
        <tr>
          <th class="px-5 py-3 text-left text-[11px] font-black uppercase tracking-wider text-gray-500">Cột</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-100">
        <tr class="hover:bg-indigo-50/30 transition-colors">
          <td class="px-5 py-4 text-sm">Dữ liệu</td>
        </tr>
      </tbody>
    </table>
  </div>
</div>
```

### E. CARD DANH SÁCH (Item Cards)
```html
<!-- Card item với hover effect (dùng thay cho Table trên Mobile) -->
<div class="bg-white rounded-2xl p-4 sm:p-5 border border-gray-100 shadow-sm hover:shadow-md hover:border-indigo-200 transition-all group cursor-pointer">
  <div class="flex items-start justify-between">
    <!-- Icon + Title -->
    <div class="flex items-start space-x-4">
      <div class="w-11 h-11 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center shrink-0 group-hover:bg-indigo-500 group-hover:text-white transition-colors">
        <!-- SVG Icon -->
      </div>
      <div>
        <h3 class="font-bold text-gray-900 group-hover:text-indigo-700 transition-colors">Tiêu đề</h3>
        <p class="text-xs text-gray-500 mt-0.5">Mô tả phụ</p>
      </div>
    </div>
    <!-- Arrow -->
    <div class="w-8 h-8 rounded-full bg-gray-50 flex items-center justify-center group-hover:bg-indigo-100 group-hover:text-indigo-600 transition-colors">
      <svg class="w-4 h-4" ...>...</svg>
    </div>
  </div>
</div>
```

### F. BADGE TRẠNG THÁI
```html
<!-- Dùng rounded-full, text nhỏ font-bold uppercase -->
<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase bg-emerald-100 text-emerald-800">Hoàn thành</span>
<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase bg-amber-100 text-amber-800">Đang chờ</span>
<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase bg-red-100 text-red-800">Từ chối</span>
<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase bg-gray-100 text-gray-700">Không xác định</span>
```

### G. BUTTONS
```html
<!-- Primary -->
<button class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white text-sm font-bold rounded-xl hover:bg-indigo-700 transition-colors shadow-sm active:scale-95">
  + Thêm mới
</button>

<!-- Secondary/Ghost -->
<button class="inline-flex items-center gap-2 px-4 py-2 bg-white text-gray-700 text-sm font-bold rounded-xl border border-gray-200 hover:bg-gray-50 transition-colors">
  Hủy
</button>

<!-- Danger -->
<button class="inline-flex items-center gap-2 px-4 py-2 bg-red-50 text-red-600 text-sm font-bold rounded-xl hover:bg-red-100 transition-colors border border-red-100">
  Xóa
</button>
```

### I. MOBILE FAB – Nút Hành Động Nổi (⭐ Bắt buộc trên Mobile)

> **Quy tắc:** Trên màn hình điện thoại (`sm:hidden`), tất cả các nút "Thêm mới", "Gửi đề xuất", "Tạo mới" **PHẢI** dùng FAB thay cho button thông thường trong toolbar. FAB đặt cố định ở **góc dưới phải** màn hình, nhỏ gọn, có shadow nổi bật.

```html
<!-- ✅ CHUẨN: Mobile FAB - cố định góc dưới phải -->
<button
  @click="openModal"
  class="sm:hidden fixed bottom-20 right-4 z-40
         w-14 h-14 bg-indigo-600 text-white rounded-full shadow-xl
         flex items-center justify-center
         hover:bg-indigo-700 active:scale-95 transition-all
         ring-4 ring-white"
  aria-label="Thêm mới"
>
  <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
  </svg>
</button>

<!-- ✅ Desktop: Nút thông thường trong toolbar, ẩn trên mobile -->
<button @click="openModal" class="hidden sm:inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white text-sm font-bold rounded-xl hover:bg-indigo-700 transition-colors shadow-sm">
  + Thêm mới
</button>
```

**Các biến thể màu theo từng module:**

| Module | Màu FAB | Class |
|---|---|---|
| Tín hữu / Nhân sự | Indigo | `bg-indigo-600` |
| Chăm sóc / Care | Rose | `bg-rose-500` |
| Tài chính | Emerald | `bg-emerald-600` |
| Lịch sự kiện | Teal | `bg-teal-500` |
| Tài liệu | Sky blue | `bg-sky-600` |
| Diễn giả | Violet | `bg-violet-600` |
| Đề xuất / Góp ý | Amber | `bg-amber-500` |

**Vị trí `bottom` điều chỉnh theo MobileLayout:**
- Nếu có bottom nav bar → dùng `bottom-20` (tránh đè lên nav)
- Nếu không có bottom nav → dùng `bottom-6`

**Animation nhẹ khi xuất hiện (tùy chọn):**
```html
<button class="sm:hidden fixed bottom-20 right-4 z-40 w-14 h-14 ...
               animate-bounce-once
               hover:scale-110 active:scale-90 transition-transform duration-150">
```


### G. FORM FIELDS (Step Form theo MeetingForm pattern)
```html
<!-- 3-Step Form (Tiêu chuẩn) -->
<!-- Step 1: Loại hình/Phân loại -->
<!-- Step 2: Thông tin chính -->
<!-- Step 3: Chi tiết bổ sung -->

<!-- Label chuẩn -->
<label class="text-sm font-black text-gray-900">Tên trường <span class="text-red-500">*</span></label>
<!-- Input chuẩn -->
<input class="w-full border-gray-200 rounded-xl bg-gray-50 px-4 py-3 text-sm focus:ring-indigo-500 focus:border-indigo-500 transition" />
<!-- Error -->
<p class="text-xs text-red-500 font-medium mt-1">Thông báo lỗi</p>
```

### H. EMPTY STATE
```html
<div class="bg-white rounded-2xl border border-dashed border-gray-200 p-10 flex flex-col items-center justify-center text-center">
  <div class="w-16 h-16 rounded-full bg-gray-50 flex items-center justify-center mb-4">
    <svg class="w-8 h-8 text-gray-400" ...></svg>
  </div>
  <h3 class="font-bold text-gray-700">Chưa có dữ liệu</h3>
  <p class="text-sm text-gray-400 mt-1 max-w-xs">Mô tả khi trống.</p>
</div>
```

---

## 5. LAYOUT CHUẨN (PAGE STRUCTURE)

```html
<AuthenticatedLayout>
  <template #header>Tên Trang</template>
  <div class="py-6 space-y-6">
    <!-- 1. Hero Banner -->
    <!-- 2. KPI Stats Row (nếu có) -->
    <!-- 3. Filter Bar (nếu có) -->
    <!-- 4. Main Content (Table/Cards/Grid) -->
    <!-- 5. Pagination (nếu có) -->
  </div>
</AuthenticatedLayout>
```

---

## 6. RESPONSIVE RULES (QUAN TRỌNG)

| Màn hình | Behaviour |
|---|---|
| Mobile (`< 640px`) | 1 cột, card/list view, hidden columns `hidden sm:table-cell` |
| Tablet (`640px - 1024px`) | 2 cột grid, thu gọn sidebar |
| Desktop (`> 1024px`) | Full layout, sidebar mở rộng, table đầy đủ cột |

- **Tables PHẢI** dùng `<div class="overflow-x-auto">` làm wrapper
- **Cột thứ yếu** trong table dùng `hidden sm:table-cell` để ẩn trên mobile
- **Grid** luôn bắt đầu `grid-cols-1 sm:grid-cols-2 lg:grid-cols-3`
- **Padding** dùng `px-4 sm:px-6 lg:px-8` pattern

---

## 7. ANIMATION & MICRO-INTERACTIONS

```css
/* Entry animations (dùng class Tailwind) */
.animate-in { ... }
.fade-in { ... }
.slide-in-from-bottom-4 { ... }

/* Hover transitions mặc định */
transition-colors duration-200
hover:shadow-md
hover:scale-[1.02] /* chỉ cho cards, không dùng cho text */

/* Loading state */
.animate-spin /* cho spinner icon */
.animate-pulse /* cho skeleton loading */
```

---

## 8. HỆ THỐNG PORTAL (ROLE-BASED PORTAL ROUTING)

Sau khi đăng nhập, hệ thống sẽ tự động điều hướng người dùng tới Portal riêng phù hợp nhất với vai trò và ban ngành của họ (để tránh hiển thị những tính năng thừa thãi không cần thiết):

| Portal | URL | Dành cho | Màu chủ đạo |
|---|---|---|---|
| **Trang cho Mục sư (Dashboard)** | `/dashboard` | Mục sư, Super Admin | Blue (`from-blue-700 to-blue-900`) |
| **Portal Ban ngành Sinh hoạt** | `/portal` | Ban Sinh Hoạt | Emerald (`from-emerald-500`) |
| **Portal Ban ngành Mục vụ** | `/ministry` | Ban Mục Vụ | Indigo (`from-indigo-600`) |
| **Portal Ban ngành Chấp sự** | `/deacon` | Ban Chấp Sự (Deacon) | Amber (`from-amber-500`) |
| **Portal Tín hữu** | `/member` | Tín hữu bình thường | Orange (`from-orange-500 to-orange-700`) |

> **Quy tắc hiển thị:** Đăng nhập xong -> Người dùng tới thẳng trang riêng của họ với những tính năng mà họ được phép. Tín hữu sẽ có một trang cực kỳ tối giản (chỉ chứa thông báo, lịch tuần, gửi yêu cầu).

---

## 9. QUY CHUẨN PORTAL LAYOUT (MARGIN & PADDING)

**Vấn đề:** Các trang Portal (portal/ministry/deacon) từng bị lỗi chiều ngang quá lớn ("bành bành kỳ quá") trên Desktop và bị chạm sát mép màn hình trên Mobile khi thiếu class constainer.

**✅ GIẢI PHÁP CHUẨN:**
Tất cả các layout (như `PortalLayout.vue` hoặc `AuthenticatedLayout.vue`) **PHẢI** luôn có một wrapper quy định độ rộng tối đa và padding an toàn hai bên:

```html
<!-- Cấu trúc wrapper chuẩn BẮT BUỘC trong Layout -->
<main class="flex-1 overflow-x-hidden overflow-y-auto w-full relative pb-safe">
    <!-- KHÔNG ĐƯỢC THIẾU DÒNG NÀY: -->
    <div class="max-w-7xl mx-auto w-full px-4 sm:px-6 lg:px-8">
        <slot />
    </div>
</main>
```

Bên trong các Vue Component của từng trang (`Index.vue`), **KHÔNG CẦN** tự định nghĩa lại max-w, chỉ cần padding dọc:
```html
<!-- Cấu trúc chuẩn trong các trang Portal / Dashboard -->
<div class="py-6 space-y-6 w-full">
    <!-- Nội dung (Feature Cards, Bảng thống kê) -->
</div>
```

**Lưới (Grid) của Feature Cards chuẩn:**
- Mobile: `grid-cols-2`
- Tablet: `sm:grid-cols-3`
- Desktop: `lg:grid-cols-4` (hoặc 3 tuỳ số lượng tính năng)

---

## 10. PORTAL TÍN HỮU (TỐI GIẢN)

Tính năng hiển thị (Giao diện giống kiểu app xịn):
- Hồ sơ Thành viên
- Lịch sinh hoạt HT (Current week strip)
- Gửi yêu cầu chăm sóc / Nhu cầu cầu nguyện
- Lời chào cá nhân hoá / Câu gốc kinh thánh tự động thay đổi

**KHÔNG hiển thị:** Quản lý tín hữu khác, Tài chính, Phân công nội bộ, Tính năng quản trị.
