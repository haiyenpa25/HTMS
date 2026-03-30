<?php

namespace App\Policies;

use App\Models\Department;
use App\Models\User;
use App\Models\UserDepartmentFeature;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * DepartmentPortalPolicy — Kiểm soát quyền xem/quản lý portal ban ngành.
 * Áp dụng cho Department model trong portal context.
 */
class DepartmentPortalPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        if ($user->isSuperAdmin()) return true;

        // User có ít nhất 1 dept được phép trong bất kỳ block nào
        return UserDepartmentFeature::where('user_id', $user->id)
            ->where('is_enabled', true)
            ->exists();
    }

    public function view(User $user, Department $department): bool
    {
        if ($user->isSuperAdmin()) return true;

        return UserDepartmentFeature::where('user_id', $user->id)
            ->where('department_id', $department->id)
            ->where('is_enabled', true)
            ->exists();
    }

    public function create(User $user): bool
    {
        return $user->isSuperAdmin();
    }

    public function update(User $user, Department $department): bool
    {
        return $user->isSuperAdmin();
    }

    public function delete(User $user, Department $department): bool
    {
        return $user->isSuperAdmin();
    }
}
