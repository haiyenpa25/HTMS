<?php

namespace App\Policies;

use App\Models\Member;
use App\Models\User;
use App\Models\Department;
use Illuminate\Auth\Access\Response;

class PortalMemberPolicy
{
    /**
     * View the board members or list of all members in the portal.
     */
    public function viewAny(User $user, ?Department $department = null): bool
    {
        if ($user->hasRole(['Super_Admin', 'Super_Admin', 'Pastor', 'BTS_Admin'])) {
            return true;
        }

        if ($user->hasRole('Department_Lead') && $department) {
            return $user->member && $user->member->hasOrgRoleIn($department->id, ['TruongBan', 'PhoBan', 'ThuKy', 'ThuQuy', 'UyVien']);
        }

        // Technically, all portal users could be allowed to view members if they have `portal_view_all_members`
        return $user->hasPermissionTo('portal_view_all_members');
    }

    /**
     * Determine whether the user can manage the board for the current department.
     */
    public function update(User $user, ?Member $member = null): bool
    {
        if ($user->hasRole(['Super_Admin', 'Super_Admin', 'Pastor', 'BTS_Admin'])) {
            return true;
        }

        return $user->hasPermissionTo('portal_manage_board');
    }

    /**
     * Export members list for the department.
     */
    public function export(User $user, ?Department $department = null): bool
    {
        if ($user->hasRole(['Super_Admin', 'Super_Admin', 'Pastor', 'BTS_Admin'])) {
            return true;
        }

        return $user->hasPermissionTo('portal_export_members');
    }
}

