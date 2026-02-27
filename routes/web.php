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

    Route::resource('members', \App\Http\Controllers\MemberController::class)->only(['index', 'show', 'store', 'update']);
    Route::resource('departments', \App\Http\Controllers\DepartmentController::class)->only(['index', 'show']);

    // System Settings (Users & Roles)
    Route::resource('users', \App\Http\Controllers\UserController::class);
    Route::resource('roles', \App\Http\Controllers\RoleController::class);

    Route::post('logout', [AuthController::class, 'logout'])->name('logout');
});


