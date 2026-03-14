<?php

namespace App\Policies;

use App\Models\Member;
use App\Models\User;
use App\Models\Department;
use App\Models\UserDepartmentFeature;
use Illuminate\Auth\Access\Response;

class PortalMemberPolicy
{
    /**
     * View the board members or list of all members in the portal.
     */
    public function portal_view_all_members(User $user, $arg1 = null, $arg2 = null): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        $department = ($arg1 instanceof Department) ? $arg1 : $arg2;
        $deptId = $department?->id ?? session('active_portal_dept_id') ?? session('active_ministry_dept_id');

        if ($deptId && $this->hasMacAccess($user, (int)$deptId, 'members')) {
            return true;
        }

        return $user->isSuperAdmin();
    }

    public function portal_manage_board(User $user, $arg1 = null, $arg2 = null): bool
    {
        if ($user->isSuperAdmin()) return true;

        $department = ($arg1 instanceof Department) ? $arg1 : $arg2;
        $deptId = $department?->id ?? session('active_portal_dept_id') ?? session('active_ministry_dept_id');

        if ($deptId && $this->hasMacAccess($user, (int)$deptId, 'members')) return true;

        return $user->isSuperAdmin();
    }

    public function portal_export_members(User $user, $arg1 = null, $arg2 = null): bool
    {
        if ($user->isSuperAdmin()) return true;

        $department = ($arg1 instanceof Department) ? $arg1 : $arg2;
        $deptId = $department?->id ?? session('active_portal_dept_id') ?? session('active_ministry_dept_id');

        if ($deptId && $this->hasMacAccess($user, (int)$deptId, 'members')) return true;

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
