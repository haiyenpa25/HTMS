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

        $block      = self::BLOCK_MAP[$portalType] ?? 'activities';
        $sessionKey = self::SESSION_DEPT_KEY[$portalType] ?? 'active_portal_dept_id';

        $isGlobalAdmin = $user->isSuperAdmin();
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

        if (!$isGlobalAdmin && empty($validDeptIds)) {
            // No portal access: redirect to the Member Portal instead of login
            // to prevent the ERR_TOO_MANY_REDIRECTS loop for standard users.
            return redirect()->route('member.portal.index')->with('error', 'Bạn không có quyền truy cập tính năng nào trong cổng này.');
        }

        $activeDeptId = session($sessionKey);

        if (!$activeDeptId || (!$isGlobalAdmin && !in_array($activeDeptId, $validDeptIds))) {
            if ($isGlobalAdmin) {
                $firstDept = Department::where('block', $block)->orderBy('name')->first();
                if ($firstDept) {
                    $activeDeptId = $firstDept->id;
                    session([$sessionKey => $activeDeptId]);
                }
            } else {
                if (!empty($validDeptIds)) {
                     $activeDeptId = $validDeptIds[0];
                     session([$sessionKey => $activeDeptId]);
                }
            }
        }
        $activeDept = Department::find($activeDeptId);
        
        $departmentFeatures = [];
        $userPermissions = [];
        
        if ($activeDept) {
            $service = app(FeatureAssignmentService::class);
            $departmentFeatures = $service->getAvailableFeaturesForDepartment($activeDept);
            
            // Level 2: Inherit from Level 1 by default (MAC v2 standard).
            // If the department has a feature, all its members have it by default.
            // user_department_features records serve as explicit OVERRIDES only.
            $userPermissions = collect(\App\Models\Feature::pluck('slug'))
                ->mapWithKeys(fn($s) => [$s => !empty($departmentFeatures[$s])])
                ->toArray();

            $isGlobalAdmin = $user->isSuperAdmin();

            if ($isGlobalAdmin) {
                $userPermissions = collect(\App\Models\Feature::pluck('slug'))
                    ->mapWithKeys(fn($s) => [$s => !empty($departmentFeatures[$s])])
                    ->toArray();
            } else {
                // Apply explicit user-level overrides (can grant OR restrict)
                $overrideRecords = UserDepartmentFeature::where('user_id', $user->id)
                    ->where('department_id', $activeDeptId)
                    ->with('feature')
                    ->get();
                    
                foreach ($overrideRecords as $uf) {
                    if (!$uf->feature) continue;
                    $userPermissions[$uf->feature->slug] = (bool) $uf->is_enabled;
                }
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
