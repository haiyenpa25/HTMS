<?php

namespace App\Policies;

use App\Models\EduClass;
use App\Models\User;

class EduClassPolicy
{
    /**
     * Global admin & Pastor bypass all checks.
     */
    public function before(User $user, string $ability): ?bool
    {
        if ($user->hasRole(['Super_Admin', 'Pastor'])) {
            return true;
        }
        return null;
    }

    /**
     * View list of classes — anyone with view_edu_classes or a teacher/student of any class.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('view_edu_classes')
            || $this->isAnyClassMember($user);
    }

    /**
     * View a specific class — must be a member OR have view_edu_classes.
     */
    public function view(User $user, EduClass $class): bool
    {
        return $user->hasPermissionTo('view_edu_classes')
            || $this->isClassMember($user, $class);
    }

    /**
     * Create/manage classes — only with manage_edu_classes.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('manage_edu_classes');
    }

    public function update(User $user, EduClass $class): bool
    {
        return $user->hasPermissionTo('manage_edu_classes');
    }

    public function delete(User $user, EduClass $class): bool
    {
        return $user->hasPermissionTo('manage_edu_classes');
    }

    /**
     * Mark attendance — only teachers of THAT specific class.
     */
    public function markAttendance(User $user, EduClass $class): bool
    {
        if (!$user->member_id) return false;
        return $class->hasTeacher($user->member_id);
    }

    /**
     * Record offering/transaction — only teachers of that class.
     */
    public function recordOffering(User $user, EduClass $class): bool
    {
        if (!$user->member_id) return false;
        return $class->hasTeacher($user->member_id);
    }

    // ── Private helpers ──────────────────────────────────────────────

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
