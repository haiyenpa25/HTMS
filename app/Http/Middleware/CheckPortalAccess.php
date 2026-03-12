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
            $activeDeptId = $this->ensureSessionContext($user, $portalType);
            $activeDept = $activeDeptId ? Department::find($activeDeptId) : null;
            
            $service = app(FeatureAssignmentService::class);
            $departmentFeatures = $activeDept ? $service->getAvailableFeaturesForDepartment($activeDept) : [];
            
            // Fix: Admin should only see features that are enabled for this specific department
            // Instead of returning ALL features blindly.
            $allFeatures = \App\Models\Feature::pluck('slug');
            $userPermissions = collect($allFeatures)->mapWithKeys(function($slug) use ($departmentFeatures) {
                // If the department feature mapping explicitly denies this feature (or doesn't have it configed for this dept)
                // then Admin should not see it in this portal/department context.
                return [$slug => $departmentFeatures[$slug] ?? false];
            })->toArray();
            
            \Inertia\Inertia::share('departmentFeatures', $departmentFeatures);
            \Inertia\Inertia::share('userPermissions', $userPermissions);
            \Inertia\Inertia::share('activeDepartment', $activeDept);
            
            return $next($request);
        }

        $block      = self::BLOCK_MAP[$portalType] ?? 'activities';
        $sessionKey = self::SESSION_DEPT_KEY[$portalType] ?? 'active_portal_dept_id';

        $validDeptIds = [];

        // 1. Các ban mà user là thành viên
        $member = \App\Models\Member::where('user_id', $user->id)->first();
        if ($member) {
            $validDeptIds = $member->departments()->where('block', $block)->pluck('departments.id')->toArray();
        }

        // 2. Các ban mà user được cấp quyền tính năng (nhưng có thể chưa gán membership)
        $featureDeptIds = UserDepartmentFeature::where('user_id', $user->id)
            ->where('is_enabled', true)
            ->whereHas('department', fn ($q) => $q->where('block', $block))
            ->pluck('department_id')
            ->toArray();
            
        $validDeptIds = array_unique(array_merge($validDeptIds, $featureDeptIds));

        if (empty($validDeptIds)) {
            // No portal access: redirect to the Member Portal instead of login
            // to prevent the ERR_TOO_MANY_REDIRECTS loop for standard users.
            return redirect()->route('member.portal.index')->with('error', 'Bạn không có quyền truy cập tính năng nào trong cổng này.');
        }

        // Auto-set session nếu chưa có hoặc session dept không thuộc valid list
        $activeDeptId = session($sessionKey);
        if (!$activeDeptId || !in_array($activeDeptId, $validDeptIds)) {
            $activeDeptId = $validDeptIds[0];
            session([$sessionKey => $activeDeptId]);
        }
        $activeDept = Department::find($activeDeptId);
        
        $departmentFeatures = [];
        $userPermissions = [];
        
        if ($activeDept) {
            $service = app(FeatureAssignmentService::class);
            $departmentFeatures = $service->getAvailableFeaturesForDepartment($activeDept);
            
            // Level 2: bắt đầu từ departmentFeatures, UserDepartmentFeature chỉ là override
            $userPermissions = collect(\App\Models\Feature::pluck('slug'))
                ->mapWithKeys(fn($s) => [$s => $departmentFeatures[$s] ?? false])
                ->toArray();

            $overrideRecords = UserDepartmentFeature::where('user_id', $user->id)
                ->where('department_id', $activeDeptId)
                ->with('feature')
                ->get();
                
            foreach ($overrideRecords as $uf) {
                if (!$uf->feature) continue;
                $userPermissions[$uf->feature->slug] = (bool) $uf->is_enabled;
            }
        }

        \Inertia\Inertia::share('departmentFeatures', $departmentFeatures);
        \Inertia\Inertia::share('userPermissions', $userPermissions);
        \Inertia\Inertia::share('activeDepartment', $activeDept);

        return $next($request);
    }

    private function ensureSessionContext($user, string $portalType): ?int
    {
        $block      = self::BLOCK_MAP[$portalType] ?? 'activities';
        $sessionKey = self::SESSION_DEPT_KEY[$portalType] ?? 'active_portal_dept_id';

        if (!session()->has($sessionKey)) {
            $firstDept = Department::where('block', $block)->orderBy('name')->first();
            if ($firstDept) {
                session([$sessionKey => $firstDept->id]);
                return $firstDept->id;
            }
            return null;
        }
        return session($sessionKey);
    }
}
