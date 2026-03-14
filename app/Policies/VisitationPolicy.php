<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Visitation;
use App\Models\UserDepartmentFeature;

class VisitationPolicy
{
    public function viewAny(User $user): bool
    {
        if ($user->isSuperAdmin()) return true;
        
        // MAC: has any visitation permission
        return UserDepartmentFeature::where('user_id', $user->id)
            ->where('is_enabled', true)
            ->whereHas('feature', fn($q) => $q->where('slug', 'visitation'))
            ->exists();
    }

    public function view(User $user, $arg1 = null, $arg2 = null): bool
    {
        if ($user->isSuperAdmin()) return true;

        $visitation = ($arg1 instanceof Visitation) ? $arg1 : $arg2;
        
        // MAC check
        $deptId = $visitation?->department_id ?? session('active_portal_dept_id');
        if ($deptId && $this->hasMacAccess($user, $deptId, 'visitation')) {
            return true;
        }

        // Legacy Spatie fallback
        if ($user->isSuperAdmin()) {
            if (!$visitation) return true;
            if ($user->isSuperAdmin()) return true;
            if ($visitation->department_id === (int)session('active_portal_dept_id')) return true;
        }

        return false;
    }

    public function viewSensitiveContent(User $user, Visitation $visitation): bool
    {
        if ($user->isSuperAdmin()) return true;
        if ($user->isSuperAdmin()) return true;

        // Visitors can see their own reports
        if ($user->member_id && $visitation->visitors->contains('id', $user->member_id)) {
            return true;
        }

        return false;
    }

    public function create(User $user): bool
    {
        if ($user->isSuperAdmin()) return true;
        
        $deptId = session('active_portal_dept_id');
        return $deptId && $this->hasMacAccess($user, $deptId, 'visitation');
    }

    public function update(User $user, Visitation $visitation = null): bool
    {
        return $this->create($user);
    }

    public function delete(User $user, Visitation $visitation = null): bool
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
