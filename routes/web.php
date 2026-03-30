<?php

use Illuminate\Support\Facades\Route;

use Inertia\Inertia;

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\NewPasswordController;

Route::middleware('guest')->group(function () {
    Route::get('login', [AuthController::class, 'login'])->name('login');
    Route::post('login', [AuthController::class, 'authenticate'])->name('login.authenticate');

    // Mật khẩu (Password Reset)
    Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])->name('password.request');
    Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])->name('password.email');
    Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])->name('password.reset');
    Route::post('reset-password', [NewPasswordController::class, 'store'])->name('password.store');
});
// Hướng dẫn cài đặt và Dữ liệu mẫu ban đầu (Moved to 'help.' group)

Route::middleware('auth')->group(function () {
    Route::get('/', [\App\Http\Controllers\WelcomeController::class, 'index'])->name('welcome');
    Route::get('/dashboard', [\App\Http\Controllers\DashboardController::class, 'index'])->name('dashboard');

    Route::get('api/members', [\App\Http\Controllers\MemberController::class, 'apiIndex'])->name('api.members.index');
    Route::resource('members', \App\Http\Controllers\MemberController::class)->except(['create', 'edit']);

    // Household Head & Member Relationships
    Route::put('households/{household}/head', [\App\Http\Controllers\MemberController::class, 'setHouseholdHead'])->name('households.set-head');
    Route::post('members/{member}/household', [\App\Http\Controllers\HouseholdController::class, 'store'])->name('households.store');
    Route::post('households/{household}/members', [\App\Http\Controllers\HouseholdController::class, 'addMember'])->name('households.add_member');
    Route::delete('households/{household}/members/{member}', [\App\Http\Controllers\HouseholdController::class, 'removeMember'])->name('households.remove_member');

    Route::post('members/{member}/relationships', [\App\Http\Controllers\MemberController::class, 'storeRelationship'])->name('members.relationships.store');
    Route::delete('members/{member}/relationships/{relatedMember}', [\App\Http\Controllers\MemberController::class, 'destroyRelationship'])->name('members.relationships.destroy');

    // Faith Journeys
    Route::resource('faith-journeys', \App\Http\Controllers\FaithJourneyController::class)->only(['store', 'update', 'destroy']);

    // Visitation Reasons
    Route::post('/visitation-reasons', [\App\Http\Controllers\Portal\VisitationController::class, 'storeReason'])->name('visitation-reasons.store');
    Route::delete('/visitation-reasons/{reason}', [\App\Http\Controllers\Portal\VisitationController::class, 'destroyReason'])->name('visitation-reasons.destroy');
    
    // Search
    Route::get('api/search', [\App\Http\Controllers\SearchController::class, 'search'])->name('search.global');

    // Notifications
    Route::get('notifications', [\App\Http\Controllers\NotificationController::class, 'index'])->name('notifications.index');
    Route::get('api/notifications/list', [\App\Http\Controllers\NotificationController::class, 'getList'])->name('api.notifications.list');
    Route::get('api/notifications/unread-count', [\App\Http\Controllers\NotificationController::class, 'getUnreadCount'])->name('api.notifications.unread-count');
    Route::post('notifications/{id}/read', [\App\Http\Controllers\NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('notifications/mark-all-read', [\App\Http\Controllers\NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-read');
    
    // Documents / Knowledge Base
    Route::get('documents', [\App\Http\Controllers\DocumentController::class, 'index'])->name('documents.index');
    Route::post('documents', [\App\Http\Controllers\DocumentController::class, 'store'])->name('documents.store');
    Route::delete('documents/{document}', [\App\Http\Controllers\DocumentController::class, 'destroy'])->name('documents.destroy');
    Route::get('documents/{document}/download', [\App\Http\Controllers\DocumentController::class, 'download'])->name('documents.download');

    // Calendar
    Route::get('calendar', [\App\Http\Controllers\CalendarController::class, 'index'])->name('calendar.index');
    Route::get('api/calendar/events', [\App\Http\Controllers\CalendarController::class, 'fetchEvents'])->name('calendar.api.events');
    Route::post('calendar/events', [\App\Http\Controllers\CalendarController::class, 'store'])->name('calendar.events.store');
    Route::put('calendar/events/{event}', [\App\Http\Controllers\CalendarController::class, 'update'])->name('calendar.events.update');
    Route::delete('calendar/events/{event}', [\App\Http\Controllers\CalendarController::class, 'destroy'])->name('calendar.events.destroy');

    // Pastoral Care & Ticketing
    Route::get('care', [\App\Http\Controllers\CareController::class, 'index'])->name('care.index');
    Route::post('care', [\App\Http\Controllers\CareController::class, 'store'])->name('care.store');
    Route::patch('care/{careRequest}/status', [\App\Http\Controllers\CareController::class, 'updateStatus'])->name('care.status.update');
    Route::patch('care/{careRequest}/assign', [\App\Http\Controllers\CareController::class, 'assign'])->name('care.assign.update');
    Route::delete('care/{careRequest}', [\App\Http\Controllers\CareController::class, 'destroy'])->name('care.destroy');

    Route::patch('members/update-status', [\App\Http\Controllers\MemberController::class, 'updateStatus'])->name('members.update-status');
    
    // ==========================================
    // Sổ Tay Hội Thánh & Ban Ngành (Data Scope MAC V2)
    // ==========================================
    Route::prefix('admin/chronicles')->name('admin.chronicles.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\ChronicleController::class, 'index'])->name('index');
        Route::post('/', [\App\Http\Controllers\Admin\ChronicleController::class, 'store'])->name('store');
        Route::put('/{chronicle}', [\App\Http\Controllers\Admin\ChronicleController::class, 'update'])->name('update');
        Route::delete('/{chronicle}', [\App\Http\Controllers\Admin\ChronicleController::class, 'destroy'])->name('destroy');
    });

    // ==========================================
    // Restricted Administrative Routes
    // ==========================================
    Route::middleware(\App\Http\Middleware\EnsureSuperAdmin::class)->group(function () {
        Route::resource('departments', \App\Http\Controllers\DepartmentController::class)->except(['create', 'edit', 'destroy']);
        Route::delete('departments/{department}', [\App\Http\Controllers\DepartmentController::class, 'destroy'])->name('departments.destroy');
        
        // Departments Sub-features
        Route::post('departments/{department}/teams', [\App\Http\Controllers\DepartmentController::class, 'storeTeam'])->name('departments.teams.store');
        Route::put('departments/{department}/teams/{team}', [\App\Http\Controllers\DepartmentController::class, 'updateTeam'])->name('departments.teams.update');
        Route::delete('departments/{department}/teams/{team}', [\App\Http\Controllers\DepartmentController::class, 'destroyTeam'])->name('departments.teams.destroy');
        
        Route::post('departments/{department}/members', [\App\Http\Controllers\DepartmentController::class, 'assignMember'])->name('departments.members.assign');
        Route::delete('departments/{department}/members/{member}', [\App\Http\Controllers\DepartmentController::class, 'removeMember'])->name('departments.members.remove');
        Route::put('departments/{department}/features', [\App\Http\Controllers\DepartmentController::class, 'updateFeatures'])->name('departments.features.update');

        // Speakers
        Route::get('api/speakers', [\App\Http\Controllers\SpeakerController::class, 'apiIndex'])->name('api.speakers.index');
        Route::resource('speakers', \App\Http\Controllers\SpeakerController::class)->except(['create', 'edit']);

        // Trimmers & Account Management
        Route::resource('users', \App\Http\Controllers\UserController::class);
        Route::post('users/{user}/link-member', [\App\Http\Controllers\UserController::class, 'linkMember'])->name('users.link-member');
        Route::resource('roles', \App\Http\Controllers\RoleController::class);
    });
    // ==========================================

    // Meetings — export/import MUST be before resource() to avoid {meeting} route conflict
    Route::get('meetings/export', [\App\Http\Controllers\MeetingController::class, 'export'])->name('meetings.export');
    Route::post('meetings/import', [\App\Http\Controllers\MeetingController::class, 'import'])->name('meetings.import');
    Route::post('meetings/{meeting}/toggle-cancel', [\App\Http\Controllers\MeetingController::class, 'toggleCancel'])->name('meetings.toggle-cancel');
    Route::resource('meetings', \App\Http\Controllers\MeetingController::class);

    // Lịch sử Dâng Hiến Cá Nhân (Tithe & Offering)
    Route::get('my-giving', [\App\Http\Controllers\User\DonationController::class, 'myGiving'])->name('user.donations.index');
    
    Route::prefix('admin')->middleware(\App\Http\Middleware\EnsureSuperAdmin::class)->group(function () {
        Route::get('/users/permissions', [\App\Http\Controllers\Admin\UserPermissionController::class, 'index'])->name('admin.users.permissions');
        // MAC APIs
        Route::get('/users/{user}/permissions', [\App\Http\Controllers\Admin\UserPermissionController::class, 'show'])->name('admin.users.permissions.show');
        Route::post('/users/{user}/permissions/toggle', [\App\Http\Controllers\Admin\UserPermissionController::class, 'toggle'])->name('admin.users.permissions.toggle');
        Route::post('/users/{user}/permissions/roles', [\App\Http\Controllers\Admin\UserPermissionController::class, 'updateRoles'])->name('admin.users.permissions.roles');
        Route::post('/users/{user}/permissions/grant-full', [\App\Http\Controllers\Admin\UserPermissionController::class, 'grantFull'])->name('admin.users.permissions.grant-full');

        // Global System Activity Logs
        Route::get('/activity-logs', [\App\Http\Controllers\Admin\ActivityLogController::class, 'index'])->name('admin.activity.index');

        // Feature System Configuration (Tab Tính Năng)
        Route::get('/features', [\App\Http\Controllers\Admin\SystemFeatureController::class, 'index'])->name('admin.features.index');
        Route::post('/features/assign', [\App\Http\Controllers\Admin\SystemFeatureController::class, 'assign'])->name('admin.features.assign');
        Route::post('/features/matrix/toggle', [\App\Http\Controllers\Admin\SystemFeatureController::class, 'matrixToggle'])->name('admin.features.matrix.toggle');
        Route::post('/features/store', [\App\Http\Controllers\Admin\SystemFeatureController::class, 'storeFeature'])->name('admin.features.store');

        // Quản lý Tài Sản (Asset & Inventory)
        Route::resource('assets', \App\Http\Controllers\Admin\AssetController::class)->except(['create', 'edit', 'show'])->names([
            'index' => 'admin.assets.index',
            'store' => 'admin.assets.store',
            'update' => 'admin.assets.update',
            'destroy' => 'admin.assets.destroy',
        ]);
        Route::prefix('assets/{asset}')->name('admin.assets.')->group(function () {
            Route::get('loans', [\App\Http\Controllers\Admin\AssetController::class, 'fetchLoans'])->name('loans');
            Route::post('loan', [\App\Http\Controllers\Admin\AssetController::class, 'loanAsset'])->name('loan.store');
        });
        Route::patch('asset-loans/{loan}/return', [\App\Http\Controllers\Admin\AssetController::class, 'returnAsset'])->name('admin.assets.loan.return');
        Route::get('api/search-users', [\App\Http\Controllers\Admin\AssetController::class, 'searchBorrowers'])->name('admin.assets.api.search-users'); // API query borrowers
        
        // Quản lý Thân Hữu (Visitor CRM)
        Route::resource('visitors', \App\Http\Controllers\Admin\VisitorController::class)->except(['create', 'edit', 'show'])->names([
            'index' => 'admin.visitors.index',
            'store' => 'admin.visitors.store',
            'update' => 'admin.visitors.update',
            'destroy' => 'admin.visitors.destroy',
        ]);
        Route::get('visitors/{visitor}/followups', [\App\Http\Controllers\Admin\VisitorController::class, 'getFollowups'])->name('admin.visitors.followups.index');
        Route::post('visitors/{visitor}/followups', [\App\Http\Controllers\Admin\VisitorController::class, 'storeFollowup'])->name('admin.visitors.followups.store');

        // Quản lý Dâng Hiến (Tithe & Offering)
        Route::get('donations', [\App\Http\Controllers\Admin\DonationController::class, 'index'])->name('admin.donations.index');
        Route::get('donations/batch', [\App\Http\Controllers\Admin\DonationController::class, 'createBatch'])->name('admin.donations.batch');
        Route::post('donations/batch', [\App\Http\Controllers\Admin\DonationController::class, 'storeBatch'])->name('admin.donations.store-batch');
        Route::get('donations/api/search-users', [\App\Http\Controllers\Admin\DonationController::class, 'searchUsers'])->name('admin.donations.api.search-users');
        Route::post('funds', [\App\Http\Controllers\Admin\DonationController::class, 'storeFund'])->name('admin.funds.store');
        Route::put('funds/{fund}', [\App\Http\Controllers\Admin\DonationController::class, 'updateFund'])->name('admin.funds.update');

        // Gửi thông báo / Email Hàng Loạt (Broadcasting)
        Route::get('broadcasts', [\App\Http\Controllers\Admin\BroadcastController::class, 'index'])->name('admin.broadcasts.index');
        Route::get('broadcasts/create', [\App\Http\Controllers\Admin\BroadcastController::class, 'create'])->name('admin.broadcasts.create');
        Route::post('broadcasts', [\App\Http\Controllers\Admin\BroadcastController::class, 'store'])->name('admin.broadcasts.store');
        Route::post('broadcasts/{broadcast}/send', [\App\Http\Controllers\Admin\BroadcastController::class, 'send'])->name('admin.broadcasts.send');
        Route::delete('broadcasts/{broadcast}', [\App\Http\Controllers\Admin\BroadcastController::class, 'destroy'])->name('admin.broadcasts.destroy');

        // Quản lý Bản Tin (Announcements)
        Route::resource('announcements', \App\Http\Controllers\Admin\AnnouncementController::class)->except(['show', 'edit', 'update'])->names('admin.announcements');

        // Quản lý Biểu mẫu (Forms Manager)
        Route::resource('forms-manager', \App\Http\Controllers\Admin\FormTemplateController::class)->except(['create', 'edit', 'show'])->parameters(['forms-manager' => 'form'])->names('admin.forms-manager');
        Route::get('forms-manager/{form}/download', [\App\Http\Controllers\Admin\FormTemplateController::class, 'download'])->name('admin.forms-manager.download');

        // Activity Logs
        Route::get('/activity-logs', [\App\Http\Controllers\Admin\ActivityLogController::class, 'index'])->name('admin.activity.logs');
    });

    // ── Member Portal (Cổng tín hữu) ─────────────────────────────────────────────
    Route::prefix('member')->group(function () {
        Route::get('/', [\App\Http\Controllers\MemberPortalController::class, 'index'])->name('member.portal.index');
        Route::post('/care', [\App\Http\Controllers\MemberPortalController::class, 'submitCare'])->name('member.portal.care.submit');
        Route::patch('/duty/{dutyAssignment}/status', [\App\Http\Controllers\DutyRosterController::class, 'updateMemberStatus'])->name('member.duty.update-status');
    });

    // ── Offline Page (PWA fallback) ────────────────────────────────────────────
    Route::get('/offline', function () {
        return \Inertia\Inertia::render('Offline');
    })->name('offline');

    // ── Portal, Ministry, Finance, Deacon ─────────────────────────────────────
    require __DIR__.'/portal.php';
    require __DIR__.'/ministry.php';

    Route::post('logout', [\App\Http\Controllers\Auth\AuthController::class, 'logout'])->name('logout');
});

// Help / Documentation (Publicly accessible)
Route::group(['prefix' => 'huong-dan', 'as' => 'help.'], function () {
    Route::get('/', function () {
        return redirect()->route('help.install', ['mode' => 'theo-chuc-nang']);
    });
    
    Route::group(['prefix' => '{mode}', 'where' => ['mode' => 'theo-chuc-nang|theo-nguoi-dung|theo-portal']], function () {
        Route::get('/dang-nhap', [\App\Http\Controllers\DocsController::class, 'auth'])->name('auth');
        Route::get('/cai-dat', [\App\Http\Controllers\DocsController::class, 'setup'])->name('install');
        Route::get('/tong-quan', [\App\Http\Controllers\DocsController::class, 'overview'])->name('overview');
        Route::get('/lich-phan-cong', [\App\Http\Controllers\DocsController::class, 'dutyRoster'])->name('duty_rooster');
        
        Route::group(['prefix' => 'ban-nganh', 'as' => 'departments.'], function () {
            Route::get('/', function ($mode) { return redirect()->route('help.departments.members', ['mode' => $mode]); })->name('index');
            Route::get('/gioi-thieu', [\App\Http\Controllers\DocsController::class, 'deptIntro'])->name('intro');
            Route::get('/thanh-vien', [\App\Http\Controllers\DocsController::class, 'deptMembers'])->name('members');
            Route::get('/diem-danh', [\App\Http\Controllers\DocsController::class, 'deptAttendance'])->name('attendance');
            Route::get('/tham-vieng', [\App\Http\Controllers\DocsController::class, 'deptVisitation'])->name('visitation');
            Route::get('/phan-cong', [\App\Http\Controllers\DocsController::class, 'deptAssignments'])->name('assignments');
            Route::get('/tai-chinh', [\App\Http\Controllers\DocsController::class, 'deptFinance'])->name('finance');
            Route::get('/bao-cao', [\App\Http\Controllers\DocsController::class, 'deptReports'])->name('reports');
        });

        Route::group(['prefix' => 'portal', 'as' => 'portals.'], function () {
            Route::get('/gioi-thieu', [\App\Http\Controllers\DocsController::class, 'portalIntro'])->name('intro');
        });

        Route::get('/su-kien', [\App\Http\Controllers\DocsController::class, 'meetings'])->name('meetings');
        Route::get('/nhan-su', [\App\Http\Controllers\DocsController::class, 'members'])->name('members');
        Route::get('/tai-chinh', [\App\Http\Controllers\DocsController::class, 'finance'])->name('finance');

        Route::group(['prefix' => 'quan-tri', 'as' => 'admin.'], function () {
            Route::get('/nguoi-dung', [\App\Http\Controllers\DocsController::class, 'adminUsers'])->name('users');
            Route::get('/tinh-nang', [\App\Http\Controllers\DocsController::class, 'adminFeatures'])->name('features');
            Route::get('/phan-quyen', [\App\Http\Controllers\DocsController::class, 'adminPermissions'])->name('permissions');
        });

        Route::get('/quan-tri-he-thong', [\App\Http\Controllers\DocsController::class, 'sysadmin'])->name('sysadmin');
        Route::get('/lanh-dao', [\App\Http\Controllers\DocsController::class, 'leadership'])->name('leadership');
        Route::get('/giao-duc', [\App\Http\Controllers\DocsController::class, 'education'])->name('education');
        Route::get('/cong-truc-tuyen', [\App\Http\Controllers\DocsController::class, 'portals'])->name('portals');
    });

    // Keep leftover routes as redirects to Docs logic or keep them if needed,
    // but we will mainly use the new Docs pages.
    Route::get('/thong-bao', function () { return redirect()->route('help.install', ['mode' => 'theo-chuc-nang']); })->name('notifications');
    Route::get('/ban-tin', function () { return redirect()->route('help.install', ['mode' => 'theo-chuc-nang']); })->name('announcements');
    Route::get('/tin-tu-dong', function () { return redirect()->route('help.install', ['mode' => 'theo-chuc-nang']); })->name('broadcasts');
    Route::get('/thanh-vien-phan-cong', function () { return redirect()->route('help.install', ['mode' => 'theo-chuc-nang']); })->name('portal.members');
    Route::get('/diem-danh-tham-vieng', function () { return redirect()->route('help.install', ['mode' => 'theo-chuc-nang']); })->name('portal.attendance');
    Route::get('/tai-chinh-bao-cao', function () { return redirect()->route('help.install', ['mode' => 'theo-chuc-nang']); })->name('portal.finance');
});
