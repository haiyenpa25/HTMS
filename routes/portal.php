<?php

use Illuminate\Support\Facades\Route;

/**
 * Department Portal — Ban Sinh Hoạt (Activities)
 * Middleware: CheckPortalAccess:activities (block-gate)
 * Feature-level: portal.access:feature,activities (CheckFeatureAccess)
 */
Route::prefix('portal')
    ->middleware(\App\Http\Middleware\CheckPortalAccess::class . ':activities')
    ->group(function () {

    Route::get('/', [\App\Http\Controllers\DepartmentPortalController::class, 'index'])->name('portal.index');
    Route::post('/switch-context', [\App\Http\Controllers\DepartmentPortalController::class, 'switchContext'])->name('portal.switch-context');
    Route::get('/logs', [\App\Http\Controllers\DepartmentPortalController::class, 'logs'])->name('portal.logs');

    // ── Điểm danh ──────────────────────────────────────────
    Route::middleware('portal.access:attendance,activities')->group(function () {
        Route::get('/attendance', [\App\Http\Controllers\Portal\AttendanceController::class, 'index'])->name('portal.attendance.index');
        Route::get('/attendance/{meeting}/export', [\App\Http\Controllers\Portal\AttendanceController::class, 'exportTemplate'])->name('portal.attendance.export');
        Route::post('/attendance/import', [\App\Http\Controllers\Portal\AttendanceController::class, 'import'])->name('portal.attendance.import');
        Route::get('/attendance/{meeting}', [\App\Http\Controllers\Portal\AttendanceController::class, 'show'])->name('portal.attendance.show');
        Route::post('/attendance/{meeting}', [\App\Http\Controllers\Portal\AttendanceController::class, 'store'])->name('portal.attendance.store');
    });

    // ── Thành viên ──────────────────────────────────────────
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
        Route::post('/members/store-pending', [\App\Http\Controllers\Portal\PortalMemberController::class, 'storePending'])->name('portal.members.store-pending');
        Route::post('/members/{member}/approve-pending', [\App\Http\Controllers\Portal\PortalMemberController::class, 'approvePending'])->name('portal.members.approve-pending');
        Route::post('/members/{member}/reject-pending', [\App\Http\Controllers\Portal\PortalMemberController::class, 'rejectPending'])->name('portal.members.reject-pending');
    });

    // ── Phân công / Duty Roster ────────────────────────────
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

    // ── Báo cáo ────────────────────────────────────────────
    Route::middleware('portal.access:reports,activities')->group(function () {
        Route::get('/reports', [\App\Http\Controllers\Portal\DeptReportController::class, 'index'])->name('portal.reports.index');
        Route::post('/reports/save', [\App\Http\Controllers\Portal\DeptReportController::class, 'saveReport'])->name('portal.reports.save');
        Route::post('/reports/{report}/approve', [\App\Http\Controllers\Portal\DeptReportController::class, 'approveReport'])->name('portal.reports.approve');
        Route::get('/reports/export-pdf', [\App\Http\Controllers\Portal\DeptReportController::class, 'exportPdf'])->name('portal.reports.export-pdf');
    });

    // ── Tài chính ──────────────────────────────────────────
    Route::middleware('portal.access:finance,activities')->group(function () {
        Route::get('/finance', [\App\Http\Controllers\Portal\DeptFinanceController::class, 'index'])->name('portal.finance.index');
        Route::post('/finance/meetings/{meeting}/finance', [\App\Http\Controllers\Portal\DeptFinanceController::class, 'storeFinance'])->name('portal.finance.store');
        Route::delete('/finance/meetings/{meeting}/finance', [\App\Http\Controllers\Portal\DeptFinanceController::class, 'deleteFinance'])->name('portal.finance.delete');
        Route::post('/finance/funds', [\App\Http\Controllers\Portal\DeptFinanceController::class, 'storeFund'])->name('portal.finance.funds.store');
        Route::delete('/finance/funds/{fund}', [\App\Http\Controllers\Portal\DeptFinanceController::class, 'destroyFund'])->name('portal.finance.funds.destroy');
    });

    // ── Thăm viếng ────────────────────────────────────────
    Route::middleware('portal.access:visitation,activities')->group(function () {
        Route::get('/visitation', [\App\Http\Controllers\Portal\ActivitiesVisitationController::class, 'index'])->name('portal.visitation.index');
        Route::post('/visitation', [\App\Http\Controllers\Portal\ActivitiesVisitationController::class, 'store'])->name('portal.visitation.store');
        Route::put('/visitation/{visitation}', [\App\Http\Controllers\Portal\ActivitiesVisitationController::class, 'update'])->name('portal.visitation.update');
        Route::delete('/visitation/{visitation}', [\App\Http\Controllers\Portal\ActivitiesVisitationController::class, 'destroy'])->name('portal.visitation.destroy');
    });

    // ── Sổ Tay Ban Ngành ──────────────────────────────────
    Route::middleware('portal.access:chronicles,activities')->group(function () {
        Route::prefix('chronicles')->name('portal.chronicles.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Portal\ChronicleController::class, 'index'])->name('index');
            Route::post('/', [\App\Http\Controllers\Portal\ChronicleController::class, 'store'])->name('store');
            Route::put('/{chronicle}', [\App\Http\Controllers\Portal\ChronicleController::class, 'update'])->name('update');
            Route::delete('/{chronicle}', [\App\Http\Controllers\Portal\ChronicleController::class, 'destroy'])->name('destroy');
        });
    });

    // ── Tài liệu ──────────────────────────────────────────
    Route::middleware('portal.access:documents,activities')->group(function () {
        Route::get('/documents', [\App\Http\Controllers\DocumentController::class, 'index'])->name('portal.documents.index');
        Route::post('/documents', [\App\Http\Controllers\DocumentController::class, 'store'])->name('portal.documents.store');
        Route::delete('/documents/{document}', [\App\Http\Controllers\DocumentController::class, 'destroy'])->name('portal.documents.destroy');
    });

    // ── Chăm Sóc Tín Hữu ─────────────────────────────────
    Route::middleware('portal.access:care,activities')->group(function () {
        Route::get('/care', [\App\Http\Controllers\CareController::class, 'index'])->name('portal.care.index');
        Route::post('/care', [\App\Http\Controllers\CareController::class, 'store'])->name('portal.care.store');
        Route::patch('/care/{careRequest}/status', [\App\Http\Controllers\CareController::class, 'updateStatus'])->name('portal.care.updateStatus');
        Route::patch('/care/{careRequest}/assign', [\App\Http\Controllers\CareController::class, 'assign'])->name('portal.care.assign');
        Route::delete('/care/{careRequest}', [\App\Http\Controllers\CareController::class, 'destroy'])->name('portal.care.destroy');
    });
});
