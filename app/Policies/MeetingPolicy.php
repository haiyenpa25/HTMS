<?php

namespace App\Policies;

use App\Models\Meeting;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class MeetingPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true; // The scopeAccessibleBy handles row-level filtering already
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Meeting $meeting): bool
    {
        if ($user->hasRole(['BTS_Admin', 'Pastor', 'Super_Admin'])) {
            return true;
        }

        if (!$meeting->department_id) {
            return true;
        }

        return \App\Models\OrgMembership::where('member_id', $user->member_id)
            ->where('model_type', \App\Models\Department::class)
            ->where('model_id', $meeting->department_id)
            ->exists();
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasRole(['BTS_Admin', 'Pastor']) || $user->supervisedDepartments()->exists();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Meeting $meeting): bool
    {
        return $this->view($user, $meeting);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Meeting $meeting): bool
    {
        return $this->view($user, $meeting);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Meeting $meeting): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Meeting $meeting): bool
    {
        return false;
    }

    /**
     * Determine if user can approve meeting finances.
     */
    public function approveFinances(User $user, Meeting $meeting): bool
    {
        return $user->hasRole(['BTS_Admin', 'Pastor']);
    }
}

