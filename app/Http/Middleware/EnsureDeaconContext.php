<?php

namespace App\Http\Middleware;

use App\Models\Department;
use App\Models\User;

/**
 * P4 — Cổng Chấp Sự (Deacon Portal)
 * Cho phép: SuperAdmin + tín hữu có org_role: cs, tkhu, ptk, tqht, ptq trong Ban Chấp Sự
 *
 * Active department luôn là Ban Chấp Sự (code=BCS, block=leadership).
 */
class EnsureDeaconContext extends AbstractPortalMiddleware
{
    protected function getPortalType(): string        { return 'deacon'; }
    protected function getBlock(): string             { return 'leadership'; }
    protected function getPortalDisplayName(): string { return 'Cổng Chấp Sự'; }

    /**
     * Roles được phép: Chấp Sự, Thư Ký HT, Phó TK, Thủ Quỹ HT, Phó TQ
     */
    protected function getAllowedOrgRoleCodes(): array
    {
        return ['cs', 'tkhu', 'ptk', 'tqht', 'ptq'];
    }

    /**
     * Deacon Portal luôn dùng Ban Chấp Sự (BCS) — không cho chuyển dept.
     */
    protected function resolveActiveDepartment(User $user, bool $isAdmin): ?Department
    {
        // Tìm theo code thay vì hardcode ID
        return Department::where('code', 'BCS')->first()
            ?? Department::where('block', 'leadership')->first();
    }

    /**
     * Thêm activeDeaconRole vào Inertia props.
     */
    protected function extraInertiaProps(User $user, bool $isAdmin): array
    {
        if (!session()->has('active_deacon_role')) {
            session(['active_deacon_role' => 'deacon']);
        }

        return [
            'activeDeaconRole' => session('active_deacon_role', 'deacon'),
        ];
    }
}
