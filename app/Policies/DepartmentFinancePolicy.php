<?php

namespace App\Policies;

use App\Models\DepartmentTransaction;
use App\Models\DepartmentMeeting;
use App\Models\User;
use App\Models\UserDepartmentFeature;
use Illuminate\Auth\Access\HandlesAuthorization;

class DepartmentFinancePolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        if ($user->hasRole(['Pastor', 'BTS_Admin', 'Super_Admin'])) return true;
        
        return UserDepartmentFeature::where('user_id', $user->id)
            ->where('is_enabled', true)
            ->whereHas('feature', fn($q) => $q->where('slug', 'finance'))
            ->exists();
    }

    public function view(User $user, $arg1 = null, $arg2 = null): bool
    {
        if ($user->hasRole(['Pastor', 'BTS_Admin', 'Super_Admin'])) return true;

        $deptId = session('active_portal_dept_id');
        if ($deptId && $this->hasMacAccess($user, $deptId, 'finance')) return true;

        return $user->hasPermissionTo('view_dept_finance');
    }

    public function create(User $user): bool
    {
        if ($user->hasRole(['Pastor', 'BTS_Admin', 'Super_Admin'])) return true;

        $deptId = session('active_portal_dept_id');
        if ($deptId && $this->hasMacAccess($user, $deptId, 'finance')) return true;

        return $user->hasPermissionTo('manage_dept_finance');
    }

    public function update(User $user, $arg1 = null, $arg2 = null): bool
    {
        return $this->create($user);
    }

    public function delete(User $user, $arg1 = null, $arg2 = null): bool
    {
        return $this->create($user);
    }

    public function manageMeeting(User $user, $arg1 = null, $arg2 = null): bool
    {
        return $this->create($user);
    }

    private function hasMacAccess(User $user, int $deptId, string $slug): bool
    {
        return UserDepartmentFeature::where('user_id', $user->id)
            ->where('department_id', $deptId)
            ->where('is_enabled', true)
            ->whereHas('feature', fn($q) => $q->where('slug', $slug))
            ->exists();
    }
}
