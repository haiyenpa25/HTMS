<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Department;
use App\Models\OrgMembership;

class EnsurePortalContext
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if (!$user) return $next($request);

        $activeDeptId = session('active_portal_dept_id');
        $isGlobalAdmin = clone $user;
        $isGlobalAdmin = $isGlobalAdmin->hasAnyRole(['Pastor', 'BTS_Admin', 'Super_Admin']) || $user->email === 'superadmin@httlthanhmyloi.com';

        if (!$activeDeptId) {
            $memberId = $user->member->id ?? null;
            
            if ($isGlobalAdmin) {
                $firstDepart = Department::where('block', 'activities')->first();
                if ($firstDepart) {
                    $activeDeptId = $firstDepart->id;
                    session(['active_portal_dept_id' => $activeDeptId]);
                }
            } else if ($memberId) {
                $firstMembership = OrgMembership::where('member_id', $memberId)
                                                ->where('model_type', Department::class)
                                                ->whereHasMorph('model', [Department::class], function ($query) {
                                                    $query->where('block', 'activities');
                                                })
                                                ->first();
                if ($firstMembership) {
                    $activeDeptId = $firstMembership->model_id;
                    session(['active_portal_dept_id' => $activeDeptId]);
                } else {
                    // No access to any department portal
                    abort(403, 'Bạn không thuộc Ban ngành nào để truy cập Portal.');
                }
            } else {
                abort(403, 'Bạn không thuộc Ban ngành nào.');
            }
        }

        return $next($request);
    }
}

