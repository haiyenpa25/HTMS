<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class EnsureSuperAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 1. Must be logged in
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        // 2. Must have the Super_Admin or Pastor role
        $user = Auth::user();
        
        if (!$user->hasRole(['Super_Admin', 'Pastor']) && $user->email !== 'superadmin@httlthanhmyloi.com') {
            // Optional: Log unauthorized access attempt
            abort(403, 'Bạn không có quyền truy cập trang quản trị hệ thống cấp cao.');
        }

        return $next($request);
    }
}
