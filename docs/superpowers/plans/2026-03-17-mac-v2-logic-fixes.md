# MAC V2 Permission Logic Fixes Implementation Plan

> **For agentic workers:** REQUIRED: Use superpowers:subagent-driven-development (if subagents available) or superpowers:executing-plans to implement this plan. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Khắc phục 3 lỗi logic phân quyền MAC V2: thiếu mục Toàn Hệ Thống ở Kho Tính Năng, ẩn tính năng khác block ở giao diện phân quyền cá nhân, và thiết lập thiếu widget Sổ Tay HT trên dashboard Cổng Sinh Hoạt.

**Architecture:** Bổ sung loại `portal_type` = `global` trong database. Sửa trực tiếp frontend Vue (Users/Index.vue và Portal/Dashboard.vue) để thêm metadata và thay đổi logic filter linh hoạt hơn.

**Tech Stack:** Laravel, Vue 3, Tailwind CSS.

---

## Chunk 1: Database & Backend Logic

### Task 1: Update portal_type for Global Features

**Files:**
- Modify: Database `features` table (via tinker)

- [ ] **Step 1: Write a DB update script (Tinker)**
Dùng `run_command` gọi:
```bash
php artisan tinker --execute="App\Models\Feature::whereIn('slug', ['members', 'thanh-vien', 'chronicles'])->update(['portal_type' => 'global']); \Illuminate\Support\Facades\Cache::forget('system_features_model');"
```

- [ ] **Step 2: Verify the DB update**
Run:
```bash
php artisan tinker --execute="echo json_encode(App\Models\Feature::where('portal_type', 'global')->pluck('slug'));"
```
Expected: `["members","chronicles"]`

## Chunk 2: Frontend "Users Management & Permissions Matrix"

### Task 2: Add 'global' to `portalMeta` and Fix Matrix Filter

**Files:**
- Modify: `resources/js/Pages/Users/Index.vue`

- [ ] **Step 1: Add `global` to `portalMeta()` function**
Modify line ~94:
```javascript
const portalMeta = (type) => ({
  activities: { name: 'Sinh Hoạt', icon: '🎯', color: 'emerald' },
  ministry:   { name: 'Mục Vụ',   icon: '⛪', color: 'blue' },
  deacon:     { name: 'Chấp Sự',  icon: '🛡', color: 'amber' },
  global:     { name: 'Toàn Hệ Thống', icon: '🌐', color: 'purple' },
})[type] || { name: type, icon: '📦', color: 'indigo' };
```

- [ ] **Step 2: Add `purple` to `cardBorder` and `textAccent`**
Modify around line ~100:
```javascript
const cardBorder = (color) => ({
  emerald: 'border-emerald-200 hover:border-emerald-400',
  blue:    'border-blue-200 hover:border-blue-400',
  amber:   'border-amber-200 hover:border-amber-400',
  indigo:  'border-indigo-200 hover:border-indigo-400',
  purple:  'border-purple-200 hover:border-purple-400',
})[color] || 'border-gray-200 hover:border-gray-300';

const textAccent = (color) => ({
  emerald: 'text-emerald-600',
  blue:    'text-blue-600',
  amber:   'text-amber-600',
  indigo:  'text-indigo-600',
  purple:  'text-purple-600',
})[color] || 'text-gray-600';
```

- [ ] **Step 3: Fix `permDeptFeatures` filter**
Modify line ~133 to include global features:
```javascript
const permDeptFeatures = computed(() =>
  (props.features || []).filter(f => f.portal_type === permSelectedBlock.value || f.portal_type === 'global')
);
```

## Chunk 3: Frontend "Portal Dashboard"

### Task 3: Add Chronicles to Portal Dashboard

**Files:**
- Modify: `resources/js/Pages/Portal/Dashboard.vue`

- [ ] **Step 1: Add `chronicles` feature card**
Inject into `allFeatureCards` array (lines 22-80):
```javascript
    {
        key: 'chronicles',
        label: 'Sổ Tay HT',
        sub: 'Biên Niên Sử',
        route: 'ministry.chronicles.index',
        color: 'purple',
        icon: `<path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25"/>`,
    },
```

### Task Final: User Guide & Documentation (MANDATORY)

- [ ] **Step 1: Write User Guide**
Update `walkthrough.md`.
- [ ] **Step 2: Capture & Embed Images**
Take screenshots of the new "Kho Tính Năng Hệ Thống" with the Global section, and the Portal Dashboard with the Chronicles widget.
