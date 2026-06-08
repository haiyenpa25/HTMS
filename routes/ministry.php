<?php

use Illuminate\Support\Facades\Route;

/**
 * Ministry Portal — Mục Vụ (/ministry/*)
 * Finance Portal — Tài Chính (/finance-portal/*)
 * Deacon Portal — Chấp Sự (/deacon/*)
 */

// ════════════════════════════════════════════════════════
// Ministry Portal
// ════════════════════════════════════════════════════════
Route::prefix('ministry')
    ->middleware(\App\Http\Middleware\EnsureMinistryContext::class)
    ->group(function () {

    Route::get('/', [\App\Http\Controllers\MinistryPortalController::class, 'index'])->name('ministry.index');
    Route::post('/switch-context', [\App\Http\Controllers\MinistryPortalController::class, 'switchContext'])->name('ministry.switch-context');
    Route::get('/logs', [\App\Http\Controllers\MinistryPortalController::class, 'logs'])->name('ministry.logs');

    // ── Thăm viếng (MAC V2) ────────────────────────────────
    Route::middleware('portal.access:visitation,ministry')->group(function () {
        Route::get('/visitation', [\App\Http\Controllers\Portal\VisitationController::class, 'index'])->name('ministry.visitation.index');
        Route::post('/visitation', [\App\Http\Controllers\Portal\VisitationController::class, 'store'])->name('ministry.visitation.store');
        Route::put('/visitation/{visitation}', [\App\Http\Controllers\Portal\VisitationController::class, 'update'])->name('ministry.visitation.update');
        Route::patch('/visitation/{visitation}/complete', [\App\Http\Controllers\Portal\VisitationController::class, 'quickComplete'])->name('ministry.visitation.quick-complete');
        Route::delete('/visitation/{visitation}', [\App\Http\Controllers\Portal\VisitationController::class, 'destroy'])->name('ministry.visitation.destroy');
    });

    // ── Thành viên (MAC V2) ────────────────────────────────
    Route::middleware('portal.access:members,ministry')->group(function () {
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
    });

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

    Route::middleware('portal.access:chronicles,ministry')->group(function () {
        Route::prefix('chronicles')->name('ministry.chronicles.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Portal\ChronicleController::class, 'index'])->name('index');
            Route::post('/', [\App\Http\Controllers\Portal\ChronicleController::class, 'store'])->name('store');
            Route::put('/{chronicle}', [\App\Http\Controllers\Portal\ChronicleController::class, 'update'])->name('update');
            Route::delete('/{chronicle}', [\App\Http\Controllers\Portal\ChronicleController::class, 'destroy'])->name('destroy');
        });
    });

    Route::middleware('portal.access:documents,ministry')->group(function () {
        Route::get('/documents', [\App\Http\Controllers\DocumentController::class, 'index'])->name('ministry.documents.index');
        Route::post('/documents', [\App\Http\Controllers\DocumentController::class, 'store'])->name('ministry.documents.store');
        Route::delete('/documents/{document}', [\App\Http\Controllers\DocumentController::class, 'destroy'])->name('ministry.documents.destroy');
    });

    Route::middleware('portal.access:care,ministry')->group(function () {
        Route::get('/care', [\App\Http\Controllers\CareController::class, 'index'])->name('ministry.care.index');
        Route::post('/care', [\App\Http\Controllers\CareController::class, 'store'])->name('ministry.care.store');
        Route::patch('/care/{careRequest}/status', [\App\Http\Controllers\CareController::class, 'updateStatus'])->name('ministry.care.update-status');
        Route::patch('/care/{careRequest}/assign', [\App\Http\Controllers\CareController::class, 'assign'])->name('ministry.care.assign');
        Route::delete('/care/{careRequest}', [\App\Http\Controllers\CareController::class, 'destroy'])->name('ministry.care.destroy');
    });

    // ── Giáo Dục ─────────────────────────────────────────
    Route::prefix('education')->group(function () {
        Route::get('/', [\App\Http\Controllers\Portal\EducationController::class, 'dashboard'])->name('ministry.education.index');

        Route::middleware('portal.access:education-classes,ministry')->group(function () {
            Route::get('/classes', [\App\Http\Controllers\Portal\EducationController::class, 'index'])->name('ministry.education.classes');
            Route::post('/', [\App\Http\Controllers\Portal\EducationController::class, 'store'])->name('ministry.education.store');
            Route::put('/{eduClass}', [\App\Http\Controllers\Portal\EducationController::class, 'update'])->name('ministry.education.update');
            Route::delete('/{eduClass}', [\App\Http\Controllers\Portal\EducationController::class, 'destroy'])->name('ministry.education.destroy');
            Route::post('/{eduClass}/members', [\App\Http\Controllers\Portal\EducationController::class, 'storeMember'])->name('ministry.education.members.store');
            Route::post('/{eduClass}/members/bulk', [\App\Http\Controllers\Portal\EducationController::class, 'bulkStoreMember'])->name('ministry.education.members.bulk-store');
            Route::delete('/{eduClass}/members/{member}', [\App\Http\Controllers\Portal\EducationController::class, 'destroyMember'])->name('ministry.education.members.destroy');
        });

        Route::middleware('portal.access:education-attendance,ministry')->group(function () {
            Route::get('/{eduClass}/sessions', [\App\Http\Controllers\Portal\EducationController::class, 'sessions'])->name('ministry.education.sessions');
            Route::post('/{eduClass}/sessions', [\App\Http\Controllers\Portal\EducationController::class, 'createSession'])->name('ministry.education.sessions.store');
            Route::delete('/{eduClass}/sessions/{eduSession}', [\App\Http\Controllers\Portal\EducationController::class, 'destroySession'])->name('ministry.education.sessions.destroy');
            Route::delete('/{eduClass}/sessions', [\App\Http\Controllers\Portal\EducationController::class, 'bulkDestroySession'])->name('ministry.education.sessions.bulk-destroy');
            Route::get('/{eduClass}/session/{eduSession}', [\App\Http\Controllers\Portal\EducationController::class, 'sessionById'])->name('ministry.education.session.view');
            Route::put('/{eduClass}/session/{eduSession}', [\App\Http\Controllers\Portal\EducationController::class, 'updateSession'])->name('ministry.education.session.update');
            Route::post('/{eduClass}/session/{eduSession}/attendance', [\App\Http\Controllers\Portal\EducationController::class, 'saveAttendance'])->name('ministry.education.attendance.save');
        });

        Route::middleware('portal.access:education-offering,ministry')->group(function () {
            Route::post('/{eduClass}/session/{eduSession}/offering', [\App\Http\Controllers\Portal\EducationController::class, 'storeOffering'])->name('ministry.education.offering.store');
            Route::delete('/{eduClass}/session/{eduSession}/offering/{transaction}', [\App\Http\Controllers\Portal\EducationController::class, 'destroyOffering'])->name('ministry.education.offering.destroy');
        });

        Route::middleware('portal.access:education-report,ministry')->group(function () {
            Route::get('/report', [\App\Http\Controllers\Portal\EducationController::class, 'report'])->name('ministry.education.report');
            Route::post('/report/save', [\App\Http\Controllers\Portal\EducationController::class, 'saveReport'])->name('ministry.education.report.save');
            Route::post('/report/{eduReport}/approve', [\App\Http\Controllers\Portal\EducationController::class, 'approveReport'])->name('ministry.education.report.approve');
        });

        // Bảng xếp hạng quiz trong lớp (xem: chỉ cần có education-classes access)
        Route::middleware('portal.access:education-classes,ministry')->group(function () {
            Route::get('/{eduClass}/ranking', [\App\Http\Controllers\Portal\EduRankingController::class, 'classRanking'])->name('ministry.education.ranking');
        });
    });
});


// ════════════════════════════════════════════════════════
// Finance Portal — Tài Chính Hội Thánh
// ════════════════════════════════════════════════════════
Route::prefix('finance-portal')
    ->middleware(\App\Http\Middleware\EnsureFinanceContext::class)
    ->group(function () {

    Route::get('/', [\App\Http\Controllers\Portal\FinancePortalController::class, 'index'])->name('finance.index');
    Route::post('/switch-context', [\App\Http\Controllers\Portal\FinancePortalController::class, 'switchContext'])->name('finance.switch-context');
    Route::resource('funds', \App\Http\Controllers\Portal\FinanceFundController::class)->names('finance.funds');
    Route::resource('transactions', \App\Http\Controllers\Portal\FinanceTransactionController::class)->names('finance.transactions');
    Route::post('transactions/{transaction}/approve', [\App\Http\Controllers\Portal\FinanceTransactionController::class, 'approve'])->name('finance.transactions.approve');
    Route::post('transfers', [\App\Http\Controllers\Portal\FinanceFundTransferController::class, 'store'])->name('finance.transfers.store');
    Route::post('transfers/{fundTransfer}/approve', [\App\Http\Controllers\Portal\FinanceFundTransferController::class, 'approve'])->name('finance.transfers.approve');
    Route::delete('transfers/{fundTransfer}', [\App\Http\Controllers\Portal\FinanceFundTransferController::class, 'destroy'])->name('finance.transfers.destroy');
    Route::get('/reports', [\App\Http\Controllers\Portal\FinanceReportController::class, 'index'])->name('finance.reports.index');
});

// ════════════════════════════════════════════════════════
// Deacon Portal — Ban Chấp Sự
// ════════════════════════════════════════════════════════
Route::prefix('deacon')
    ->middleware(\App\Http\Middleware\EnsureDeaconContext::class)
    ->group(function () {

    Route::get('/', [\App\Http\Controllers\Portal\DeaconPortalController::class, 'index'])->name('deacon.index');
    Route::post('/switch-role', [\App\Http\Controllers\Portal\DeaconPortalController::class, 'switchRole'])->name('deacon.switch-role');
    Route::get('/attendance', [\App\Http\Controllers\Portal\DeaconPortalController::class, 'attendance'])->name('deacon.attendance');
    Route::get('/attendance/{meeting}', [\App\Http\Controllers\Portal\DeaconPortalController::class, 'attendanceShow'])->name('deacon.attendance.show');
    Route::post('/attendance/{meeting}', [\App\Http\Controllers\Portal\DeaconPortalController::class, 'attendanceStore'])->name('deacon.attendance.store');
    Route::get('/report', [\App\Http\Controllers\Portal\DeaconPortalController::class, 'report'])->name('deacon.report');
    Route::post('/report', [\App\Http\Controllers\Portal\DeaconPortalController::class, 'reportSave'])->name('deacon.report.save');
    Route::post('/report/{report}/status', [\App\Http\Controllers\Portal\DeaconPortalController::class, 'reportStatusUpdate'])->name('deacon.report.status');
    Route::post('/report/incidents', [\App\Http\Controllers\Portal\DeaconPortalController::class, 'reportIncidentStore'])->name('deacon.incident.store');
    Route::put('/report/incidents/{incident}', [\App\Http\Controllers\Portal\DeaconPortalController::class, 'reportIncidentUpdate'])->name('deacon.incident.update');
    Route::delete('/report/incidents/{incident}', [\App\Http\Controllers\Portal\DeaconPortalController::class, 'reportIncidentDestroy'])->name('deacon.incident.destroy');
    Route::get('/members', [\App\Http\Controllers\Portal\PortalMemberController::class, 'index'])->name('deacon.members.index');
    Route::get('/members/export', [\App\Http\Controllers\Portal\PortalMemberController::class, 'exportTemplate'])->name('deacon.members.export');
    Route::post('/members/import', [\App\Http\Controllers\Portal\PortalMemberController::class, 'import'])->name('deacon.members.import');
    Route::put('/members/{member}/role', [\App\Http\Controllers\Portal\PortalMemberController::class, 'updateRole'])->name('deacon.members.update-role');
    Route::post('/members/{member}/generate-account', [\App\Http\Controllers\Portal\PortalMemberController::class, 'createUserAccount'])->name('deacon.members.generate-account');
    Route::delete('/members/{member}', [\App\Http\Controllers\Portal\PortalMemberController::class, 'removeMember'])->name('deacon.members.remove');
    Route::post('/members/bulk-assign-team', [\App\Http\Controllers\Portal\PortalMemberController::class, 'bulkAssignTeam'])->name('deacon.members.bulk-assign-team');
    Route::post('/members/bulk-remove', [\App\Http\Controllers\Portal\PortalMemberController::class, 'bulkRemove'])->name('deacon.members.bulk-remove');

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
