<?php

namespace App\Policies;

use App\Models\Member;
use App\Models\User;
use App\Models\UserDepartmentFeature;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * PortalMemberPolicy — Kiểm soát quyền quản lý thành viên trong portal.
 * Áp dụng cho Member model.
 */
class PortalMemberPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        if ($user->isSuperAdmin()) return true;

        return UserDepartmentFeature::where('user_id', $user->id)
            ->where('is_enabled', true)
            ->whereHas('feature', fn($q) => $q->where('slug', 'members'))
            ->exists();
    }

    public function view(User $user, Member $member): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        if ($user->isSuperAdmin()) return true;

        $deptId = session('active_portal_dept_id') ?? session('active_ministry_dept_id');
        if (!$deptId) return false;

        return UserDepartmentFeature::where('user_id', $user->id)
            ->where('department_id', $deptId)
            ->where('is_enabled', true)
            ->whereHas('feature', fn($q) => $q->where('slug', 'members'))
            ->exists();
    }

    public function update(User $user, Member $member): bool
    {
        return $this->create($user);
    }

    public function delete(User $user, Member $member): bool
    {
        return $this->create($user);
    }
}
