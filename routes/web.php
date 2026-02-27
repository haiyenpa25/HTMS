<?php

use Illuminate\Support\Facades\Route;

use Inertia\Inertia;

use App\Http\Controllers\Auth\AuthController;

Route::middleware('guest')->group(function () {
    Route::get('login', [AuthController::class, 'login'])->name('login');
    Route::post('login', [AuthController::class, 'authenticate'])->name('login.authenticate');
});

Route::middleware('auth')->group(function () {
    Route::get('/', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');

    Route::get('api/members', [\App\Http\Controllers\MemberController::class, 'apiIndex'])->name('api.members.index');
    Route::resource('members', \App\Http\Controllers\MemberController::class)->except(['create', 'edit']);
    Route::resource('departments', \App\Http\Controllers\DepartmentController::class)->except(['create', 'edit', 'destroy']);
    Route::delete('departments/{department}', [\App\Http\Controllers\DepartmentController::class, 'destroy'])->name('departments.destroy');
    
    // Departments Sub-features
    Route::post('departments/{department}/teams', [\App\Http\Controllers\DepartmentController::class, 'storeTeam'])->name('departments.teams.store');
    Route::put('departments/{department}/teams/{team}', [\App\Http\Controllers\DepartmentController::class, 'updateTeam'])->name('departments.teams.update');
    Route::delete('departments/{department}/teams/{team}', [\App\Http\Controllers\DepartmentController::class, 'destroyTeam'])->name('departments.teams.destroy');
    
    Route::post('departments/{department}/members', [\App\Http\Controllers\DepartmentController::class, 'assignMember'])->name('departments.members.assign');
    Route::delete('departments/{department}/members/{member}', [\App\Http\Controllers\DepartmentController::class, 'removeMember'])->name('departments.members.remove');
    
    Route::put('departments/{department}/features', [\App\Http\Controllers\DepartmentController::class, 'updateFeatures'])->name('departments.features.update');

    // System Settings (Users & Roles)
    Route::resource('users', \App\Http\Controllers\UserController::class);
    Route::resource('roles', \App\Http\Controllers\RoleController::class);

    Route::post('logout', [AuthController::class, 'logout'])->name('logout');
});


