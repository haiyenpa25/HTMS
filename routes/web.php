<?php

use Illuminate\Support\Facades\Route;

use Inertia\Inertia;

use App\Http\Controllers\Auth\AuthController;

Route::middleware('guest')->group(function () {
    Route::get('login', [AuthController::class, 'login'])->name('login');
    Route::post('login', [AuthController::class, 'authenticate'])->name('login.authenticate');
});

Route::middleware('auth')->group(function () {
    Route::get('/', [\App\Http\Controllers\DashboardController::class, 'index'])->name('dashboard');

    Route::get('api/members', [\App\Http\Controllers\MemberController::class, 'apiIndex'])->name('api.members.index');
    Route::resource('members', \App\Http\Controllers\MemberController::class)->except(['create', 'edit']);
    Route::patch('members/update-status', [\App\Http\Controllers\MemberController::class, 'updateStatus'])->name('members.update-status');
    Route::resource('departments', \App\Http\Controllers\DepartmentController::class)->except(['create', 'edit', 'destroy']);
    Route::delete('departments/{department}', [\App\Http\Controllers\DepartmentController::class, 'destroy'])->name('departments.destroy');
    
    // Departments Sub-features
    Route::post('departments/{department}/teams', [\App\Http\Controllers\DepartmentController::class, 'storeTeam'])->name('departments.teams.store');
    Route::put('departments/{department}/teams/{team}', [\App\Http\Controllers\DepartmentController::class, 'updateTeam'])->name('departments.teams.update');
    Route::delete('departments/{department}/teams/{team}', [\App\Http\Controllers\DepartmentController::class, 'destroyTeam'])->name('departments.teams.destroy');
    
    Route::post('departments/{department}/members', [\App\Http\Controllers\DepartmentController::class, 'assignMember'])->name('departments.members.assign');
    Route::delete('departments/{department}/members/{member}', [\App\Http\Controllers\DepartmentController::class, 'removeMember'])->name('departments.members.remove');
    
    Route::put('departments/{department}/features', [\App\Http\Controllers\DepartmentController::class, 'updateFeatures'])->name('departments.features.update');

    // Meetings
    Route::resource('meetings', \App\Http\Controllers\MeetingController::class);

    // Speakers
    Route::get('api/speakers', [\App\Http\Controllers\SpeakerController::class, 'apiIndex'])->name('api.speakers.index');
    Route::resource('speakers', \App\Http\Controllers\SpeakerController::class)->except(['create', 'edit']);

    // System Settings (Users & Roles)
    Route::resource('users', \App\Http\Controllers\UserController::class);
    Route::resource('roles', \App\Http\Controllers\RoleController::class);
    
    Route::prefix('admin')->middleware(\App\Http\Middleware\EnsureSuperAdmin::class)->group(function () {
        Route::get('/users/permissions', [\App\Http\Controllers\Admin\UserPermissionController::class, 'index'])->name('admin.users.permissions');
        // MAC APIs
        Route::get('/users/{user}/permissions', [\App\Http\Controllers\Admin\UserPermissionController::class, 'show'])->name('admin.users.permissions.show');
        Route::post('/users/{user}/permissions/toggle', [\App\Http\Controllers\Admin\UserPermissionController::class, 'toggle'])->name('admin.users.permissions.toggle');
        Route::post('/users/{user}/permissions/roles', [\App\Http\Controllers\Admin\UserPermissionController::class, 'updateRoles'])->name('admin.users.permissions.roles');
        Route::post('/users/{user}/permissions/grant-full', [\App\Http\Controllers\Admin\UserPermissionController::class, 'grantFull'])->name('admin.users.permissions.grant-full');

        // Feature System Configuration (Tab Tính Năng)
        Route::get('/features', [\App\Http\Controllers\Admin\SystemFeatureController::class, 'index'])->name('admin.features.index');
        Route::post('/features/assign', [\App\Http\Controllers\Admin\SystemFeatureController::class, 'assign'])->name('admin.features.assign');
        Route::post('/features/store', [\App\Http\Controllers\Admin\SystemFeatureController::class, 'storeFeature'])->name('admin.features.store');
    });

    // Department Portal (Ban Sinh Hoạt — Activities)
    Route::prefix('portal')->middleware(\App\Http\Middleware\CheckPortalAccess::class . ':activities')->group(function () {
        Route::get('/', [\App\Http\Controllers\DepartmentPortalController::class, 'index'])->name('portal.index');
        Route::post('/switch-context', [\App\Http\Controllers\DepartmentPortalController::class, 'switchContext'])->name('portal.switch-context');
        
        // Điểm danh
        Route::middleware('portal.access:attendance,activities')->group(function () {
            Route::get('/attendance', [\App\Http\Controllers\Portal\AttendanceController::class, 'index'])->name('portal.attendance.index');
            Route::get('/attendance/{meeting}', [\App\Http\Controllers\Portal\AttendanceController::class, 'show'])->name('portal.attendance.show');
            Route::post('/attendance/{meeting}', [\App\Http\Controllers\Portal\AttendanceController::class, 'store'])->name('portal.attendance.store');
        });

        // Thành viên
        Route::middleware('portal.access:members,activities')->group(function () {
            Route::get('/members', [\App\Http\Controllers\Portal\PortalMemberController::class, 'index'])->name('portal.members.index');
            Route::post('/members/{member}/role', [\App\Http\Controllers\Portal\PortalMemberController::class, 'updateRole'])->name('portal.members.update');
            Route::post('/members/bulk-assign-team', [\App\Http\Controllers\Portal\PortalMemberController::class, 'bulkAssignTeam'])->name('portal.members.bulk-assign');
            Route::delete('/members/{member}', [\App\Http\Controllers\Portal\PortalMemberController::class, 'removeMember'])->name('portal.members.remove');
            Route::delete('/members/bulk/remove', [\App\Http\Controllers\Portal\PortalMemberController::class, 'bulkRemove'])->name('portal.members.bulk-remove');
        });

        // Phân công
        Route::middleware('portal.access:assignments,activities')->group(function () {
            Route::get('/assignments', [\App\Http\Controllers\Portal\AssignmentsController::class, 'index'])->name('portal.assignments.index');
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
    });


    // Ministry Portal
    Route::prefix('ministry')->middleware(\App\Http\Middleware\EnsureMinistryContext::class)->group(function () {
        Route::get('/', [\App\Http\Controllers\MinistryPortalController::class, 'index'])->name('ministry.index');
        Route::post('/switch-context', [\App\Http\Controllers\MinistryPortalController::class, 'switchContext'])->name('ministry.switch-context');
        
        // Visitation Module
        Route::get('/visitation', [\App\Http\Controllers\Portal\VisitationController::class, 'index'])->name('ministry.visitation.index');
        Route::post('/visitation', [\App\Http\Controllers\Portal\VisitationController::class, 'store'])->name('ministry.visitation.store');
        Route::put('/visitation/{visitation}', [\App\Http\Controllers\Portal\VisitationController::class, 'update'])->name('ministry.visitation.update');
        Route::delete('/visitation/{visitation}', [\App\Http\Controllers\Portal\VisitationController::class, 'destroy'])->name('ministry.visitation.destroy');

        // Member Management Module
        Route::get('/members', [\App\Http\Controllers\Portal\PortalMemberController::class, 'index'])->name('ministry.members.index');
        Route::post('/members/{member}/role', [\App\Http\Controllers\Portal\PortalMemberController::class, 'updateRole'])->name('ministry.members.update');
        Route::post('/members/bulk-assign-team', [\App\Http\Controllers\Portal\PortalMemberController::class, 'bulkAssignTeam'])->name('ministry.members.bulk-assign');
        Route::delete('/members/{member}', [\App\Http\Controllers\Portal\PortalMemberController::class, 'removeMember'])->name('ministry.members.remove');
        Route::delete('/members/bulk/remove', [\App\Http\Controllers\Portal\PortalMemberController::class, 'bulkRemove'])->name('ministry.members.bulk-remove');

        // ─── Ban Cơ Đốc Giáo Dục — Education sub-portal ──────────────────────
        Route::prefix('education')->group(function () {
            Route::get('/', [\App\Http\Controllers\Portal\EducationController::class, 'dashboard'])->name('ministry.education.index');
            Route::get('/classes', [\App\Http\Controllers\Portal\EducationController::class, 'index'])->name('ministry.education.classes');
            Route::post('/', [\App\Http\Controllers\Portal\EducationController::class, 'store'])->name('ministry.education.store');
            Route::put('/{eduClass}', [\App\Http\Controllers\Portal\EducationController::class, 'update'])->name('ministry.education.update');
            Route::delete('/{eduClass}', [\App\Http\Controllers\Portal\EducationController::class, 'destroy'])->name('ministry.education.destroy');
            // Members
            Route::post('/{eduClass}/members', [\App\Http\Controllers\Portal\EducationController::class, 'storeMember'])->name('ministry.education.members.store');
            Route::post('/{eduClass}/members/bulk', [\App\Http\Controllers\Portal\EducationController::class, 'bulkStoreMember'])->name('ministry.education.members.bulk-store');
            Route::delete('/{eduClass}/members/{member}', [\App\Http\Controllers\Portal\EducationController::class, 'destroyMember'])->name('ministry.education.members.destroy');
            // Sessions
            Route::get('/{eduClass}/sessions', [\App\Http\Controllers\Portal\EducationController::class, 'sessions'])->name('ministry.education.sessions');
            Route::post('/{eduClass}/sessions', [\App\Http\Controllers\Portal\EducationController::class, 'createSession'])->name('ministry.education.sessions.store');
            Route::delete('/{eduClass}/sessions/{eduSession}', [\App\Http\Controllers\Portal\EducationController::class, 'destroySession'])->name('ministry.education.sessions.destroy');
            Route::delete('/{eduClass}/sessions', [\App\Http\Controllers\Portal\EducationController::class, 'bulkDestroySession'])->name('ministry.education.sessions.bulk-destroy');
            // Session Detail
            Route::get('/{eduClass}/session/{eduSession}', [\App\Http\Controllers\Portal\EducationController::class, 'sessionById'])->name('ministry.education.session.view');
            Route::put('/{eduClass}/session/{eduSession}', [\App\Http\Controllers\Portal\EducationController::class, 'updateSession'])->name('ministry.education.session.update');
            Route::post('/{eduClass}/session/{eduSession}/attendance', [\App\Http\Controllers\Portal\EducationController::class, 'saveAttendance'])->name('ministry.education.attendance.save');
            Route::post('/{eduClass}/session/{eduSession}/offering', [\App\Http\Controllers\Portal\EducationController::class, 'storeOffering'])->name('ministry.education.offering.store');
            Route::delete('/{eduClass}/session/{eduSession}/offering/{transaction}', [\App\Http\Controllers\Portal\EducationController::class, 'destroyOffering'])->name('ministry.education.offering.destroy');
            // Report
            Route::get('/report', [\App\Http\Controllers\Portal\EducationController::class, 'report'])->name('ministry.education.report');
            Route::post('/report/save', [\App\Http\Controllers\Portal\EducationController::class, 'saveReport'])->name('ministry.education.report.save');
            Route::post('/report/{eduReport}/approve', [\App\Http\Controllers\Portal\EducationController::class, 'approveReport'])->name('ministry.education.report.approve');
        });
    });


    // Education (CĐGD) Portal — Portal riêng của Ban Cơ Đốc Giáo Dục
    Route::prefix('education')->middleware(\App\Http\Middleware\EnsureMinistryContext::class)->group(function () {
        // Portal Dashboard — Tính năng tổng quan
        Route::get('/', [\App\Http\Controllers\Portal\EducationController::class, 'dashboard'])->name('education.index');
        // Class Management — Danh sách và quản lý lớp
        Route::get('/classes', [\App\Http\Controllers\Portal\EducationController::class, 'index'])->name('education.classes');
        // Class CRUD
        Route::post('/', [\App\Http\Controllers\Portal\EducationController::class, 'store'])->name('education.store');
        Route::put('/{eduClass}', [\App\Http\Controllers\Portal\EducationController::class, 'update'])->name('education.update');
        Route::delete('/{eduClass}', [\App\Http\Controllers\Portal\EducationController::class, 'destroy'])->name('education.destroy');
        // Class Members
        Route::post('/{eduClass}/members', [\App\Http\Controllers\Portal\EducationController::class, 'storeMember'])->name('education.members.store');
        Route::post('/{eduClass}/members/bulk', [\App\Http\Controllers\Portal\EducationController::class, 'bulkStoreMember'])->name('education.members.bulk-store');
        Route::delete('/{eduClass}/members/{member}', [\App\Http\Controllers\Portal\EducationController::class, 'destroyMember'])->name('education.members.destroy');
        // Session Management — Quản lý buổi học (list, create, delete)
        Route::get('/{eduClass}/sessions', [\App\Http\Controllers\Portal\EducationController::class, 'sessions'])->name('education.sessions');
        Route::post('/{eduClass}/sessions', [\App\Http\Controllers\Portal\EducationController::class, 'createSession'])->name('education.sessions.store');
        Route::delete('/{eduClass}/sessions/{eduSession}', [\App\Http\Controllers\Portal\EducationController::class, 'destroySession'])->name('education.sessions.destroy');
        Route::delete('/{eduClass}/sessions', [\App\Http\Controllers\Portal\EducationController::class, 'bulkDestroySession'])->name('education.sessions.bulk-destroy');
        // Session Detail / Focus Mode — Điểm danh + Tiền dâng (theo session ID)
        Route::get('/{eduClass}/session/{eduSession}', [\App\Http\Controllers\Portal\EducationController::class, 'sessionById'])->name('education.session.view');
        Route::put('/{eduClass}/session/{eduSession}', [\App\Http\Controllers\Portal\EducationController::class, 'updateSession'])->name('education.session.update');
        Route::post('/{eduClass}/session/{eduSession}/attendance', [\App\Http\Controllers\Portal\EducationController::class, 'saveAttendance'])->name('education.attendance.save');
        Route::post('/{eduClass}/session/{eduSession}/offering', [\App\Http\Controllers\Portal\EducationController::class, 'storeOffering'])->name('education.offering.store');
        Route::delete('/{eduClass}/session/{eduSession}/offering/{transaction}', [\App\Http\Controllers\Portal\EducationController::class, 'destroyOffering'])->name('education.offering.destroy');
        // Monthly Report
        Route::get('/report', [\App\Http\Controllers\Portal\EducationController::class, 'report'])->name('education.report');
        Route::post('/report/save', [\App\Http\Controllers\Portal\EducationController::class, 'saveReport'])->name('education.report.save');
        Route::post('/report/{eduReport}/approve', [\App\Http\Controllers\Portal\EducationController::class, 'approveReport'])->name('education.report.approve');
    });

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
    });

    Route::post('logout', [AuthController::class, 'logout'])->name('logout');
});
