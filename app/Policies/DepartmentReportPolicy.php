<?php

namespace App\Policies;

use App\Models\DepartmentReport;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class DepartmentReportPolicy
{
    use HandlesAuthorization;

    public function viewAny(?User $user = null): bool
    {
        return $user?->hasPermissionTo('view_reports') ?? false;
    }

    public function view(User $user, ?DepartmentReport $report = null): bool
    {
        return $user->hasPermissionTo('view_reports');
    }

    public function create(?User $user = null): bool
    {
        return $user?->hasPermissionTo('create_reports') ?? false;
    }

    public function update(User $user, ?DepartmentReport $report = null): bool
    {
        return $user->hasPermissionTo('create_reports');
    }

    public function approve(User $user, ?DepartmentReport $report = null): bool
    {
        return $user->hasPermissionTo('approve_reports');
    }
}
