<?php

namespace App\Http\Middleware;

use App\Models\Department;
use App\Models\OrgMembership;
use App\Models\UserDepartmentFeature;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * EnsureMinistryContext — Sets active_ministry_dept_id session.
 * Access granted via OrgMembership OR explicit UserDepartmentFeature (MAC).
 */
class EnsureMinistryContext
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if (!$user) return $next($request);

        $activeDeptId  = session('active_ministry_dept_id');
        $isGlobalAdmin = $user->isSuperAdmin();

        $validDeptIds = [];

        // 1. Các ban mà user là thành viên
        $member = \App\Models\Member::where('user_id', $user->id)->first();
        if ($member) {
            $validDeptIds = $member->departments()->where('block', 'ministry')->pluck('departments.id')->toArray();
        }

        // 2. Các ban mà user được cấp quyền tính năng (nhưng có thể chưa gán membership)
        $featureDeptIds = UserDepartmentFeature::where('user_id', $user->id)
            ->where('is_enabled', true)
            ->whereHas('department', fn ($q) => $q->where('block', 'ministry'))
            ->pluck('department_id')
            ->toArray();
            
        $validDeptIds = array_unique(array_merge($validDeptIds, $featureDeptIds));

        // 1. Determine active department
        if (!$activeDeptId || (!$isGlobalAdmin && !in_array($activeDeptId, $validDeptIds))) {
            if ($isGlobalAdmin) {
                $firstDept = Department::where('block', 'ministry')->orderBy('name')->first();
                if ($firstDept) {
                    $activeDeptId = $firstDept->id;
                    session(['active_ministry_dept_id' => $activeDeptId]);
                }
            } else {
                if (!empty($validDeptIds)) {
                     $activeDeptId = $validDeptIds[0];
                     session(['active_ministry_dept_id' => $activeDeptId]);
                }
            }
        }

        if (!$activeDeptId) {
            return redirect()->route('login')->withErrors([
                'email' => 'Bạn chưa được cấp quyền truy cập cổng Mục Vụ. Vui lòng liên hệ quản trị viên.',
            ]);
        }

        $activeDept = Department::find($activeDeptId);
        
        // ── MAC: Matrix Access Control Prop Sharing ─────────────────────
        $service = app(\App\Services\FeatureAssignmentService::class);
        $departmentFeatures = $activeDept ? $service->getAvailableFeaturesForDepartment($activeDept) : [];
        
        // ── Level 2: User-level permissions ───────────────────────────────
        // Logic: 
        //   1. Strict Whitelist: Mặc định user không có quyền gì cả (false)
        //   2. UserDepartmentFeature dùng để CẤP QUYỀN (is_enabled = true)
        //   3. Super Admin → Tôn trọng giới hạn của department
        $userPermissions = collect(\App\Models\Feature::pluck('slug'))
            ->mapWithKeys(fn($s) => [$s => false])
            ->toArray();

        if ($isGlobalAdmin) {
            // Admin only gets what the department has
            $userPermissions = collect(\App\Models\Feature::pluck('slug'))
                ->mapWithKeys(fn($s) => [$s => $departmentFeatures[$s] ?? false])
                ->toArray();
        } else {
            // Áp dụng explicit overrides từ UserDepartmentFeature (nếu có)
            $overrideRecords = UserDepartmentFeature::where('user_id', $user->id)
                ->where('department_id', $activeDeptId)
                ->with('feature')
                ->get();
                
            foreach ($overrideRecords as $uf) {
                if (!$uf->feature) continue;
                // Override theo is_enabled (có thể true hoặc false)
                $userPermissions[$uf->feature->slug] = $uf->is_enabled ? ($uf->data_scope ?? 'dept') : false;
            }
        }

        $request->attributes->set('userPermissions', $userPermissions);

        \Inertia\Inertia::share('departmentFeatures', $departmentFeatures);
        \Inertia\Inertia::share('userPermissions', $userPermissions);
        \Inertia\Inertia::share('activeDepartment', $activeDept);
        \Inertia\Inertia::share('isGlobalAdmin', $isGlobalAdmin);

        return $next($request);
    }
}
