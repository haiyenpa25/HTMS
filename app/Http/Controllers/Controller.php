<?php

namespace App\Http\Controllers;

use App\Services\PortalService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

abstract class Controller
{
    use AuthorizesRequests;

    /**
     * Kiểm tra quyền XEM tính năng trong ngữ cảnh ban ngành hiện tại.
     * SuperAdmin bypass tự động. Abort 403 nếu không có quyền.
     *
     * @param string $featureSlug  VD: 'finance', 'visitation', 'reports'
     */
    protected function authorizeFeature(string $featureSlug): void
    {
        $user = auth()->user();
        if (!$user) abort(403);
        if ($user->isSuperAdmin()) return;

        $deptId = $this->activeDeptId();
        if (!$deptId) abort(403, 'Không có ngữ cảnh ban ngành.');

        if (!app(PortalService::class)->canAccess($user, $deptId, $featureSlug)) {
            abort(403, 'Bạn không có quyền truy cập tính năng này.');
        }
    }

    /**
     * Kiểm tra quyền QUẢN LÝ (tạo/sửa/xóa) tính năng.
     * Yêu cầu access_level = 'manage'. Abort 403 nếu không đủ quyền.
     *
     * @param string $featureSlug  VD: 'finance', 'visitation', 'reports'
     */
    protected function authorizeManage(string $featureSlug): void
    {
        $user = auth()->user();
        if (!$user) abort(403);
        if ($user->isSuperAdmin()) return;

        $deptId = $this->activeDeptId();
        if (!$deptId) abort(403, 'Không có ngữ cảnh ban ngành.');

        if (!app(PortalService::class)->canManage($user, $deptId, $featureSlug)) {
            abort(403, 'Bạn không có quyền quản lý tính năng này (cần access_level = manage).');
        }
    }

    /**
     * Lấy active department ID từ session (theo ngữ cảnh portal/ministry/deacon).
     */
    protected function activeDeptId(): ?int
    {
        $request = request();

        if ($request->is('ministry*')) {
            return session('active_ministry_dept_id');
        } elseif ($request->is('deacon*')) {
            return session('active_deacon_dept_id');
        } else {
            // activities portal hoặc fallback
            return session('active_portal_dept_id')
                ?? session('active_ministry_dept_id')
                ?? session('active_deacon_dept_id');
        }
    }
}
