<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Schema::defaultStringLength(191);

        // Implicitly grant "Super_Admin" role all permissions
        // This works in the app by using gate-related functions like auth()->user->can() and @can()
        \Illuminate\Support\Facades\Gate::before(function ($user, $ability) {
            return $user->hasRole(['Pastor', 'BTS_Admin', 'Super_Admin', 'Super_Admin']) ? true : null;
        });

        \Illuminate\Support\Facades\Gate::policy(\App\Models\Meeting::class, \App\Policies\AttendancePolicy::class);
        \Illuminate\Support\Facades\Gate::policy(\App\Models\Department::class, \App\Policies\DepartmentPortalPolicy::class);
        \Illuminate\Support\Facades\Gate::policy(\App\Models\Member::class, \App\Policies\PortalMemberPolicy::class);
        \Illuminate\Support\Facades\Gate::policy(\App\Models\FinanceTransaction::class, \App\Policies\FinancePolicy::class);
        \Illuminate\Support\Facades\Gate::policy(\App\Models\FundTransfer::class, \App\Policies\FinancePolicy::class);
        \Illuminate\Support\Facades\Gate::policy(\App\Models\DepartmentMeeting::class, \App\Policies\DepartmentFinancePolicy::class);
        \Illuminate\Support\Facades\Gate::policy(\App\Models\DepartmentTransaction::class, \App\Policies\DepartmentFinancePolicy::class);
        \Illuminate\Support\Facades\Gate::policy(\App\Models\DepartmentReport::class, \App\Policies\DepartmentReportPolicy::class);
    }
}

