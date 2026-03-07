<?php

namespace App\Http\Middleware;

use App\Models\Department;
use App\Models\OrgMembership;
use App\Models\UserDepartmentFeature;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * EnsurePortalContext — Sets the active department session for the portal.
 *
 * Access granted if:
 * 1. User is a global admin (Super_Admin, BTS_Admin, Pastor)
 * 2. User has an OrgMembership in an activities department (old path)
 * 3. User has explicit UserDepartmentFeature permissions in an activities dept (new MAC path)
 *    → No membership required for this path.
 */
class EnsurePortalContext
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if (!$user) return $next($request);

        $activeDeptId = session('active_portal_dept_id');

        // Already has an active dept context — proceed
        if ($activeDeptId) {
            return $next($request);
        }

        $isGlobalAdmin = $user->hasAnyRole(['Pastor', 'BTS_Admin', 'Super_Admin'])
            || $user->email === 'superadmin@httlthanhmyloi.com';

        // 1. Global Admin → pick any first activities dept
        if ($isGlobalAdmin) {
            $firstDept = Department::where('block', 'activities')->orderBy('name')->first();
            if ($firstDept) {
                session(['active_portal_dept_id' => $firstDept->id]);
            }
            return $next($request);
        }

        // 2. Try OrgMembership path (user is a member of an activities department)
        $memberId = $user->member?->id ?? null;
        if ($memberId) {
            $membership = OrgMembership::where('member_id', $memberId)
                ->where('model_type', Department::class)
                ->whereHasMorph('model', [Department::class], fn($q) => $q->where('block', 'activities'))
                ->first();
            if ($membership) {
                session(['active_portal_dept_id' => $membership->model_id]);
                return $next($request);
            }
        }

        // 3. MAC path: user has explicit permission in user_department_features → no membership needed
        $macDeptId = UserDepartmentFeature::where('user_id', $user->id)
            ->where('is_enabled', true)
            ->whereHas('department', fn($q) => $q->where('block', 'activities'))
            ->value('department_id');

        if ($macDeptId) {
            session(['active_portal_dept_id' => $macDeptId]);
            return $next($request);
        }

        // No access at all
        abort(403, 'Bạn chưa được cấp quyền truy cập cổng này.');
    }
}
