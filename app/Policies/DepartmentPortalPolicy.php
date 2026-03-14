<?php

namespace App\Policies;

use App\Models\Department;
use App\Models\User;
use App\Models\UserDepartmentFeature;

class DepartmentPortalPolicy
{
    /**
     * Determine whether the user can access the department portal.
     * Note: Controllers pass [Department::class, $department], so we must handle 
     * the optional class name argument if passed via array.
     */
    public function access_portal(User $user, $arg1 = null, $arg2 = null): bool
    {
        // Handle Laravel's Gate::authorize('ability', [Class::class, $instance]) pattern
        $department = ($arg1 instanceof Department) ? $arg1 : $arg2;

        // 1. Global Admins bypass all checks
        if ($user->isSuperAdmin()) {
            return true;
        }

        // 2. No department specified — check for any enabled MAC permission
        if (!$department) {
            return UserDepartmentFeature::where('user_id', $user->id)
                ->where('is_enabled', true)
                ->exists();
        }

        // 3. MAC path: explicit user_department_features grant
        $hasAnyMacAccess = UserDepartmentFeature::where('user_id', $user->id)
            ->where('department_id', $department->id)
            ->where('is_enabled', true)
            ->exists();

        if ($hasAnyMacAccess) {
            return true;
        }

        // 4. Legacy: OrgMembership path
        return $this->userIsMemberOf($user, $department);
    }

    /**
     * Specifically for managing finances via DeptFinanceController
     */
    public function manage_finance(User $user, $arg1 = null, $arg2 = null): bool
    {
        if ($user->isSuperAdmin()) return true;

        $department = ($arg1 instanceof Department) ? $arg1 : $arg2;
        if (!$department) return false;

        // MAC path
        $hasMac = UserDepartmentFeature::where('user_id', $user->id)
            ->where('department_id', $department->id)
            ->where('is_enabled', true)
            ->whereHas('feature', fn($q) => $q->where('slug', 'finance'))
            ->exists();

        if ($hasMac) return true;

        return $this->userIsMemberOf($user, $department);
    }

    /**
     * Specifically for viewing reports via DeptReportController
     */
    public function view_reports(User $user, $arg1 = null, $arg2 = null): bool
    {
        if ($user->isSuperAdmin()) return true;

        $department = ($arg1 instanceof Department) ? $arg1 : $arg2;
        if (!$department) return false;

        // MAC path
        $hasMac = UserDepartmentFeature::where('user_id', $user->id)
            ->where('department_id', $department->id)
            ->where('is_enabled', true)
            ->whereHas('feature', fn($q) => $q->where('slug', 'reports'))
            ->exists();

        if ($hasMac) return true;

        return $this->userIsMemberOf($user, $department);
    }

    private function userIsMemberOf(User $user, Department $department): bool
    {
        $memberId = $user->member?->id ?? $user->member_id;
        if (!$memberId) return false;

        return \App\Models\OrgMembership::where('member_id', $memberId)
            ->where('model_type', Department::class)
            ->where('model_id', $department->id)
            ->exists();
    }
}
