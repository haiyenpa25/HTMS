<?php

namespace App\Policies;

use App\Models\EduClass;
use App\Models\User;
use App\Models\UserDepartmentFeature;

class EduClassPolicy
{
    /**
     * Global admin & Pastor bypass all checks.
     */
    public function before(User $user, string $ability): ?bool
    {
        if ($user->hasRole(['Super_Admin', 'Pastor', 'BTS_Admin'])) {
            return true;
        }
        return null;
    }

    /**
     * View list of classes — anyone with view_edu_classes, a teacher/student, OR MAC access to education-classes.
     */
    public function viewAny(User $user): bool
    {
        if ($user->hasPermissionTo('view_edu_classes')) return true;

        // MAC: has any education-related permission in any department
        $hasMac = UserDepartmentFeature::where('user_id', $user->id)
            ->where('is_enabled', true)
            ->whereHas('feature', fn($q) => $q->where('slug', 'like', 'education-%'))
            ->exists();
        
        if ($hasMac) return true;

        return $this->isAnyClassMember($user);
    }

    /**
     * View a specific class.
     */
    public function view(User $user, $arg1, $arg2 = null): bool
    {
        $class = ($arg1 instanceof EduClass) ? $arg1 : $arg2;
        if (!$class) return false;

        if ($user->hasPermissionTo('view_edu_classes')) return true;

        // MAC
        if ($this->hasMacAccess($user, $class->department_id, 'education-classes')) return true;

        return $this->isClassMember($user, $class);
    }

    public function create(User $user): bool
    {
        if ($user->hasPermissionTo('manage_edu_classes')) return true;
        
        // MAC: allow creation if they have education-classes in ANY department
        return UserDepartmentFeature::where('user_id', $user->id)
            ->where('is_enabled', true)
            ->whereHas('feature', fn($q) => $q->where('slug', 'education-classes'))
            ->exists();
    }

    public function update(User $user, $arg1, $arg2 = null): bool
    {
        $class = ($arg1 instanceof EduClass) ? $arg1 : $arg2;
        if (!$class) return false;

        if ($user->hasPermissionTo('manage_edu_classes')) return true;
        
        return $this->hasMacAccess($user, $class->department_id, 'education-classes');
    }

    public function delete(User $user, $arg1, $arg2 = null): bool
    {
        return $this->update($user, $arg1, $arg2);
    }

    /**
     * Mark attendance.
     */
    public function markAttendance(User $user, $arg1, $arg2 = null): bool
    {
        $class = ($arg1 instanceof EduClass) ? $arg1 : $arg2;
        if (!$class) return false;

        // MAC
        if ($this->hasMacAccess($user, $class->department_id, 'education-attendance')) return true;

        // Legacy: teacher of class
        if (!$user->member_id) return false;
        return $class->hasTeacher($user->member_id);
    }

    /**
     * Record offering/transaction.
     */
    public function recordOffering(User $user, $arg1, $arg2 = null): bool
    {
        $class = ($arg1 instanceof EduClass) ? $arg1 : $arg2;
        if (!$class) return false;

        // MAC
        if ($this->hasMacAccess($user, $class->department_id, 'education-offering')) return true;

        // Legacy: teacher
        if (!$user->member_id) return false;
        return $class->hasTeacher($user->member_id);
    }

    // ── Private helpers ──────────────────────────────────────────────

    private function hasMacAccess(User $user, ?int $deptId, string $slug): bool
    {
        if (!$deptId) return false;
        return UserDepartmentFeature::where('user_id', $user->id)
            ->where('department_id', $deptId)
            ->where('is_enabled', true)
            ->whereHas('feature', fn($q) => $q->where('slug', $slug))
            ->exists();
    }

    private function isClassMember(User $user, EduClass $class): bool
    {
        if (!$user->member_id) return false;
        return $class->classMembers()->where('member_id', $user->member_id)->exists();
    }

    private function isAnyClassMember(User $user): bool
    {
        if (!$user->member_id) return false;
        return \App\Models\EduClassMember::where('member_id', $user->member_id)->exists();
    }
}
