<?php

namespace App\Http\Middleware;

/**
 * P5 — Cổng Mục Vụ (Ministry Portal)
 * Cho phép: SuperAdmin + thành viên block=ministry
 *           hoặc được cấp MAC feature trong ministry dept
 */
class EnsureMinistryContext extends AbstractPortalMiddleware
{
    protected function getPortalType(): string        { return 'ministry'; }
    protected function getBlock(): string             { return 'ministry'; }
    protected function getAllowedOrgRoleCodes(): array { return []; } // dùng dept membership
    protected function getPortalDisplayName(): string { return 'Cổng Mục Vụ'; }
}
