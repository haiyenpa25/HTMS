<?php

namespace App\Policies;

use App\Models\Department;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class DepartmentPortalPolicy
{
    /**
     * Determine whether the user can access the department portal.
     * We use a nullable $department parameter to prevent missing argument errors 
     * when the system blindly injects it or calls without it initially.
     */
    public function access_portal(User $user, ?Department $department = null): bool
    {
        // 1. Global Admins can access any portal
        if ($user->hasRole(['Pastor', 'BTS_Admin', 'Super_Admin'])) {
            return true;
        }

        // 2. If no department is provided (e.g. checking global permission), 
        // they must at least have the baseline portal access permission
        if (!$department) {
            return $user->hasPermissionTo('access_department_portal');
        }

        // 3. Must be a member of this specific department
        $memberId = $user->member_id;
        if (!$memberId) {
            return false;
        }

        return \App\Models\OrgMembership::where('member_id', $memberId)
            ->where('model_type', Department::class)
            ->where('model_id', $department->id)
            ->exists();
    }
}

