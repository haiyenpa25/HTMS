<?php

namespace App\Policies;

use App\Models\Department;
use App\Models\Meeting;
use App\Models\OrgMembership;
use App\Models\User;
use App\Models\UserDepartmentFeature;

class AttendancePolicy
{
    /**
     * Handle [Meeting::class, $meeting] or just $meeting
     */
    public function view_attendance(User $user, $arg1 = null, $arg2 = null): bool
    {
        if ($user->hasRole(['Pastor', 'BTS_Admin', 'Super_Admin'])) return true;
        
        $meeting = ($arg1 instanceof Meeting) ? $arg1 : $arg2;
        if (!$meeting) return false;

        // MAC path
        if ($this->hasMacAccess($user, $meeting->department_id, 'attendance')) return true;

        // Legacy OrgMembership path
        $memberId = $user->member?->id ?? $user->member_id;
        if ($memberId && $user->hasRole(['Department_Lead', 'Team_Lead'])) {
            return OrgMembership::where('member_id', $memberId)
                ->where('model_type', Department::class)
                ->where('model_id', $meeting->department_id)
                ->exists();
        }

        return false;
    }

    public function mark_attendance(User $user, $arg1 = null, $arg2 = null): bool
    {
        if ($user->hasRole(['Pastor', 'BTS_Admin', 'Super_Admin'])) return true;
        
        $meeting = ($arg1 instanceof Meeting) ? $arg1 : $arg2;
        if (!$meeting) return false;

        // Church-wide meeting
        if (is_null($meeting->department_id) && $user->hasRole(['Department_Lead', 'Team_Lead'])) {
            return true;
        }

        // MAC path
        if ($this->hasMacAccess($user, $meeting->department_id, 'attendance')) return true;

        // Legacy
        $memberId = $user->member?->id ?? $user->member_id;
        if ($memberId && $user->hasRole(['Department_Lead', 'Team_Lead'])) {
            return OrgMembership::where('member_id', $memberId)
                ->where('model_type', Department::class)
                ->where('model_id', $meeting->department_id)
                ->exists();
        }

        return false;
    }

    public function bypass_attendance_lock(User $user): bool
    {
        return $user->hasRole(['Pastor', 'BTS_Admin', 'Super_Admin']);
    }

    private function hasMacAccess(User $user, ?int $deptId, string $slug): bool
    {
        if (!$deptId) return false;
        return UserDepartmentFeature::where('user_id', $user->id)
            ->where('department_id', $deptId)
            ->where('is_enabled', true)
            ->whereHas('feature', fn($q) => $q->where('slug', $slug))
            ->exists();
    }
}
