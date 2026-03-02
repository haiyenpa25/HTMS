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

        if (!$user || !$user->hasPermissionTo('view_finance')) {
            abort(403, 'Bạn không có quyền truy cập cổng Tài chính.');
        }

        // Logic to determine and set active_finance_dept_id
        if (!session()->has('active_finance_dept_id')) {
            if ($user->hasRole(['Super_Admin', 'Pastor'])) {
                // Default to a specific department or null (Church)
                $firstDept = \App\Models\Department::first();
                session(['active_finance_dept_id' => $firstDept ? $firstDept->id : null]);
            } else {
                // Find all departments the user is a member of
                $deptIds = $user->memberships()->where('model_type', \App\Models\Department::class)->pluck('model_id');

                if ($deptIds->isEmpty()) {
                    abort(403, 'Bạn không thuộc ban ngành nào để quản lý tài chính.');
                }
                
                session(['active_finance_dept_id' => $deptIds->first()]);
            }
        }

        return $next($request);
    }
}
