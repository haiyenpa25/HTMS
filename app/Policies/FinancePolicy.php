<?php

namespace App\Policies;

use App\Models\FinanceTransaction;
use App\Models\FundTransfer;
use App\Models\User;
use App\Services\PortalService;

/**
 * FinancePolicy — MAC V2 Only
 * Không dùng Spatie permissions. Tất cả check qua PortalService.
 * view   → canAccess(user, dept, 'finance')
 * manage → canManage(user, dept, 'finance')
 */
class FinancePolicy
{
    private function deptId(): ?int
    {
        return session('active_portal_dept_id')
            ?? session('active_ministry_dept_id')
            ?? session('active_deacon_dept_id');
    }

    private function canView(User $user): bool
    {
        if ($user->isSuperAdmin()) return true;
        $deptId = $this->deptId();
        if (!$deptId) return false;
        return app(PortalService::class)->canAccess($user, $deptId, 'finance');
    }

    private function canWrite(User $user): bool
    {
        if ($user->isSuperAdmin()) return true;
        $deptId = $this->deptId();
        if (!$deptId) return false;
        return app(PortalService::class)->canManage($user, $deptId, 'finance');
    }

    public function viewAny(?User $user = null): bool
    {
        if (!$user) return false;
        return $this->canView($user);
    }

    public function view(User $user, ?FinanceTransaction $transaction = null): bool
    {
        return $this->canView($user);
    }

    public function create(?User $user = null): bool
    {
        if (!$user) return false;
        return $this->canWrite($user);
    }

    public function update(User $user, ?FinanceTransaction $transaction = null): bool
    {
        return $this->canWrite($user);
    }

    public function delete(User $user, ?FinanceTransaction $transaction = null): bool
    {
        return $this->canWrite($user);
    }

    public function approve(User $user, ?FinanceTransaction $transaction = null): bool
    {
        // Approve yêu cầu manage-level
        return $this->canWrite($user);
    }

    public function transfer(User $user, ?FundTransfer $transfer = null): bool
    {
        return $this->canWrite($user);
    }

    public function report(?User $user = null): bool
    {
        if (!$user) return false;
        return $this->canView($user);
    }
}
