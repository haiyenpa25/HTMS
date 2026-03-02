<?php

namespace App\Policies;

use App\Models\DepartmentTransaction;
use App\Models\DepartmentMeeting;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class DepartmentFinancePolicy
{
    use HandlesAuthorization;

    public function viewAny(?User $user = null): bool
    {
        return $user?->hasPermissionTo('view_dept_finance') ?? false;
    }

    public function view(User $user, ?DepartmentMeeting $meeting = null): bool
    {
        return $user->hasPermissionTo('view_dept_finance');
    }

    public function create(?User $user = null): bool
    {
        return $user?->hasPermissionTo('manage_dept_finance') ?? false;
    }

    public function update(User $user, ?DepartmentTransaction $transaction = null): bool
    {
        return $user->hasPermissionTo('manage_dept_finance');
    }

    public function delete(User $user, ?DepartmentTransaction $transaction = null): bool
    {
        return $user->hasPermissionTo('manage_dept_finance');
    }

    public function manageMeeting(User $user, ?DepartmentMeeting $meeting = null): bool
    {
        return $user->hasPermissionTo('manage_dept_finance');
    }
}
