<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Department;
use App\Models\OrgMembership;

class EnsureMinistryContext
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

        $activeDeptId = session('active_ministry_dept_id');
        $isGlobalAdmin = $user->hasRole(['Pastor', 'BTS_Admin', 'Super_Admin']);

        if (!$activeDeptId) {
            $memberId = $user->member_id;
            
            if ($isGlobalAdmin) {
                $firstDepart = Department::where('block', 'ministry')->first();
                if ($firstDepart) {
                    $activeDeptId = $firstDepart->id;
                    session(['active_ministry_dept_id' => $activeDeptId]);
                }
            } else if ($memberId) {
                $firstMembership = OrgMembership::where('member_id', $memberId)
                                                ->where('model_type', Department::class)
                                                ->whereHas('department', function ($query) {
                                                    $query->where('block', 'ministry');
                                                })
                                                ->first();
                if ($firstMembership) {
                    $activeDeptId = $firstMembership->model_id;
                    session(['active_ministry_dept_id' => $activeDeptId]);
                } else {
                    // No access to any ministry department
                    abort(403, 'Bạn không thuộc Ban mục vụ nào để truy cập.');
                }
            } else {
                abort(403, 'Bạn không thuộc Ban mục vụ nào.');
            }
        }

        return $next($request);
    }
}

