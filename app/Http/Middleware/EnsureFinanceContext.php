<?php

namespace App\Http\Middleware;

use App\Models\Department;
use App\Models\User;
use App\Models\UserDepartmentFeature;

/**
 * P3 — Cổng Tài Chính (Finance Portal)
 * Cho phép: SuperAdmin + user được cấp MAC feature 'finance' trong bất kỳ dept nào
 *
 * Finance dùng feature-based access thay vì org_role/membership
 * → Override resolveActiveDepartment + getValidDeptIds
 */
class EnsureFinanceContext extends AbstractPortalMiddleware
{
    protected function getPortalType(): string        { return 'finance'; }
    protected function getBlock(): string             { return 'activities'; } // finance spans across all depts
    protected function getAllowedOrgRoleCodes(): array { return []; }
    protected function getPortalDisplayName(): string { return 'Cổng Tài Chính'; }

    /**
     * Finance: lấy dept IDs mà user có feature 'finance' enabled.
     */
    protected function getValidDeptIds(User $user): array
    {
        return UserDepartmentFeature::where('user_id', $user->id)
            ->where('is_enabled', true)
            ->whereHas('feature', fn ($q) => $q->where('slug', 'finance'))
            ->pluck('department_id')
            ->toArray();
    }

    /**
     * Finance: active dept từ session, validate còn trong valid list.
     */
    protected function resolveActiveDepartment(User $user, bool $isAdmin): ?Department
    {
        $sessionKey = $this->getSessionKey();
        $activeDeptId = session($sessionKey);

        if ($isAdmin) {
            if (!$activeDeptId) {
                $first = Department::first();
                if ($first) session([$sessionKey => $first->id]);
                return $first;
            }
            return Department::find($activeDeptId);
        }

        $validDeptIds = $this->getValidDeptIds($user);

        if ($activeDeptId && in_array($activeDeptId, $validDeptIds)) {
            return Department::find($activeDeptId);
        }

        if (!empty($validDeptIds)) {
            $dept = Department::find($validDeptIds[0]);
            session([$sessionKey => $validDeptIds[0]]);
            return $dept;
        }

        return null;
    }
}
