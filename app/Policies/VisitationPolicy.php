<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Visitation;

class VisitationPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('view_visitations');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, ?Visitation $visitation = null): bool
    {
        if (!$user->hasPermissionTo('view_visitations')) {
            return false;
        }

        if (!$visitation) {
            return true;
        }

        if ($user->hasRole(['Pastor', 'Super_Admin', 'Visitation_Staff'])) {
            return true;
        }

        // Department Leads can only view visitations for their current portal department context
        $activePortalDeptId = session('active_portal_dept_id');
        if ($activePortalDeptId && $visitation->department_id === $activePortalDeptId) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can see sensitive content of a visitation
     */
    public function viewSensitiveContent(User $user, Visitation $visitation): bool
    {
        if ($user->hasPermissionTo('view_sensitive_visitation_content')) {
            return true; // Pastor, Super Admin
        }

        // If the user's member profile was one of the visitors
        if ($user->member) {
            return $visitation->visitors->contains('id', $user->member->id);
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('create_visitation_requests') || $user->hasPermissionTo('manage_visitations');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, ?Visitation $visitation = null): bool
    {
        return $user->hasPermissionTo('manage_visitations');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, ?Visitation $visitation = null): bool
    {
        return $user->hasPermissionTo('manage_visitations');
    }
}

