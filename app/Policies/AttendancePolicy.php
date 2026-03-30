<?php

namespace App\Policies;

use App\Models\Meeting;
use App\Models\User;
use App\Services\PortalService;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * AttendancePolicy — Kiểm soát quyền điểm danh theo MAC.
 * Áp dụng cho Meeting model.
 */
class AttendancePolicy
{
    use HandlesAuthorization;

    public function __construct(private PortalService $service) {}

    public function view(User $user, Meeting $meeting): bool
    {
        if ($user->isSuperAdmin()) return true;

        $deptId = session('active_portal_dept_id') ?? session('active_ministry_dept_id');
        if (!$deptId) return false;

        return $this->service->canAccess($user, (int) $deptId, 'attendance');
    }

    public function create(User $user): bool
    {
        if ($user->isSuperAdmin()) return true;

        $deptId = session('active_portal_dept_id') ?? session('active_ministry_dept_id');
        if (!$deptId) return false;

        return $this->service->canAccess($user, (int) $deptId, 'attendance');
    }

    public function update(User $user, Meeting $meeting): bool
    {
        return $this->create($user);
    }

    public function delete(User $user, Meeting $meeting): bool
    {
        return $this->create($user);
    }
}
