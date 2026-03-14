# Cải Tiến Member Portal - Implementation Plan

> **For agentic workers:** REQUIRED: Use superpowers:subagent-driven-development (if subagents available) or superpowers:executing-plans to implement this plan. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Xây dựng Lịch Custom, Thẻ Hồ Sơ Bản Đồ và Module Truyền Thông Thông Báo vào Portal Tín Hữu. Giao diện sang trọng, mượt mà chuẩn PWA.

**Architecture:** Sử dụng Vue 3 Custom Logic cho Lịch, cài đặt `leaflet` cho Bản đồ và `@vueup/vue-quill` cho Rich Text Editor. Model `Announcement` có sẵn sẽ được tận dụng.

**Tech Stack:** Laravel, Vue 3, Inertia, TailwindCSS, Leaflet.

---

### Task 1: Cài đặt thư viện (npm libraries)

**Files:**
- Modify: `package.json`

- [ ] **Step 1: Install Dependencies**

```bash
npm install leaflet @vue-leaflet/vue-leaflet @vueup/vue-quill@latest
```

- [ ] **Step 2: Commit**

```bash
git add package.json package-lock.json
git commit -m "build: cài đặt leaflet và vue-quill cho bản đồ và soạn thảo văn bản"
```

### Task 2: Xây dựng Module Create News (Truyền Thông -> Tạo Thông Báo)

**Files:**
- Create: `app/Http/Controllers/Communication/AnnouncementController.php`
- Modify: `routes/web.php:xxx-xxx`
- Create: `resources/js/Pages/Communication/Announcements/Index.vue`
- Create: `resources/js/Pages/Communication/Announcements/Create.vue`

- [ ] **Step 1: Tạo AnnouncementController.php**
Write `AnnouncementController` providing indexing and storing of Announcements.

- [ ] **Step 2: Khai báo Route trong web.php**
Add routes for `communication/announcements` under `auth`.

- [ ] **Step 3: Tạo Frontend Pages (Index & Create)**
Build the views to list announcements and generate Rich Text (Quill Editor) announcements to push to Member Portal. Save to database.

- [ ] **Step 4: Commit**
```bash
git add app/ routes/ resources/js/Pages/Communication
git commit -m "feat: bổ sung tính năng quản trị tin tức nội bộ (announcements)"
```

### Task 3: Cập nhật Fetch API cho Member Portal

**Files:**
- Modify: `app/Http/Controllers/MemberPortalController.php`

- [ ] **Step 1: Truy xuất Tin tức (Announcements)**
Cập nhật biến `$notifications` trong `MemberPortalController@index` để lấy danh sách từ Model `Announcement` thay vì bảng notifications cũ chưa có dữ liệu thật.

- [ ] **Step 2: Commit**
```bash
git add app/Http/Controllers/MemberPortalController.php
git commit -m "feat: fetch bài báo từ Announcement qua Portal"
```

### Task 4: Làm mới Giao Diện Member Portal (Calendar + Map + News)

**Files:**
- Modify: `resources/js/Pages/MemberPortal/Index.vue`
- Modify: `resources/css/app.css` (nếu cần import Quill/Leaflet css)

- [ ] **Step 1: Tích hợp Vue Leaflet và Vue Quill StyleSheet**
Đưa các import stylesheet vào component hoặc `app.css`.

- [ ] **Step 2: Xây dựng Custom Calendar**
Thay đổi logic từ WeekDays tĩnh hiện tại sang Custom Calendar Component có thể xem theo Tuần và Tháng. Call AJAX API tới `/api/calendar/events`.

- [ ] **Step 3: Hiển thị Bản đồ thông tin cá nhân**
Cắt lại giao diện Thẻ Personal Info Card ở right-sidebar, tích hợp bản đồ Leaflet hiển thị `latitude` vs `longitude`.

- [ ] **Step 4: Hiển thị danh sách Tin Tức**
Sử dụng dữ liệu `$notifications` (bay giờ chứa announcements) để render trong khung Thông báo Nhắc việc mới.

- [ ] **Step 5: Load Dev server & Check**

Run `npm run build` or `npm run dev`. Expected: Giao diện chạy mượt tru, Lịch hiển thị sự kiện tuần/tháng, có Bản đồ map, có khung Tin tức.

- [ ] **Step 6: Commit**
```bash
git add resources/
git commit -m "ui: hoàn thiện thiết kế member portal mới với map và custom calendar"
```

### Task Final: User Guide & Documentation (MANDATORY)

- [ ] **Step 1: Write User Guide**
Viết/hoặc cập nhật tài liệu hướng dẫn sử dụng cho tính năng vừa làm xong (trong `walkthrough.md`). Phải hướng dẫn chi tiết luồng sử dụng của người dùng cách chuyển Lịch, xem Bản đồ, và Đăng Tin tức.
- [ ] **Step 2: Capture & Embed Images**
Chụp màn hình giao diện (screenshots) luồng tạo Announcement và luồng giao diện cập nhật của Portal Tín Hữu, nhúng vào tài liệu.
