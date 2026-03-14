# Extended Documentation Hub Implementation Plan

> **For agentic workers:** REQUIRED: Use superpowers:subagent-driven-development (if subagents available) or superpowers:executing-plans to implement this plan. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Expand the newly created Documentation Hub to cover system Overview (MAC), Member Management, Department Portals (Internal features), Meetings & Calendar, and Finance processing. This meets the user's requirement to meticulously document all modules of the web app.

**Architecture:** We will reuse the `DocsLayout.vue` structure. We will add new views and routes where necessary, and expand the placeholder files (`Overview.vue`, `Members.vue`, `Finance.vue`) with detailed, structured Vue markup (`v-html`, `prose`, `alert` boxes).

**Tech Stack:** Laravel, Vue 3, Inertia.js, Tailwind CSS

---

### Task 1: Scaffolding New Routes & Layout Links

**Files:**
- Modify: `routes/web.php`
- Modify: `app/Http/Controllers/DocsController.php`
- Modify: `resources/js/Layouts/DocsLayout.vue`

- [ ] **Step 1: Add new Docs routes**
Add `help.departments` and `help.meetings` to the `DocsController` routing group in `web.php` and the controller methods.

- [ ] **Step 2: Update Sidebar Navigation**
Add "Cổng Ban ngành" (`help.departments`) and "Sự kiện & Buổi nhóm" (`help.meetings`) to `DocsLayout.vue`'s `navLinks` array. Reorder them logically.

### Task 2: Write "Tổng quan & Phân quyền" (Overview.vue)

**Files:**
- Modify: `resources/js/Pages/Docs/Overview.vue`

- [ ] **Step 1: Document MAC Architecture**
Write the user guide explaining the Matrix Access Control (Tier 1: Department visibility, Tier 2: Feature toggles) visually using grid layouts or bullet points. Explain the 3 major Portals (Leadership, Ministry, Department).

### Task 3: Write "Quản lý Nhân sự" (Members.vue)

**Files:**
- Modify: `resources/js/Pages/Docs/Members.vue`

- [ ] **Step 1: Document Member Management**
Detail how to add/edit believers, assign basic roles, and importantly, managing Household relations (Chủ hộ - Thành viên). Explain the transition for newly created accounts (Default password).

### Task 4: Write "Sinh hoạt Ban ngành" (Departments.vue)

**Files:**
- Create: `resources/js/Pages/Docs/Departments.vue`

- [ ] **Step 1: Document Portal features**
Explain how the Department Portal works for `tb.thanhtrang` / `tk.thanhtrang`. Detail the sub-features:
  - Điểm danh (Attendance)
  - Hoạt động thăm viếng (Visitations)
  - Hành trình Đức tin (Faith Journeys)
  - Gửi báo cáo định kỳ (Periodic Reports)

### Task 5: Write "Sự kiện & Buổi nhóm" (Meetings.vue)

**Files:**
- Create: `resources/js/Pages/Docs/Meetings.vue`

- [ ] **Step 1: Document Calendar & Meetings**
Explain the Church Calendar (`/calendar` vs `/portal/sessions`). How to create basic recurrent events, and the linkage to the Duty Roster feature.

### Task 6: Write "Quản lý Tài chính" (Finance.vue)

**Files:**
- Modify: `resources/js/Pages/Docs/Finance.vue`

- [ ] **Step 1: Document Finance Management**
Explain how the Finance Portal handles Funds, Income Receipts (Phiếu thu), and Expense Vouchers (Phiếu chi), and how it generates the visual charts/reports.
