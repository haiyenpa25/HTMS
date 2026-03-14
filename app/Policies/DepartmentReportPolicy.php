<?php

namespace App\Policies;

use App\Models\DepartmentReport;
use App\Models\User;
use App\Models\UserDepartmentFeature;
use Illuminate\Auth\Access\HandlesAuthorization;

class DepartmentReportPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        if ($user->isSuperAdmin()) return true;
        
        return UserDepartmentFeature::where('user_id', $user->id)
            ->where('is_enabled', true)
            ->whereHas('feature', fn($q) => $q->where('slug', 'reports'))
            ->exists();
    }

    public function view(User $user, $arg1 = null, $arg2 = null): bool
    {
        if ($user->isSuperAdmin()) return true;

        $deptId = session('active_portal_dept_id');
        if ($deptId && $this->hasMacAccess($user, $deptId, 'reports')) return true;

        return $user->isSuperAdmin();
    }

    public function create(User $user): bool
    {
        if ($user->isSuperAdmin()) return true;

        $deptId = session('active_portal_dept_id');
        if ($deptId && $this->hasMacAccess($user, $deptId, 'reports')) return true;

        return $user->isSuperAdmin();
    }

    public function update(User $user, $arg1 = null, $arg2 = null): bool
    {
        return $this->create($user);
    }

    public function approve(User $user, $arg1 = null, $arg2 = null): bool
    {
        if ($user->isSuperAdmin()) return true;

        // NOTE: In some systems, only Lead can approve. 
        // If MAC allows 'reports', we assume they can manage it unless we split 'reports-view' vs 'reports-approve'.
        $deptId = session('active_portal_dept_id');
        if ($deptId && $this->hasMacAccess($user, $deptId, 'reports')) return true;

        return $user->isSuperAdmin();
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
