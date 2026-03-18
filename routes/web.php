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
    Route::get('/', [\App\Http\Controllers\DashboardController::class, 'index'])->name('dashboard');

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

    // ══════════════════════════════════════════════════════
    // MEMBER PORTAL — Cổng Tín Hữu (Tối giản, Mobile-first)
    // ══════════════════════════════════════════════════════
    Route::prefix('member')->group(function () {
        Route::get('/', [\App\Http\Controllers\MemberPortalController::class, 'index'])->name('member.portal.index');
        Route::post('/care', [\App\Http\Controllers\MemberPortalController::class, 'submitCare'])->name('member.portal.care.submit');
        Route::patch('/duty/{dutyAssignment}/status', [\App\Http\Controllers\DutyRosterController::class, 'updateMemberStatus'])->name('member.duty.update-status');
    });

    // Department Portal (Ban Sinh Hoạt — Activities)
    Route::prefix('portal')->middleware(\App\Http\Middleware\CheckPortalAccess::class . ':activities')->group(function () {
        Route::get('/', [\App\Http\Controllers\DepartmentPortalController::class, 'index'])->name('portal.index');
        Route::post('/switch-context', [\App\Http\Controllers\DepartmentPortalController::class, 'switchContext'])->name('portal.switch-context');
        
        // Nhật ký Ban ngành
        Route::get('/logs', [\App\Http\Controllers\DepartmentPortalController::class, 'logs'])->name('portal.logs');

        // Điểm danh
        Route::middleware('portal.access:attendance,activities')->group(function () {
            Route::get('/attendance', [\App\Http\Controllers\Portal\AttendanceController::class, 'index'])->name('portal.attendance.index');
            Route::get('/attendance/{meeting}/export', [\App\Http\Controllers\Portal\AttendanceController::class, 'exportTemplate'])->name('portal.attendance.export');
            Route::post('/attendance/import', [\App\Http\Controllers\Portal\AttendanceController::class, 'import'])->name('portal.attendance.import');
            Route::get('/attendance/{meeting}', [\App\Http\Controllers\Portal\AttendanceController::class, 'show'])->name('portal.attendance.show');
            Route::post('/attendance/{meeting}', [\App\Http\Controllers\Portal\AttendanceController::class, 'store'])->name('portal.attendance.store');
        });

        // Thành viên
        Route::middleware('portal.access:members,activities')->group(function () {
            Route::get('/members', [\App\Http\Controllers\Portal\PortalMemberController::class, 'index'])->name('portal.members.index');
            Route::get('/members/export', [\App\Http\Controllers\Portal\PortalMemberController::class, 'exportTemplate'])->name('portal.members.export');
            Route::post('/members/import', [\App\Http\Controllers\Portal\PortalMemberController::class, 'import'])->name('portal.members.import');
            Route::post('/members/{member}/role', [\App\Http\Controllers\Portal\PortalMemberController::class, 'updateRole'])->name('portal.members.update');
            Route::post('/members/{member}/toggle-active', [\App\Http\Controllers\Portal\PortalMemberController::class, 'toggleActiveStatus'])->name('portal.members.toggle-active');
            Route::post('/members/bulk-assign-team', [\App\Http\Controllers\Portal\PortalMemberController::class, 'bulkAssignTeam'])->name('portal.members.bulk-assign');
            Route::post('/members/bulk-toggle-active', [\App\Http\Controllers\Portal\PortalMemberController::class, 'bulkToggleActive'])->name('portal.members.bulk-toggle-active');
            Route::post('/members/{member}/generate-account', [\App\Http\Controllers\Portal\PortalMemberController::class, 'createUserAccount'])->name('portal.members.generate-account');
            Route::delete('/members/{member}', [\App\Http\Controllers\Portal\PortalMemberController::class, 'removeMember'])->name('portal.members.remove');
            Route::delete('/members/bulk/remove', [\App\Http\Controllers\Portal\PortalMemberController::class, 'bulkRemove'])->name('portal.members.bulk-remove');
        });

        // Phân công
        Route::middleware('portal.access:assignments,activities')->group(function () {
            Route::prefix('duty-rooster')->name('portal.duty-rooster.')->group(function () {
                Route::get('/', [\App\Http\Controllers\DutyRosterController::class, 'index'])->name('index');
                Route::post('/assignments', [\App\Http\Controllers\DutyRosterController::class, 'storeAssignment'])->name('assignments.store');
                Route::post('/copy-week', [\App\Http\Controllers\DutyRosterController::class, 'copyWeek'])->name('copy-week');
                Route::post('/departments/{department}/roles', [\App\Http\Controllers\DutyRosterController::class, 'storeRole'])->name('roles.store');
                Route::delete('/roles/{role}', [\App\Http\Controllers\DutyRosterController::class, 'destroyRole'])->name('roles.destroy');

                Route::get('/templates', [\App\Http\Controllers\DutyRosterController::class, 'templatesIndex'])->name('templates.index');
                Route::get('/templates/create', [\App\Http\Controllers\DutyRosterController::class, 'templateCreate'])->name('templates.create');
                Route::get('/templates/{template}', [\App\Http\Controllers\DutyRosterController::class, 'templateShow'])->name('templates.show');
                Route::post('/templates', [\App\Http\Controllers\DutyRosterController::class, 'storeTemplate'])->name('templates.store');
                Route::put('/templates/{template}', [\App\Http\Controllers\DutyRosterController::class, 'updateTemplate'])->name('templates.update');
                Route::post('/templates/apply', [\App\Http\Controllers\DutyRosterController::class, 'applyTemplate'])->name('templates.apply');
                Route::delete('/templates/{template}', [\App\Http\Controllers\DutyRosterController::class, 'deleteTemplate'])->name('templates.destroy');
                Route::post('/templates/{template}/roles', [\App\Http\Controllers\DutyRosterController::class, 'addTemplateRole'])->name('templates.roles.add');
                Route::delete('/templates/{template}/roles/{role}', [\App\Http\Controllers\DutyRosterController::class, 'removeTemplateRole'])->name('templates.roles.remove');

                Route::get('/{meeting}/export', [\App\Http\Controllers\DutyRosterController::class, 'exportMeeting'])->name('export');
                Route::get('/{meeting}', [\App\Http\Controllers\DutyRosterController::class, 'show'])->name('show');
            });
        });

        // Báo cáo
        Route::middleware('portal.access:reports,activities')->group(function () {
            Route::get('/reports', [\App\Http\Controllers\Portal\DeptReportController::class, 'index'])->name('portal.reports.index');
            Route::post('/reports/save', [\App\Http\Controllers\Portal\DeptReportController::class, 'saveReport'])->name('portal.reports.save');
            Route::post('/reports/{report}/approve', [\App\Http\Controllers\Portal\DeptReportController::class, 'approveReport'])->name('portal.reports.approve');
        });

        // Tài chính
        Route::middleware('portal.access:finance,activities')->group(function () {
            Route::get('/finance', [\App\Http\Controllers\Portal\DeptFinanceController::class, 'index'])->name('portal.finance.index');
            Route::post('/finance/meetings/{meeting}/finance', [\App\Http\Controllers\Portal\DeptFinanceController::class, 'storeFinance'])->name('portal.finance.store');
            Route::delete('/finance/meetings/{meeting}/finance', [\App\Http\Controllers\Portal\DeptFinanceController::class, 'deleteFinance'])->name('portal.finance.delete');
            Route::post('/finance/funds', [\App\Http\Controllers\Portal\DeptFinanceController::class, 'storeFund'])->name('portal.finance.funds.store');
            Route::delete('/finance/funds/{fund}', [\App\Http\Controllers\Portal\DeptFinanceController::class, 'destroyFund'])->name('portal.finance.funds.destroy');
        });
        
        // Thăm viếng
        Route::middleware('portal.access:visitation,activities')->group(function () {
            Route::get('/visitation', [\App\Http\Controllers\Portal\ActivitiesVisitationController::class, 'index'])->name('portal.visitation.index');
            Route::post('/visitation', [\App\Http\Controllers\Portal\ActivitiesVisitationController::class, 'store'])->name('portal.visitation.store');
            Route::put('/visitation/{visitation}', [\App\Http\Controllers\Portal\ActivitiesVisitationController::class, 'update'])->name('portal.visitation.update');
            Route::delete('/visitation/{visitation}', [\App\Http\Controllers\Portal\ActivitiesVisitationController::class, 'destroy'])->name('portal.visitation.destroy');
        });

        // Sổ Tay Ban Ngành (Chronicles)
        Route::middleware('portal.access:chronicles,activities')->group(function () {
            Route::prefix('chronicles')->name('portal.chronicles.')->group(function () {
                Route::get('/', [\App\Http\Controllers\Portal\ChronicleController::class, 'index'])->name('index');
                Route::post('/', [\App\Http\Controllers\Portal\ChronicleController::class, 'store'])->name('store');
                Route::put('/{chronicle}', [\App\Http\Controllers\Portal\ChronicleController::class, 'update'])->name('update');
                Route::delete('/{chronicle}', [\App\Http\Controllers\Portal\ChronicleController::class, 'destroy'])->name('destroy');
            });
        });
        // Tài liệu nội bộ Ban Ngành
        Route::middleware('portal.access:documents,activities')->group(function () {
            Route::get('/documents', [\App\Http\Controllers\DocumentController::class, 'index'])->name('portal.documents.index');
            Route::post('/documents', [\App\Http\Controllers\DocumentController::class, 'store'])->name('portal.documents.store');
            Route::delete('/documents/{document}', [\App\Http\Controllers\DocumentController::class, 'destroy'])->name('portal.documents.destroy');
        });

        // Chăm Sóc Tín Hữu
        Route::middleware('portal.access:care,activities')->group(function () {
            Route::get('/care', [\App\Http\Controllers\CareController::class, 'index'])->name('portal.care.index');
            Route::post('/care', [\App\Http\Controllers\CareController::class, 'store'])->name('portal.care.store');
            Route::patch('/care/{careRequest}/status', [\App\Http\Controllers\CareController::class, 'updateStatus'])->name('portal.care.updateStatus');
            Route::patch('/care/{careRequest}/assign', [\App\Http\Controllers\CareController::class, 'assign'])->name('portal.care.assign');
            Route::delete('/care/{careRequest}', [\App\Http\Controllers\CareController::class, 'destroy'])->name('portal.care.destroy');
        });
    });


    // Ministry Portal
    Route::prefix('ministry')->middleware(\App\Http\Middleware\EnsureMinistryContext::class)->group(function () {
        Route::get('/', [\App\Http\Controllers\MinistryPortalController::class, 'index'])->name('ministry.index');
        Route::post('/switch-context', [\App\Http\Controllers\MinistryPortalController::class, 'switchContext'])->name('ministry.switch-context');
        
        // Nhật ký Ban ngành (Mục vụ)
        Route::get('/logs', [\App\Http\Controllers\MinistryPortalController::class, 'logs'])->name('ministry.logs');

        // Visitation Module
        Route::get('/visitation', [\App\Http\Controllers\Portal\VisitationController::class, 'index'])->name('ministry.visitation.index');
        Route::post('/visitation', [\App\Http\Controllers\Portal\VisitationController::class, 'store'])->name('ministry.visitation.store');
        Route::put('/visitation/{visitation}', [\App\Http\Controllers\Portal\VisitationController::class, 'update'])->name('ministry.visitation.update');
        Route::delete('/visitation/{visitation}', [\App\Http\Controllers\Portal\VisitationController::class, 'destroy'])->name('ministry.visitation.destroy');

        // Member Management Module
        Route::get('/members', [\App\Http\Controllers\Portal\PortalMemberController::class, 'index'])->name('ministry.members.index');
        Route::get('/members/export', [\App\Http\Controllers\Portal\PortalMemberController::class, 'exportTemplate'])->name('ministry.members.export');
        Route::post('/members/import', [\App\Http\Controllers\Portal\PortalMemberController::class, 'import'])->name('ministry.members.import');
        Route::post('/members/{member}/role', [\App\Http\Controllers\Portal\PortalMemberController::class, 'updateRole'])->name('ministry.members.update');
        Route::post('/members/{member}/toggle-active', [\App\Http\Controllers\Portal\PortalMemberController::class, 'toggleActiveStatus'])->name('ministry.members.toggle-active');
        Route::post('/members/bulk-assign-team', [\App\Http\Controllers\Portal\PortalMemberController::class, 'bulkAssignTeam'])->name('ministry.members.bulk-assign');
        Route::post('/members/bulk-toggle-active', [\App\Http\Controllers\Portal\PortalMemberController::class, 'bulkToggleActive'])->name('ministry.members.bulk-toggle-active');
        Route::post('/members/{member}/generate-account', [\App\Http\Controllers\Portal\PortalMemberController::class, 'createUserAccount'])->name('ministry.members.generate-account');
        Route::delete('/members/{member}', [\App\Http\Controllers\Portal\PortalMemberController::class, 'removeMember'])->name('ministry.members.remove');
        Route::delete('/members/bulk/remove', [\App\Http\Controllers\Portal\PortalMemberController::class, 'bulkRemove'])->name('ministry.members.bulk-remove');

        // Phân công
        Route::middleware('portal.access:assignments,ministry')->group(function () {
            Route::prefix('duty-rooster')->name('ministry.duty-rooster.')->group(function () {
                Route::get('/', [\App\Http\Controllers\DutyRosterController::class, 'index'])->name('index');
                Route::post('/assignments', [\App\Http\Controllers\DutyRosterController::class, 'storeAssignment'])->name('assignments.store');
                Route::post('/copy-week', [\App\Http\Controllers\DutyRosterController::class, 'copyWeek'])->name('copy-week');
                Route::post('/departments/{department}/roles', [\App\Http\Controllers\DutyRosterController::class, 'storeRole'])->name('roles.store');
                Route::delete('/roles/{role}', [\App\Http\Controllers\DutyRosterController::class, 'destroyRole'])->name('roles.destroy');

                Route::get('/templates', [\App\Http\Controllers\DutyRosterController::class, 'templatesIndex'])->name('templates.index');
                Route::get('/templates/create', [\App\Http\Controllers\DutyRosterController::class, 'templateCreate'])->name('templates.create');
                Route::get('/templates/{template}', [\App\Http\Controllers\DutyRosterController::class, 'templateShow'])->name('templates.show');
                Route::post('/templates', [\App\Http\Controllers\DutyRosterController::class, 'storeTemplate'])->name('templates.store');
                Route::put('/templates/{template}', [\App\Http\Controllers\DutyRosterController::class, 'updateTemplate'])->name('templates.update');
                Route::post('/templates/apply', [\App\Http\Controllers\DutyRosterController::class, 'applyTemplate'])->name('templates.apply');
                Route::delete('/templates/{template}', [\App\Http\Controllers\DutyRosterController::class, 'deleteTemplate'])->name('templates.destroy');
                Route::post('/templates/{template}/roles', [\App\Http\Controllers\DutyRosterController::class, 'addTemplateRole'])->name('templates.roles.add');
                Route::delete('/templates/{template}/roles/{role}', [\App\Http\Controllers\DutyRosterController::class, 'removeTemplateRole'])->name('templates.roles.remove');

                Route::get('/{meeting}/export', [\App\Http\Controllers\DutyRosterController::class, 'exportMeeting'])->name('export');
                Route::get('/{meeting}', [\App\Http\Controllers\DutyRosterController::class, 'show'])->name('show');
            });
        });

        // Sổ Tay Ban Ngành (Chronicles)
        Route::middleware('portal.access:chronicles,ministry')->group(function () {
            Route::prefix('chronicles')->name('ministry.chronicles.')->group(function () {
                Route::get('/', [\App\Http\Controllers\Portal\ChronicleController::class, 'index'])->name('index');
                Route::post('/', [\App\Http\Controllers\Portal\ChronicleController::class, 'store'])->name('store');
                Route::put('/{chronicle}', [\App\Http\Controllers\Portal\ChronicleController::class, 'update'])->name('update');
                Route::delete('/{chronicle}', [\App\Http\Controllers\Portal\ChronicleController::class, 'destroy'])->name('destroy');
            });
        });

        // Tài liệu nội bộ Ban Ngành (Mục vụ)
        Route::middleware('portal.access:documents,ministry')->group(function () {
            Route::get('/documents', [\App\Http\Controllers\DocumentController::class, 'index'])->name('ministry.documents.index');
            Route::post('/documents', [\App\Http\Controllers\DocumentController::class, 'store'])->name('ministry.documents.store');
            Route::delete('/documents/{document}', [\App\Http\Controllers\DocumentController::class, 'destroy'])->name('ministry.documents.destroy');
        });

        // Chăm Sóc Tín Hữu (Mục vụ)
        Route::middleware('portal.access:care,ministry')->group(function () {
            Route::get('/care', [\App\Http\Controllers\CareController::class, 'index'])->name('ministry.care.index');
            Route::post('/care', [\App\Http\Controllers\CareController::class, 'store'])->name('ministry.care.store');
            Route::patch('/care/{careRequest}/status', [\App\Http\Controllers\CareController::class, 'updateStatus'])->name('ministry.care.updateStatus');
            Route::patch('/care/{careRequest}/assign', [\App\Http\Controllers\CareController::class, 'assign'])->name('ministry.care.assign');
            Route::delete('/care/{careRequest}', [\App\Http\Controllers\CareController::class, 'destroy'])->name('ministry.care.destroy');
        });

        // ─── Ban Cơ Đốc Giáo Dục — Education sub-portal ──────────────────────
        Route::prefix('education')->group(function () {
            Route::get('/', [\App\Http\Controllers\Portal\EducationController::class, 'dashboard'])->name('ministry.education.index');
            
            // Tính năng lóp học (education-classes)
            Route::middleware('portal.access:education-classes,ministry')->group(function () {
                Route::get('/classes', [\App\Http\Controllers\Portal\EducationController::class, 'index'])->name('ministry.education.classes');
                Route::post('/', [\App\Http\Controllers\Portal\EducationController::class, 'store'])->name('ministry.education.store');
                Route::put('/{eduClass}', [\App\Http\Controllers\Portal\EducationController::class, 'update'])->name('ministry.education.update');
                Route::delete('/{eduClass}', [\App\Http\Controllers\Portal\EducationController::class, 'destroy'])->name('ministry.education.destroy');
                // Members
                Route::post('/{eduClass}/members', [\App\Http\Controllers\Portal\EducationController::class, 'storeMember'])->name('ministry.education.members.store');
                Route::post('/{eduClass}/members/bulk', [\App\Http\Controllers\Portal\EducationController::class, 'bulkStoreMember'])->name('ministry.education.members.bulk-store');
                Route::delete('/{eduClass}/members/{member}', [\App\Http\Controllers\Portal\EducationController::class, 'destroyMember'])->name('ministry.education.members.destroy');
            });

            // Tính năng điểm danh (education-attendance)
            Route::middleware('portal.access:education-attendance,ministry')->group(function () {
                // Sessions
                Route::get('/{eduClass}/sessions', [\App\Http\Controllers\Portal\EducationController::class, 'sessions'])->name('ministry.education.sessions');
                Route::post('/{eduClass}/sessions', [\App\Http\Controllers\Portal\EducationController::class, 'createSession'])->name('ministry.education.sessions.store');
                Route::delete('/{eduClass}/sessions/{eduSession}', [\App\Http\Controllers\Portal\EducationController::class, 'destroySession'])->name('ministry.education.sessions.destroy');
                Route::delete('/{eduClass}/sessions', [\App\Http\Controllers\Portal\EducationController::class, 'bulkDestroySession'])->name('ministry.education.sessions.bulk-destroy');
                // Session Detail & Attendance
                Route::get('/{eduClass}/session/{eduSession}', [\App\Http\Controllers\Portal\EducationController::class, 'sessionById'])->name('ministry.education.session.view');
                Route::put('/{eduClass}/session/{eduSession}', [\App\Http\Controllers\Portal\EducationController::class, 'updateSession'])->name('ministry.education.session.update');
                Route::post('/{eduClass}/session/{eduSession}/attendance', [\App\Http\Controllers\Portal\EducationController::class, 'saveAttendance'])->name('ministry.education.attendance.save');
            });
            
            // Tính năng thu quỹ (education-offering)
            Route::middleware('portal.access:education-offering,ministry')->group(function () {
                Route::post('/{eduClass}/session/{eduSession}/offering', [\App\Http\Controllers\Portal\EducationController::class, 'storeOffering'])->name('ministry.education.offering.store');
                Route::delete('/{eduClass}/session/{eduSession}/offering/{transaction}', [\App\Http\Controllers\Portal\EducationController::class, 'destroyOffering'])->name('ministry.education.offering.destroy');
            });

            // Tính năng báo cáo (education-report)
            Route::middleware('portal.access:education-report,ministry')->group(function () {
                Route::get('/report', [\App\Http\Controllers\Portal\EducationController::class, 'report'])->name('ministry.education.report');
                Route::post('/report/save', [\App\Http\Controllers\Portal\EducationController::class, 'saveReport'])->name('ministry.education.report.save');
                Route::post('/report/{eduReport}/approve', [\App\Http\Controllers\Portal\EducationController::class, 'approveReport'])->name('ministry.education.report.approve');
            });
        });
    });


    // Removed duplicate Education standalone portal, use ministry/education instead
    // Finance Portal
    Route::prefix('finance-portal')->middleware(\App\Http\Middleware\EnsureFinanceContext::class)->group(function () {
        Route::get('/', [\App\Http\Controllers\Portal\FinancePortalController::class, 'index'])->name('finance.index');
        Route::post('/switch-context', [\App\Http\Controllers\Portal\FinancePortalController::class, 'switchContext'])->name('finance.switch-context');
        
        // Funds & Transactions
        Route::resource('funds', \App\Http\Controllers\Portal\FinanceFundController::class)->names('finance.funds');
        Route::resource('transactions', \App\Http\Controllers\Portal\FinanceTransactionController::class)->names('finance.transactions');
        Route::post('transactions/{transaction}/approve', [\App\Http\Controllers\Portal\FinanceTransactionController::class, 'approve'])->name('finance.transactions.approve');
        
        // Fund Transfers
        Route::post('transfers', [\App\Http\Controllers\Portal\FinanceFundTransferController::class, 'store'])->name('finance.transfers.store');
        Route::post('transfers/{fundTransfer}/approve', [\App\Http\Controllers\Portal\FinanceFundTransferController::class, 'approve'])->name('finance.transfers.approve');
        Route::delete('transfers/{fundTransfer}', [\App\Http\Controllers\Portal\FinanceFundTransferController::class, 'destroy'])->name('finance.transfers.destroy');
        
        // Reports
        Route::get('/reports', [\App\Http\Controllers\Portal\FinanceReportController::class, 'index'])->name('finance.reports.index');
    });


    // Deacon Board Portal — Ban Chấp Sự (Thư Ký + Thủ Quỹ)
    Route::prefix('deacon')->middleware(\App\Http\Middleware\EnsureDeaconContext::class)->group(function () {
        Route::get('/', [\App\Http\Controllers\Portal\DeaconPortalController::class, 'index'])->name('deacon.index');
        Route::post('/switch-role', [\App\Http\Controllers\Portal\DeaconPortalController::class, 'switchRole'])->name('deacon.switch-role');
        Route::get('/attendance', [\App\Http\Controllers\Portal\DeaconPortalController::class, 'attendance'])->name('deacon.attendance');
        Route::get('/attendance/{meeting}', [\App\Http\Controllers\Portal\DeaconPortalController::class, 'attendanceShow'])->name('deacon.attendance.show');
        Route::post('/attendance/{meeting}', [\App\Http\Controllers\Portal\DeaconPortalController::class, 'attendanceStore'])->name('deacon.attendance.store');
        // Report
        Route::get('/report', [\App\Http\Controllers\Portal\DeaconPortalController::class, 'report'])->name('deacon.report');
        Route::post('/report', [\App\Http\Controllers\Portal\DeaconPortalController::class, 'reportSave'])->name('deacon.report.save');
        Route::post('/report/{report}/status', [\App\Http\Controllers\Portal\DeaconPortalController::class, 'reportStatusUpdate'])->name('deacon.report.status');
        // Incidents
        Route::post('/report/incidents', [\App\Http\Controllers\Portal\DeaconPortalController::class, 'reportIncidentStore'])->name('deacon.incident.store');
        Route::put('/report/incidents/{incident}', [\App\Http\Controllers\Portal\DeaconPortalController::class, 'reportIncidentUpdate'])->name('deacon.incident.update');
        Route::delete('/report/incidents/{incident}', [\App\Http\Controllers\Portal\DeaconPortalController::class, 'reportIncidentDestroy'])->name('deacon.incident.destroy');
        // Members (tái sử dụng PortalMemberController với context 'deacon')
        Route::get('/members', [\App\Http\Controllers\Portal\PortalMemberController::class, 'index'])->name('deacon.members.index');
        Route::get('/members/export', [\App\Http\Controllers\Portal\PortalMemberController::class, 'exportTemplate'])->name('deacon.members.export');
        Route::post('/members/import', [\App\Http\Controllers\Portal\PortalMemberController::class, 'import'])->name('deacon.members.import');
        Route::put('/members/{member}/role', [\App\Http\Controllers\Portal\PortalMemberController::class, 'updateRole'])->name('deacon.members.update-role');
        Route::post('/members/{member}/generate-account', [\App\Http\Controllers\Portal\PortalMemberController::class, 'createUserAccount'])->name('deacon.members.generate-account');
        Route::delete('/members/{member}', [\App\Http\Controllers\Portal\PortalMemberController::class, 'removeMember'])->name('deacon.members.remove');
        Route::post('/members/bulk-assign-team', [\App\Http\Controllers\Portal\PortalMemberController::class, 'bulkAssignTeam'])->name('deacon.members.bulk-assign-team');
        Route::post('/members/bulk-remove', [\App\Http\Controllers\Portal\PortalMemberController::class, 'bulkRemove'])->name('deacon.members.bulk-remove');

        // Phân công
        Route::middleware('portal.access:assignments,leadership')->group(function () {
            Route::prefix('duty-rooster')->name('deacon.duty-rooster.')->group(function () {
                Route::get('/', [\App\Http\Controllers\DutyRosterController::class, 'index'])->name('index');
                Route::post('/assignments', [\App\Http\Controllers\DutyRosterController::class, 'storeAssignment'])->name('assignments.store');
                Route::post('/copy-week', [\App\Http\Controllers\DutyRosterController::class, 'copyWeek'])->name('copy-week');
                Route::post('/departments/{department}/roles', [\App\Http\Controllers\DutyRosterController::class, 'storeRole'])->name('roles.store');
                Route::delete('/roles/{role}', [\App\Http\Controllers\DutyRosterController::class, 'destroyRole'])->name('roles.destroy');

                Route::get('/templates', [\App\Http\Controllers\DutyRosterController::class, 'templatesIndex'])->name('templates.index');
                Route::get('/templates/create', [\App\Http\Controllers\DutyRosterController::class, 'templateCreate'])->name('templates.create');
                Route::get('/templates/{template}', [\App\Http\Controllers\DutyRosterController::class, 'templateShow'])->name('templates.show');
                Route::post('/templates', [\App\Http\Controllers\DutyRosterController::class, 'storeTemplate'])->name('templates.store');
                Route::put('/templates/{template}', [\App\Http\Controllers\DutyRosterController::class, 'updateTemplate'])->name('templates.update');
                Route::post('/templates/apply', [\App\Http\Controllers\DutyRosterController::class, 'applyTemplate'])->name('templates.apply');
                Route::delete('/templates/{template}', [\App\Http\Controllers\DutyRosterController::class, 'deleteTemplate'])->name('templates.destroy');
                Route::post('/templates/{template}/roles', [\App\Http\Controllers\DutyRosterController::class, 'addTemplateRole'])->name('templates.roles.add');
                Route::delete('/templates/{template}/roles/{role}', [\App\Http\Controllers\DutyRosterController::class, 'removeTemplateRole'])->name('templates.roles.remove');

                Route::get('/{meeting}/export', [\App\Http\Controllers\DutyRosterController::class, 'exportMeeting'])->name('export');
                Route::get('/{meeting}', [\App\Http\Controllers\DutyRosterController::class, 'show'])->name('show');
            });
        });
    });

    // Old Duty Roster (To be removed later if obsolete)
    Route::prefix('duty-rooster')->middleware(\App\Http\Middleware\EnsureSuperAdmin::class)->group(function () {
        Route::get('/', [\App\Http\Controllers\DutyRosterController::class, 'index'])->name('duty-rooster.index');
        Route::post('/assignments', [\App\Http\Controllers\DutyRosterController::class, 'storeAssignment'])->name('duty-rooster.assignments.store');
        Route::post('/copy-week', [\App\Http\Controllers\DutyRosterController::class, 'copyWeek'])->name('duty-rooster.copy-week');
        Route::post('/departments/{department}/roles', [\App\Http\Controllers\DutyRosterController::class, 'storeRole'])->name('duty-rooster.roles.store');
        Route::delete('/roles/{role}', [\App\Http\Controllers\DutyRosterController::class, 'destroyRole'])->name('duty-rooster.roles.destroy');

        // Templates (CRUD)
        Route::get('/templates', [\App\Http\Controllers\DutyRosterController::class, 'templatesIndex'])->name('duty-rooster.templates.index');
        Route::get('/templates/create', [\App\Http\Controllers\DutyRosterController::class, 'templateCreate'])->name('duty-rooster.templates.create');
        Route::get('/templates/{template}', [\App\Http\Controllers\DutyRosterController::class, 'templateShow'])->name('duty-rooster.templates.show');
        Route::post('/templates', [\App\Http\Controllers\DutyRosterController::class, 'storeTemplate'])->name('duty-rooster.templates.store');
        Route::put('/templates/{template}', [\App\Http\Controllers\DutyRosterController::class, 'updateTemplate'])->name('duty-rooster.templates.update');
        Route::post('/templates/apply', [\App\Http\Controllers\DutyRosterController::class, 'applyTemplate'])->name('duty-rooster.templates.apply');
        Route::delete('/templates/{template}', [\App\Http\Controllers\DutyRosterController::class, 'deleteTemplate'])->name('duty-rooster.templates.destroy');
        // Template role toggles
        Route::post('/templates/{template}/roles', [\App\Http\Controllers\DutyRosterController::class, 'addTemplateRole'])->name('duty-rooster.templates.roles.add');
        Route::delete('/templates/{template}/roles/{role}', [\App\Http\Controllers\DutyRosterController::class, 'removeTemplateRole'])->name('duty-rooster.templates.roles.remove');

        // Meeting show (MUST be last to avoid conflict with /templates)
        Route::get('/{meeting}/export', [\App\Http\Controllers\DutyRosterController::class, 'exportMeeting'])->name('duty-rooster.export');
        Route::get('/{meeting}', [\App\Http\Controllers\DutyRosterController::class, 'show'])->name('duty-rooster.show');
    });


    Route::post('logout', [AuthController::class, 'logout'])->name('logout');
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
