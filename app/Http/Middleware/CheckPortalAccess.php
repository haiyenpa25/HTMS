<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Department;
use App\Models\OrgMembership;

/**
 * CheckPortalAccess — Middleware thống nhất kiểm tra & thiết lập context
 * cho tất cả 3 portal: /portal (activities), /ministry (ministry), /deacon (leadership).
 */
class CheckPortalAccess
{
    /** Email Super Admin bypass tất cả kiểm tra */
    const SUPER_ADMIN_EMAIL = 'superadmin@httlthanhmyloi.com';

    /** Mapping portal type → block trong bảng departments */
    const BLOCK_MAP = [
        'activities' => 'activities',
        'ministry'   => 'ministry',
        'deacon'     => 'leadership',
    ];

    /** Session keys per portal */
    const SESSION_DEPT_KEY = [
        'activities' => 'active_portal_dept_id',
        'ministry'   => 'active_ministry_dept_id',
        'deacon'     => 'active_deacon_dept_id',
    ];

    /**
     * @param  string  $portalType  activities | ministry | deacon
     */
    public function handle(Request $request, Closure $next, string $portalType = 'activities'): Response
    {
        $user = $request->user();
        if (!$user) return redirect()->route('login');

        // ── Super Admin bypass ────────────────────────────────────────────
        $isSuperAdmin = ($user->email === self::SUPER_ADMIN_EMAIL)
            || $user->hasRole('Super_Admin');

        if ($isSuperAdmin) {
            $this->ensureSessionContext($request, $user, $portalType, true);
            return $next($request);
        }

        // ── Regular users: Kiểm tra membership ──────────────────────────
        $member = $user->member;
        if (!$member) {
            abort(403, 'Tài khoản chưa được liên kết với Tín hữu. Vui lòng liên hệ Quản trị viên.');
        }

        $block = self::BLOCK_MAP[$portalType] ?? 'activities';
        $sessionKey = self::SESSION_DEPT_KEY[$portalType] ?? 'active_portal_dept_id';
        $activeDeptId = session($sessionKey);

        // Kiểm tra user có membership ở block này không
        $memberships = OrgMembership::where('member_id', $member->id)
            ->where('is_active', true)
            ->whereHasMorph('model', [Department::class], fn ($q) => $q->where('block', $block))
            ->get();

        if ($memberships->isEmpty()) {
            abort(403, 'Bạn không có quyền truy cập vào cổng này.');
        }

        // Nếu chưa có active dept, chọn cái đầu tiên
        if (!$activeDeptId || !$memberships->contains('model_id', $activeDeptId)) {
            $activeDeptId = $memberships->first()->model_id;
            session([$sessionKey => $activeDeptId]);
        }

        return $next($request);
    }

    private function ensureSessionContext(Request $request, $user, string $portalType, bool $isSuperAdmin): void
    {
        $block = self::BLOCK_MAP[$portalType] ?? 'activities';
        $sessionKey = self::SESSION_DEPT_KEY[$portalType] ?? 'active_portal_dept_id';

        if (!session()->has($sessionKey)) {
            $firstDept = Department::where('block', $block)->orderBy('name')->first();
            if ($firstDept) {
                session([$sessionKey => $firstDept->id]);
            }
        }
    }
}
