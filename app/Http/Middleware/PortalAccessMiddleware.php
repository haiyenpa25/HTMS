<?php

namespace App\Http\Middleware;

use App\Services\PortalService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * PortalAccessMiddleware — Thay thế CheckFeatureAccess + CheckPortalAccess cũ.
 * Kiểm tra quyền truy cập tính năng dựa vào bảng user_department_features.
 *
 * God Mode: Super_Admin, Pastor → luôn pass.
 *
 * Usage trong routes:
 *   Route::middleware('portal.access:attendance,activities')->get('/attendance', ...);
 *   Route::middleware('portal.access:visitation,ministry')->get('/visitation', ...);
 */
class PortalAccessMiddleware
{
    const SUPER_ADMIN_EMAIL = 'superadmin@httlthanhmyloi.com';

    const SESSION_DEPT_KEY = [
        'activities' => 'active_portal_dept_id',
        'ministry'   => 'active_ministry_dept_id',
        'deacon'     => 'active_deacon_dept_id',
        'education'  => 'active_ministry_dept_id', // Education dùng chung với ministry
    ];

    public function __construct(private PortalService $service) {}

    /**
     * @param  string  $featureSlug  e.g. 'attendance', 'visitation', 'education-classes'
     * @param  string  $portalType   e.g. 'activities', 'ministry', 'deacon'
     */
    public function handle(Request $request, Closure $next, string $featureSlug, string $portalType = 'activities'): Response
    {
        $user = $request->user();
        if (!$user) {
            return redirect()->route('login');
        }

        // ════════════════════════════════════════
        // GOD MODE: Super_Admin / Pastor bypass ALL
        // ════════════════════════════════════════
        if ($user->isSuperAdmin()) {
            return $next($request);
        }

        // Lấy active department từ session
        $sessionKey  = self::SESSION_DEPT_KEY[$portalType] ?? 'active_portal_dept_id';
        $activeDeptId = session($sessionKey);

        if (!$activeDeptId) {
            abort(403, 'Chưa có ban ngành được chọn. Vui lòng chọn ban ngành trước.');
        }

        // Check bảng user_department_features
        if (!$this->service->canAccess($user, (int) $activeDeptId, $featureSlug)) {
            abort(403, sprintf(
                'Bạn chưa được cấp quyền truy cập tính năng "%s". Liên hệ quản trị viên để được phân quyền.',
                $featureSlug
            ));
        }

        return $next($request);
    }
}
