<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureFinanceContext
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            abort(403, 'Bạn không có quyền truy cập cổng Tài chính.');
        }

        $isGlobalAdmin = $user->isSuperAdmin();
        $validDeptIds = [];

        // Lấy danh sách các ban mà user được cấp quyền "finance"
        if (!$isGlobalAdmin) {
            $validDeptIds = \App\Models\UserDepartmentFeature::where('user_id', $user->id)
                ->where('is_enabled', true)
                ->whereHas('feature', fn ($q) => $q->where('slug', 'finance'))
                ->pluck('department_id')
                ->toArray();
                
            if (empty($validDeptIds)) {
                abort(403, 'Bạn không có quyền truy cập cổng Tài chính.');
            }
        }

        // Logic to determine and set active_finance_dept_id
        if (!session()->has('active_finance_dept_id') || (!$isGlobalAdmin && !in_array(session('active_finance_dept_id'), $validDeptIds))) {
            if ($isGlobalAdmin) {
                // Default to Church level (null) or first dept
                $firstDept = \App\Models\Department::first();
                session(['active_finance_dept_id' => $firstDept ? $firstDept->id : null]);
            } else {
                session(['active_finance_dept_id' => $validDeptIds[0]]);
            }
        }

        return $next($request);
    }
}
