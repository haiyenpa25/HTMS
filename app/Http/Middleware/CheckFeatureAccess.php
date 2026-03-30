<?php

namespace App\Http\Middleware;

use App\Models\UserDepartmentFeature;
use App\Services\PortalService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * PortalAccessMiddleware — Kiểm tra quyền tính năng cụ thể.
 *
 * God Mode: Super_Admin → luôn pass.
 * Normal user: Kiểm tra xem user có ít nhất 1 dept trong block này
 * có quyền truy cập tính năng đó không.
 *
 * Usage:
 *   Route::middleware('portal.access:attendance,activities')
 */
class CheckFeatureAccess
{
    const SESSION_DEPT_KEY = [
        'activities' => 'active_portal_dept_id',
        'ministry'   => 'active_ministry_dept_id',
        'deacon'     => 'active_deacon_dept_id',
        'education'  => 'active_ministry_dept_id',
    ];

    const BLOCK_MAP = [
        'activities' => 'activities',
        'ministry'   => 'ministry',
        'deacon'     => 'leadership',
        'education'  => 'ministry',
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

        $block       = self::BLOCK_MAP[$portalType] ?? $portalType;
        $sessionKey  = self::SESSION_DEPT_KEY[$portalType] ?? 'active_portal_dept_id';
        $activeDeptId = session($sessionKey);

        // Strategy: try the active dept first, then scan all user depts in this block
        if ($activeDeptId && $this->service->canAccess($user, (int) $activeDeptId, $featureSlug)) {
            return $next($request);
        }

        // Fallback: look for ANY department the user has this feature enabled in
        $hasAccessInAnyDept = UserDepartmentFeature::where('user_id', $user->id)
            ->where('is_enabled', true)
            ->whereHas('feature', fn ($q) => $q->where('slug', $featureSlug))
            ->whereHas('department', fn ($q) => $q->where('block', $block))
            ->exists();

        if ($hasAccessInAnyDept) {
            // Update the active dept to one where user has this feature (so they can actually use it)
            $validDeptId = UserDepartmentFeature::where('user_id', $user->id)
                ->where('is_enabled', true)
                ->whereHas('feature', fn ($q) => $q->where('slug', $featureSlug))
                ->whereHas('department', fn ($q) => $q->where('block', $block))
                ->value('department_id');

            if ($validDeptId) {
                session([$sessionKey => $validDeptId]);
            }

            return $next($request);
        }

        $indexRoute = match($portalType) {
            'activities' => 'portal.index',
            'ministry'   => 'ministry.index',
            'deacon'     => 'deacon.index',
            'education'  => 'education.index',
            default      => 'dashboard'
        };

        return redirect()->route($indexRoute)->with('error', sprintf(
            'Bạn chưa được cấp quyền truy cập tính năng "%s". Vui lòng liên hệ quản trị viên.',
            $featureSlug
        ));
    }
}
