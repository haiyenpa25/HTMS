<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\OrgMembership;
use App\Models\Department;

/**
 * CheckFeatureAccess — Kiểm tra user có quyền truy cập vào tính năng cụ thể
 * Ví dụ: portal.attendance.index → cần permission 'attendance'
 *
 * Dùng sau CheckPortalAccess (đã xác nhận user có membership trong block)
 */
class CheckFeatureAccess
{
    const SUPER_ADMIN_EMAIL = 'superadmin@httlthanhmyloi.com';

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

    /**
     * @param string $featureKey  e.g. 'attendance', 'finance', 'reports'
     * @param string $portalType  e.g. 'activities', 'ministry', 'deacon'
     */
    public function handle(Request $request, Closure $next, string $featureKey, string $portalType = 'activities'): Response
    {
        $user = $request->user();
        if (!$user) return redirect()->route('login');

        // Super Admin bypass
        if ($user->email === self::SUPER_ADMIN_EMAIL || $user->isSuperAdmin()) {
            return $next($request);
        }

        // Lấy active department từ session
        $sessionKey  = self::SESSION_DEPT_KEY[$portalType] ?? 'active_portal_dept_id';
        $activeDeptId = session($sessionKey);

        if (!$activeDeptId) {
            abort(403, 'Chưa có ban ngành được chọn.');
        }

        // Lấy membership của user trong department đó
        $member = $user->member;
        if (!$member) {
            abort(403, 'Tài khoản chưa liên kết với tín hữu.');
        }

        $membership = OrgMembership::where('member_id', $member->id)
            ->where('model_type', Department::class)
            ->where('model_id', $activeDeptId)
            ->where('is_active', true)
            ->first();

        if (!$membership) {
            abort(403, 'Không tìm thấy tư cách thành viên.');
        }

        // Kiểm tra permission key trong membership.permissions JSON
        $permissions = $membership->permissions ?? [];

        // Nếu permissions chưa được cấu hình → mặc định cho qua (backward compat)
        if (empty($permissions)) {
            return $next($request);
        }

        if (empty($permissions[$featureKey])) {
            abort(403, "Bạn chưa được cấp quyền truy cập tính năng này.");
        }

        return $next($request);
    }
}
