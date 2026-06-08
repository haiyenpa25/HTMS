<?php

namespace App\Http\Middleware;

use App\Models\Department;
use App\Models\User;

/**
 * P4/Activities — Cổng Ban Ngành Sinh Hoạt (Activities Portal)
 * Cho phép: SuperAdmin + thành viên bất kỳ ban ngành block=activities
 *           hoặc được cấp MAC feature trong activities dept
 */
class EnsurePortalContext extends AbstractPortalMiddleware
{
    protected function getPortalType(): string        { return 'activities'; }
    protected function getBlock(): string             { return 'activities'; }
    protected function getAllowedOrgRoleCodes(): array { return []; } // dùng dept membership
    protected function getPortalDisplayName(): string { return 'Cổng Ban Ngành'; }
}
