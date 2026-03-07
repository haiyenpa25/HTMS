<?php

namespace App\Http\Middleware;

use App\Models\Department;
use App\Models\UserDepartmentFeature;
use App\Services\FeatureAssignmentService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * CheckPortalAccess — Two-Tier MAC version.
 * Kiểm tra xem user có bất kỳ tính năng nào hợp lệ (giao điểm Level 1 & Level 2) trong block này hay không.
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

        // Lấy tất cả quyền Level 2 của user trong block này
        $userFeatureRecords = UserDepartmentFeature::where('user_id', $user->id)
            ->where('is_enabled', true)
            ->whereHas('department', fn ($q) => $q->where('block', $block))
            ->with(['feature', 'department'])
            ->get()
            ->groupBy('department_id');

        $validDeptIds = [];
        $service = app(FeatureAssignmentService::class);

        foreach ($userFeatureRecords as $deptId => $ufRecords) {
            $dept = $ufRecords->first()->department;
            if (!$dept) continue;
            
            $level1Map = $service->getAvailableFeaturesForDepartment($dept);
            
            $hasValidFeature = false;
            foreach ($ufRecords as $uf) {
                if (!$uf->feature) continue;
                $slug = $uf->feature->slug;
                // If Level 1 allows (true by default when no config), accept this department
                if ($level1Map[$slug] ?? true) {
                    $hasValidFeature = true;
                    break;
                }
            }
            
            if ($hasValidFeature) {
                $validDeptIds[] = $deptId;
            }
        }

        if (empty($validDeptIds)) {
            abort(403, 'Bạn chưa được cấp quyền truy cập tính năng nào trong cổng này. Vui lòng liên hệ quản trị viên.');
        }

        // Auto-set session nếu chưa có hoặc session dept không thuộc valid list
        $activeDeptId = session($sessionKey);
        if (!$activeDeptId || !in_array($activeDeptId, $validDeptIds)) {
            $activeDeptId = $validDeptIds[0];
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
