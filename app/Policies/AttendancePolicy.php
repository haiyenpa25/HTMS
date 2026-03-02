<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Meeting;
use App\Models\Department;
use App\Models\OrgMembership;

class AttendancePolicy
{
    /**
     * Determine whether the user can view the attendance for the meeting.
     */
    public function view_attendance(User $user, ?Meeting $meeting = null): bool
    {
        if ($user->hasRole(['Pastor', 'BTS_Admin', 'Super_Admin'])) {
            return true;
        }

        if (!$meeting) return false;

        if ($user->hasRole(['Department_Lead', 'Team_Lead'])) {
            // Check if user is a member/leader of this meeting's department
            return OrgMembership::where('member_id', $user->member_id)
                ->where('model_type', Department::class)
                ->where('model_id', $meeting->department_id)
                ->exists();
        }

        return false;
    }

    /**
     * Determine whether the user can mark attendance.
     */
    public function mark_attendance(User $user, ?Meeting $meeting = null): bool
    {
        if ($user->hasRole(['Pastor', 'BTS_Admin', 'Super_Admin'])) {
            return true;
        }

        if (!$meeting) return false;

        // Logic check: if meeting is Church wide (department_id is null), Department_Lead can still mark summary for their department.
        // Thus, if it's a church wide meeting, allow Department_Lead to proceed (controller will restrict what they can submit).
        if (is_null($meeting->department_id) && $user->hasRole(['Department_Lead', 'Team_Lead'])) {
             return true; 
        }

        if ($user->hasRole(['Department_Lead', 'Team_Lead'])) {
            return OrgMembership::where('member_id', $user->member_id)
                ->where('model_type', Department::class)
                ->where('model_id', $meeting->department_id)
                ->exists();
        }

        return false;
    }

    /**
     * Determine whether the user can bypass attendance lock (modify after deadline).
     */
    public function bypass_attendance_lock(User $user): bool
    {
        return $user->hasRole(['Pastor', 'BTS_Admin', 'Super_Admin']);
    }
}

