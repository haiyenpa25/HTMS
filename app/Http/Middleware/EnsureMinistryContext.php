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
        $isGlobalAdmin = $user->hasRole(['Pastor', 'BTS_Admin', 'Super_Admin'])
            || $user->email === 'superadmin@httlthanhmyloi.com';

        // 1. Determine active department
        if (!$activeDeptId) {
            if ($isGlobalAdmin) {
                $firstDept = Department::where('block', 'ministry')->orderBy('name')->first();
                if ($firstDept) {
                    $activeDeptId = $firstDept->id;
                    session(['active_ministry_dept_id' => $activeDeptId]);
                }
            } else {
                // OrgMembership path
                $memberId = $user->member?->id ?? null;
                if ($memberId) {
                    $membership = OrgMembership::where('member_id', $memberId)
                        ->where('model_type', Department::class)
                        ->whereHas('department', fn($q) => $q->where('block', 'ministry'))
                        ->first();
                    if ($membership) {
                        $activeDeptId = $membership->model_id;
                        session(['active_ministry_dept_id' => $activeDeptId]);
                    }
                }

                if (!$activeDeptId) {
                    // MAC path
                    $macDeptId = UserDepartmentFeature::where('user_id', $user->id)
                        ->where('is_enabled', true)
                        ->whereHas('department', fn($q) => $q->where('block', 'ministry'))
                        ->value('department_id');

                    if ($macDeptId) {
                        $activeDeptId = $macDeptId;
                        session(['active_ministry_dept_id' => $activeDeptId]);
                    }
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
        
        $userPermissions = collect(\App\Models\Feature::pluck('slug'))->mapWithKeys(fn($s) => [$s => false])->toArray();
        if ($isGlobalAdmin) {
            $userPermissions = array_map(fn() => true, $userPermissions);
        } else {
            $activeRecords = UserDepartmentFeature::where('user_id', $user->id)
                ->where('department_id', $activeDeptId)
                ->where('is_enabled', true)
                ->with('feature')
                ->get();
                
            foreach ($activeRecords as $uf) {
                if (!$uf->feature) continue;
                $userPermissions[$uf->feature->slug] = true;
            }
        }

        \Inertia\Inertia::share('departmentFeatures', $departmentFeatures);
        \Inertia\Inertia::share('userPermissions', $userPermissions);
        \Inertia\Inertia::share('activeDepartment', $activeDept);
        \Inertia\Inertia::share('isGlobalAdmin', $isGlobalAdmin);

        return $next($request);
    }
}
