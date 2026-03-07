<?php

namespace App\Http\Middleware;

use App\Models\Department;
use App\Models\UserDepartmentFeature;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * CheckPortalAccess — MAC version.
 * KHÔNG dùng OrgMembership. Chỉ dùng user_department_features.
 * User có bất kỳ feature nào is_enabled=true trong block này → được vào.
 */
class CheckPortalAccess
{
    const BLOCK_MAP = [
        'activities' => 'activities',
        'ministry'   => 'ministry',
        'deacon'     => 'leadership',
    ];

    const SESSION_DEPT_KEY = [
        'activities' => 'active_portal_dept_id',
        'ministry'   => 'active_ministry_dept_id',
        'deacon'     => 'active_deacon_dept_id',
    ];

    public function handle(Request $request, Closure $next, string $portalType = 'activities'): Response
    {
        $user = $request->user();
        if (!$user) return redirect()->route('login');

        // ── God Mode bypass ──────────────────────────────────────────────
        if ($user->isSuperAdmin()) {
            $this->ensureSessionContext($user, $portalType);
            return $next($request);
        }

        $block      = self::BLOCK_MAP[$portalType] ?? 'activities';
        $sessionKey = self::SESSION_DEPT_KEY[$portalType] ?? 'active_portal_dept_id';

        // Lấy dept IDs mà user có ít nhất 1 feature enabled
        $allowedDeptIds = UserDepartmentFeature::where('user_id', $user->id)
            ->where('is_enabled', true)
            ->whereHas('department', fn ($q) => $q->where('block', $block))
            ->pluck('department_id')
            ->unique()
            ->values();

        if ($allowedDeptIds->isEmpty()) {
            abort(403, 'Bạn chưa được cấp quyền truy cập cổng này. Liên hệ quản trị viên.');
        }

        // Auto-set session nếu chưa có hoặc session dept không thuộc allowed list
        $activeDeptId = session($sessionKey);
        if (!$activeDeptId || !$allowedDeptIds->contains($activeDeptId)) {
            $activeDeptId = $allowedDeptIds->first();
            session([$sessionKey => $activeDeptId]);
        }

        return $next($request);
    }

    private function ensureSessionContext($user, string $portalType): void
    {
        $block      = self::BLOCK_MAP[$portalType] ?? 'activities';
        $sessionKey = self::SESSION_DEPT_KEY[$portalType] ?? 'active_portal_dept_id';

        if (!session()->has($sessionKey)) {
            $firstDept = Department::where('block', $block)->orderBy('name')->first();
            if ($firstDept) {
                session([$sessionKey => $firstDept->id]);
            }
        }
    }
}
