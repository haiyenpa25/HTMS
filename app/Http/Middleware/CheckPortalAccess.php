<?php

namespace App\Http\Middleware;

use App\Models\Department;
use App\Models\UserDepartmentFeature;
use App\Services\FeatureAssignmentService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * CheckPortalAccess — Cổng vào Portal (Block-level gate).
 *
 * Nhiệm vụ DUY NHẤT: Xác định xem user có được vào portal block này không.
 * Không xử lý feature-level permissions (đó là việc của PortalAccessMiddleware).
 *
 * Tiêu chí cho phép vào:
 *  1. SuperAdmin → luôn cho vào
 *  2. User có membership trong bất kỳ dept nào thuộc block này → cho vào
 *
 * Sau khi cho vào, share departmentFeatures (Level 1) để frontend render UI.
 * auth.allowed_features (nguồn sự thật duy nhất) đã được tính ở HandleInertiaRequests.
 */
class CheckPortalAccess
{
    const BLOCK_MAP = [
        'activities' => 'activities',
        'ministry'   => 'ministry',
        'deacon'     => 'leadership',
    ];

    // Dùng lại SESSION_KEYS từ AbstractPortalMiddleware (single source of truth)
    // Không tự định nghĩa riêng để tránh drift.
    private function getSessionKey(string $portalType): string
    {
        return AbstractPortalMiddleware::SESSION_KEYS[$portalType] ?? 'active_portal_dept_id';
    }

    public function handle(Request $request, Closure $next, string $portalType = 'activities'): Response
    {
        $user = $request->user();
        if (!$user) return redirect()->route('login');

        $block      = self::BLOCK_MAP[$portalType] ?? 'activities';
        $sessionKey = $this->getSessionKey($portalType);

        // ── SuperAdmin: bypass tất cả ─────────────────────────────────────────
        if ($user->isSuperAdmin()) {
            $activeDeptId = session($sessionKey);
            if (!$activeDeptId) {
                $firstDept = Department::where('block', $block)->orderBy('name')->first();
                if ($firstDept) {
                    $activeDeptId = $firstDept->id;
                    session([$sessionKey => $activeDeptId]);
                }
            }
            $activeDept = Department::find($activeDeptId);
            $this->shareDeptContext($activeDept);
            return $next($request);
        }

        // ── Normal user: tìm các dept hợp lệ trong block này ─────────────────
        // Ưu tiên 1: Membership (tín hữu thuộc ban ngành)
        $validDeptIds = [];
        $member = \App\Models\Member::where('user_id', $user->id)->first();
        if ($member) {
            $validDeptIds = $member->departments()
                ->where('block', $block)
                ->pluck('departments.id')
                ->toArray();
        }

        // Ưu tiên 2: Explicit feature grants (user được cấp quyền dù chưa có membership)
        if (empty($validDeptIds)) {
            $validDeptIds = UserDepartmentFeature::where('user_id', $user->id)
                ->where('is_enabled', true)
                ->whereHas('department', fn($q) => $q->where('block', $block))
                ->pluck('department_id')
                ->toArray();
        }

        if (empty($validDeptIds)) {
            return redirect()->route('member.portal.index')
                ->with('error', 'Bạn chưa được phân công vào ban ngành nào trong cổng này.');
        }

        // ── Resolve active dept ───────────────────────────────────────────────
        $activeDeptId = session($sessionKey);
        if (!$activeDeptId || !in_array($activeDeptId, $validDeptIds)) {
            $activeDeptId = $validDeptIds[0];
            session([$sessionKey => $activeDeptId]);
        }

        $activeDept = Department::find($activeDeptId);
        $this->shareDeptContext($activeDept);

        return $next($request);
    }

    /**
     * Share Level 1 dept feature config với frontend.
     * auth.allowed_features (Level 1 + Level 2 merged) được tính riêng trong HandleInertiaRequests.
     *
     * ✨ Feature Scope Pipeline:
     * departmentFeatures chứa data_scope string (e.g. 'global', 'dept', 'self')
     * thay vì chỉ true/false. Được forward vào request attributes 'featureScopes'
     * để các Controller dùng ScopeResolver::apply() với đúng scope.
     */
    private function shareDeptContext(?Department $dept): void
    {
        $departmentFeatures = [];
        if ($dept) {
            $service = app(FeatureAssignmentService::class);
            $departmentFeatures = $service->getAvailableFeaturesForDepartment($dept);
        }

        \Inertia\Inertia::share('departmentFeatures', $departmentFeatures);
        \Inertia\Inertia::share('activeDepartment', $dept);

        // ✨ Forward featureScopes để Controller resolve data scope qua ScopeResolver.
        // Keys = feature slugs, Values = data_scope string ('global'|'dept'|'self'|false).
        // Controllers nên đọc: request()->attributes->get('featureScopes', [])
        request()->attributes->set('featureScopes', $departmentFeatures);

        // KHÔNG share userPermissions riêng nữa — dùng auth.allowed_features từ HandleInertiaRequests
        // Điều này đảm bảo 1 nguồn sự thật duy nhất cho tất cả frontend permission checks.
    }
}
