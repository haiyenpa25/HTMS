<?php

namespace App\Policies;

use App\Models\Meeting;
use App\Models\User;
use App\Models\UserDepartmentFeature;
use App\Models\Department;
use App\Models\OrgMembership;
use Illuminate\Auth\Access\Response;

class MeetingPolicy
{
    public function viewAny(User $user): bool
    {
        return true; 
    }

    /**
     * Handle [Meeting::class, $meeting] or just $meeting
     */
    public function view(User $user, $arg1, $arg2 = null): bool
    {
        if ($user->hasRole(['BTS_Admin', 'Pastor', 'Super_Admin'])) {
            return true;
        }

        $meeting = ($arg1 instanceof Meeting) ? $arg1 : $arg2;
        if (!$meeting) return false;

        if (!$meeting->department_id) {
            return true;
        }

        // MAC path
        $hasMac = UserDepartmentFeature::where('user_id', $user->id)
            ->where('department_id', $meeting->department_id)
            ->where('is_enabled', true)
            ->exists();
        if ($hasMac) return true;

        // Legacy OrgMembership
        $memberId = $user->member?->id ?? $user->member_id;
        if ($memberId) {
            return OrgMembership::where('member_id', $memberId)
                ->where('model_type', Department::class)
                ->where('model_id', $meeting->department_id)
                ->exists();
        }

        return false;
    }

    public function create(User $user): bool
    {
        if ($user->hasRole(['BTS_Admin', 'Pastor', 'Super_Admin'])) return true;
        return UserDepartmentFeature::where('user_id', $user->id)
            ->where('is_enabled', true)
            ->exists();
    }

    public function update(User $user, $arg1, $arg2 = null): bool
    {
        return $this->view($user, $arg1, $arg2);
    }

    public function delete(User $user, $arg1, $arg2 = null): bool
    {
        return $this->view($user, $arg1, $arg2);
    }

    public function restore(User $user, $arg1, $arg2 = null): bool
    {
        return false;
    }

    public function forceDelete(User $user, $arg1, $arg2 = null): bool
    {
        return false;
    }

    public function approveFinances(User $user, $arg1, $arg2 = null): bool
    {
        return $user->hasRole(['BTS_Admin', 'Pastor', 'Super_Admin']);
    }
}
