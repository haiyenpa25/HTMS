<?php

namespace App\Http\Middleware;

use App\Models\Department;
use App\Models\User;

/**
 * P2 — Cổng Thư Ký Hội Thánh (Secretary Portal)
 * Cho phép: SuperAdmin + tín hữu có org_role: tkhu (Thư Ký HT), ptk (Phó TK)
 *
 * Active department là Ban Chấp Sự (BCS) — nơi thư ký sinh hoạt.
 */
class EnsureSecretaryContext extends AbstractPortalMiddleware
{
    protected function getPortalType(): string        { return 'secretary'; }
    protected function getBlock(): string             { return 'leadership'; }
    protected function getPortalDisplayName(): string { return 'Cổng Thư Ký'; }

    /**
     * Chỉ Thư Ký HT và Phó Thư Ký được vào.
     */
    protected function getAllowedOrgRoleCodes(): array
    {
        return ['tkhu', 'ptk'];
    }

    /**
     * Secretary Portal dùng Ban Chấp Sự (BCS).
     */
    protected function resolveActiveDepartment(User $user, bool $isAdmin): ?Department
    {
        return Department::where('code', 'BCS')->first()
            ?? Department::where('block', 'leadership')->first();
    }
}
