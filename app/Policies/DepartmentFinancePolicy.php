<?php

namespace App\Policies;

use App\Models\User;
use App\Models\UserDepartmentFeature;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * DepartmentFinancePolicy — Kiểm soát quyền tài chính ban ngành.
 * Áp dụng cho DepartmentMeeting và DepartmentTransaction models.
 */
class DepartmentFinancePolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        if ($user->isSuperAdmin()) return true;

        return UserDepartmentFeature::where('user_id', $user->id)
            ->where('is_enabled', true)
            ->whereHas('feature', fn($q) => $q->where('slug', 'finance'))
            ->exists();
    }

    public function view(User $user, $model = null): bool
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
            ->whereHas('feature', fn($q) => $q->where('slug', 'finance'))
            ->exists();
    }

    public function update(User $user, $model = null): bool
    {
        return $this->create($user);
    }

    public function delete(User $user, $model = null): bool
    {
        return $this->create($user);
    }

    public function approve(User $user, $model = null): bool
    {
        // Chỉ SuperAdmin hoặc leader mới duyệt tài chính
        if ($user->isSuperAdmin()) return true;

        $deptId = session('active_portal_dept_id') ?? session('active_ministry_dept_id');
        if (!$deptId) return false;

        return UserDepartmentFeature::where('user_id', $user->id)
            ->where('department_id', $deptId)
            ->where('is_enabled', true)
            ->whereHas('feature', fn($q) => $q->where('slug', 'finance'))
            ->exists();
    }
}
