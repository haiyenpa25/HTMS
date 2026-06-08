<?php

namespace App\Http\Middleware;

use App\Services\PortalService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * CheckFeatureAccess — Kiểm tra quyền tính năng cụ thể trong portal.
 *
 * God Mode: isSuperAdmin() → luôn pass.
 * Normal user: Kiểm tra user có quyền feature trong active dept của portal đó không.
 *
 * Usage: Route::middleware('feature.access:attendance,activities')
 *
 * SESSION_KEYS được import từ AbstractPortalMiddleware (single source of truth).
 */
class CheckFeatureAccess
{
    /**
     * Block map: portalType → department block
     */
    private const BLOCK_MAP = [
        'activities' => 'activities',
        'ministry'   => 'ministry',
        'deacon'     => 'leadership',
        'education'  => 'ministry',
        'secretary'  => 'leadership',
        'finance'    => 'activities',
    ];

    /**
     * Route index khi redirect (thiếu quyền feature).
     */
    private const INDEX_ROUTES = [
        'activities' => 'portal.index',
        'ministry'   => 'ministry.index',
        'deacon'     => 'deacon.index',
        'education'  => 'education.index',
        'secretary'  => 'secretary.dashboard',
        'finance'    => 'finance.index',
    ];

    public function __construct(private PortalService $service) {}

    public function handle(Request $request, Closure $next, string $featureSlug, string $portalType = 'activities'): Response
    {
        $user = $request->user();
        if (!$user) {
            return redirect()->route('login');
        }

        // God Mode bypass
        if ($user->isSuperAdmin()) {
            return $next($request);
        }

        $block      = self::BLOCK_MAP[$portalType]  ?? $portalType;
        $sessionKey = AbstractPortalMiddleware::SESSION_KEYS[$portalType] ?? 'active_portal_dept_id';
        $activeDeptId = session($sessionKey);

        // Check active dept first (fast path)
        if ($activeDeptId && $this->service->canAccess($user, (int) $activeDeptId, $featureSlug)) {
            return $next($request);
        }

        // Fallback: scan any dept in this block where user has this feature
        // (Single query — gộp exists + value thành 1)
        $validDeptId = \App\Models\UserDepartmentFeature::where('user_id', $user->id)
            ->where('is_enabled', true)
            ->whereHas('feature', fn ($q) => $q->where('slug', $featureSlug))
            ->whereHas('department', fn ($q) => $q->where('block', $block))
            ->value('department_id');

        if ($validDeptId) {
            session([$sessionKey => $validDeptId]);
            return $next($request);
        }

        // No access anywhere
        $indexRoute = self::INDEX_ROUTES[$portalType] ?? 'dashboard';

        return redirect()->route($indexRoute)->with(
            'error',
            "Bạn chưa được cấp quyền truy cập tính năng \"{$featureSlug}\". Vui lòng liên hệ quản trị viên."
        );
    }
}
