# Spec: CMS Comprehensive User Guide & Documentation Reorganization

## 1. Goal
The primary objective is to transform the single-page `/huong-dan/cai-dat` into a full-fledged, multi-page Documentation Hub (User Guide) for the CMS. This hub will provide step-by-step instructions, complete with visual structure, for all major modules: Setup, Duty Roster, Finance, Membership, and Calendar.

## 2. Approach: Vue-based Dynamic Documentation Hub
Instead of hardcoding a single page, we will introduce a `DocsLayout.vue` wrapper that provides a left-hand navigation sidebar (Table of Contents) specifically for the documentation section. 

Each documentation topic will be a separate Vue component inside `resources/js/Pages/Docs/`.

### 2.1 Routing Structure (`routes/web.php`)
We will expand the `huong-dan` route group:
```php
Route::group(['prefix' => 'huong-dan', 'as' => 'docs.'], function () {
    Route::get('/', [DocsController::class, 'index'])->name('index'); // Redirects to setup
    Route::get('/cai-dat', [DocsController::class, 'setup'])->name('setup');
    Route::get('/tong-quan', [DocsController::class, 'overview'])->name('overview');
    Route::get('/lich-phan-cong', [DocsController::class, 'dutyRoster'])->name('duty_rooster');
    Route::get('/nhan-su', [DocsController::class, 'members'])->name('members');
    Route::get('/tai-chinh', [DocsController::class, 'finance'])->name('finance');
});
```

### 2.2 Layout (`resources/js/Layouts/DocsLayout.vue`)
- **Left Sidebar**: A list of documentation categories (Cài đặt ban đầu, Tổng quan, Lịch phân công, v.v.).
- **Main Content Area**: The actual content of the selected page.
- **Top Bar**: A simple header with a "Back to Dashboard" button.

### 2.3 Content Outline
1. **Khởi tạo & Cài đặt (Setup)**: (The existing content) How to run migrations, seeds, and default accounts.
2. **Tổng quan hệ thống (Overview)**: Explanation of MAC (Matrix Access Control), Portal architecture (Lãnh đạo, Sinh hoạt, Mục vụ).
3. **Lịch Phân Công (Duty Roster)**: 
   - **Tạo Mẫu (Templates)**: Tại sao dùng template? Cách tạo mẫu cho Hội thánh vs Ban ngành.
   - **Thêm vị trí**: Chương trình lễ (Section I), Ban Hỗ trợ (Section II).
   - **Tạo Buổi nhóm**: Thiết lập lịch và thời gian.
   - **Phân công**: Gán template, thêm người thủ công, theo dõi tiến độ.
4. **Nhân Sự & Sinh Hoạt (Members & Activities)**: Thêm người, đổi thông tin, điểm danh.
5. **Tài Chính (Finance)**: Thu quỹ, phiếu chi, báo cáo.

## 3. Implementation Steps
1. Create `DocsLayout.vue`.
2. Update `DocsController.php` with new methods and routes.
3. Refactor `Docs/Setup.vue` to use `DocsLayout.vue`.
4. Create `Docs/DutyRoster.vue` and write the comprehensive guide for the Duty Roster feature using proper typography, alerts, and placeholder areas for images.
5. Create placeholder/stub components for the other sections (`Overview.vue`, `Members.vue`, `Finance.vue`) so the navigation is complete.

## 4. Why This Works well for the CMS
By keeping the documentation inside Vue components rather than static Markdown files:
- We can reuse UI components (Icons, Buttons, Badge styles) directly in the docs.
- We can add interactive elements (like the "Copy Command" button currently in Setup).
- Visually consistent with the rest of the application.
