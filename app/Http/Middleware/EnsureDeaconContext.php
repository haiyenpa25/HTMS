<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Member;
use App\Models\OrgRole;
use App\Models\OrgMembership;
use App\Models\Department;

/**
 * P4 — Cổng Chấp Sự (Deacon Portal)
 * Cho phép: SuperAdmin + tín hữu có org_role = 'cs' (Chấp Sự) trong BCS
 *           hoặc bất kỳ org_role lãnh đạo (tb, pb, tkhu, tqht) trong ban leadership
 *
 * MAC: Matrix Access Control chia sẻ prop cho frontend.
 */
class EnsureDeaconContext
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        $isGlobalAdmin = $user->isSuperAdmin();

        if (!$isGlobalAdmin) {
            // Lấy member gắn với user
            $member = Member::where('user_id', $user->id)->first();

            if (!$member) {
                return redirect()->route('login')->withErrors([
                    'email' => 'Tài khoản chưa được gắn với hồ sơ tín hữu. Liên hệ quản trị viên.',
                ]);
            }

            // Roles được phép vào Deacon Portal:
            // cs=Chấp Sự, tkhu=Thư Ký HT, ptk=Phó TK, tqht=Thủ Quỹ HT, ptq=Phó TQ
            $allowedCodes = ['cs', 'tkhu', 'ptk', 'tqht', 'ptq'];
            $allowedRoleIds = OrgRole::whereIn('code', $allowedCodes)->pluck('id');

            $hasAccess = OrgMembership::where('member_id', $member->id)
                ->whereIn('org_role_id', $allowedRoleIds)
                ->where('is_active', true)
                ->exists();

            if (!$hasAccess) {
                return redirect()->route('login')->withErrors([
                    'email' => 'Bạn không có quyền truy cập cổng Chấp Sự. Vui lòng liên hệ quản trị viên.',
                ]);
            }
        }

        // Default active role if not set
        if (!session()->has('active_deacon_role')) {
            session(['active_deacon_role' => 'deacon']);
        }

        // ── MAC: Matrix Access Control Prop Sharing ─────────────────────
        $deaconDept = Department::find(1); // Ban Chấp Sự BCS
        $service = app(\App\Services\FeatureAssignmentService::class);
        $departmentFeatures = $deaconDept ? $service->getAvailableFeaturesForDepartment($deaconDept) : [];

        // Level 2: Strict Whitelist — default false, phải cấp tường minh
        $userPermissions = collect(\App\Models\Feature::pluck('slug'))
            ->mapWithKeys(fn($s) => [$s => false])
            ->toArray();

        if ($isGlobalAdmin) {
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

        // Structural overrides — các chức năng mặc định của portal Chấp Sự
        $userPermissions['attendance']     = 'global';
        $userPermissions['reports']        = 'global';
        $userPermissions['finance']        = 'global';
        $userPermissions['finance-reports']= 'global';
        $userPermissions['assignments']    = 'global';

        $departmentFeatures['attendance']     = 'global';
        $departmentFeatures['reports']        = 'global';
        $departmentFeatures['finance']        = 'global';
        $departmentFeatures['finance-reports']= 'global';
        $departmentFeatures['assignments']    = 'global';

        $request->attributes->set('userPermissions', $userPermissions);

        \Inertia\Inertia::share('departmentFeatures', $departmentFeatures);
        \Inertia\Inertia::share('userPermissions', $userPermissions);
        \Inertia\Inertia::share('activeDepartment', $deaconDept);
        \Inertia\Inertia::share('isGlobalAdmin', $isGlobalAdmin);
        \Inertia\Inertia::share('activeDeaconRole', session('active_deacon_role', 'deacon'));

        return $next($request);
    }
}
