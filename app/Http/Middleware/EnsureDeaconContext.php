<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureDeaconContext
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        $isGlobalAdmin = $user->isSuperAdmin()
            || $user->email === 'superadmin@httlthanhmyloi.com';

        $hasDeaconPerm = false;
        try {
            $hasDeaconPerm = $user->hasPermissionTo('view_deacon');
        } catch (\Spatie\Permission\Exceptions\PermissionDoesNotExist $e) {
            // Permission 'view_deacon' chưa được seed — bỏ qua, dùng role check
        }

        $allowed = $isGlobalAdmin
            || $user->isSuperAdmin()
            || $hasDeaconPerm;

        if (!$allowed) {
            return redirect()->route('login')->withErrors([
                'email' => 'Bạn không có quyền truy cập cổng Chấp Sự. Vui lòng liên hệ quản trị viên.',
            ]);
        }

        // Default active role if not set
        if (!session()->has('active_deacon_role')) {
            session(['active_deacon_role' => 'secretary']);
        }

        // ── MAC: Matrix Access Control Prop Sharing ─────────────────────
        // Cho Chấp sự, chúng ta mặc định lấy cấu hình của "Ban Chấp sự" (ID=1)
        $deaconDept = \App\Models\Department::find(1); 
        $service = app(\App\Services\FeatureAssignmentService::class);
        $departmentFeatures = $deaconDept ? $service->getAvailableFeaturesForDepartment($deaconDept) : [];
        
        // Level 2: Strict Whitelist: Mặc định user không có quyền gì cả (false). Phải cấp tường minh.
        $userPermissions = collect(\App\Models\Feature::pluck('slug'))
            ->mapWithKeys(fn($s) => [$s => false])
            ->toArray();

        if ($isGlobalAdmin) {
            // Admin only gets what the department has (which are scopes now)
            $userPermissions = collect(\App\Models\Feature::pluck('slug'))
                ->mapWithKeys(fn($s) => [$s => $departmentFeatures[$s] ?? false])
                ->toArray();
        } else {
            $overrideRecords = \App\Models\UserDepartmentFeature::where('user_id', $user->id)
                ->where('department_id', 1)
                ->with('feature')
                ->get();
                
            foreach ($overrideRecords as $uf) {
                if (!$uf->feature) continue;
                $userPermissions[$uf->feature->slug] = $uf->is_enabled ? ($uf->data_scope ?? 'dept') : false;
            }
        }

        // Structural Deacon Role overrides
        // Thư ký and Thủ quỹ need these rendered on the sidebar
        $userPermissions['attendance'] = 'global';       // for Secretary Điểm danh
        $userPermissions['reports'] = 'global';          // for Secretary Báo cáo
        $userPermissions['finance'] = 'global';          // for Treasurer Quản lý quỹ
        $userPermissions['finance-reports'] = 'global';  // for Treasurer Báo cáo tài chính
        $userPermissions['assignments'] = 'global';      // for All Deacons Phân công

        $departmentFeatures['attendance'] = 'global';
        $departmentFeatures['reports'] = 'global';
        $departmentFeatures['finance'] = 'global';
        $departmentFeatures['finance-reports'] = 'global';
        $departmentFeatures['assignments'] = 'global';

        $request->attributes->set('userPermissions', $userPermissions);

        \Inertia\Inertia::share('departmentFeatures', $departmentFeatures);
        \Inertia\Inertia::share('userPermissions', $userPermissions);
        \Inertia\Inertia::share('activeDepartment', $deaconDept);
        \Inertia\Inertia::share('isGlobalAdmin', $isGlobalAdmin);
        \Inertia\Inertia::share('activeDeaconRole', session('active_deacon_role', 'secretary'));

        return $next($request);
    }
}
