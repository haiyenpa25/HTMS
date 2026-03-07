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

        if ($activeDeptId) {
            return $next($request);
        }

        // 1. Global Admin
        if ($isGlobalAdmin) {
            $firstDept = Department::where('block', 'ministry')->orderBy('name')->first();
            if ($firstDept) {
                session(['active_ministry_dept_id' => $firstDept->id]);
            }
            return $next($request);
        }

        // 2. OrgMembership path
        $memberId = $user->member?->id ?? null;
        if ($memberId) {
            $membership = OrgMembership::where('member_id', $memberId)
                ->where('model_type', Department::class)
                ->whereHas('department', fn($q) => $q->where('block', 'ministry'))
                ->first();
            if ($membership) {
                session(['active_ministry_dept_id' => $membership->model_id]);
                return $next($request);
            }
        }

        // 3. MAC path — explicit permission without membership
        $macDeptId = UserDepartmentFeature::where('user_id', $user->id)
            ->where('is_enabled', true)
            ->whereHas('department', fn($q) => $q->where('block', 'ministry'))
            ->value('department_id');

        if ($macDeptId) {
            session(['active_ministry_dept_id' => $macDeptId]);
            return $next($request);
        }

        abort(403, 'Bạn chưa được cấp quyền truy cập cổng Mục Vụ.');
    }
}
