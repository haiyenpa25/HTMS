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
            $hasDeaconPerm = $user->isSuperAdmin();
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
        
        // Level 2: bắt đầu từ departmentFeatures, UserDepartmentFeature chỉ là override
        $userPermissions = collect(\App\Models\Feature::pluck('slug'))
            ->mapWithKeys(fn($s) => [$s => $departmentFeatures[$s] ?? false])
            ->toArray();

        if ($isGlobalAdmin) {
            // Admin only gets what the department has
            // (already set by the mapWithKeys above)
            // No action needed: $userPermissions is already perfectly scoped to $departmentFeatures
        } else {
            $overrideRecords = \App\Models\UserDepartmentFeature::where('user_id', $user->id)
                ->where('department_id', 1)
                ->with('feature')
                ->get();
                
            foreach ($overrideRecords as $uf) {
                if (!$uf->feature) continue;
                $userPermissions[$uf->feature->slug] = (bool) $uf->is_enabled;
            }
        }

        \Inertia\Inertia::share('departmentFeatures', $departmentFeatures);
        \Inertia\Inertia::share('userPermissions', $userPermissions);
        \Inertia\Inertia::share('activeDepartment', $deaconDept);
        \Inertia\Inertia::share('isGlobalAdmin', $isGlobalAdmin);
        \Inertia\Inertia::share('activeDeaconRole', session('active_deacon_role', 'secretary'));

        return $next($request);
    }
}
