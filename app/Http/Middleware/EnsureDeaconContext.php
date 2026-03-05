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

        $allowed = $user->hasRole(['Super_Admin', 'Pastor', 'Deacon', 'BTS_Admin'])
            || $user->hasPermissionTo('view_deacon');

        if (!$allowed) {
            abort(403, 'Bạn không có quyền truy cập cổng Chấp Sự.');
        }

        // Default active role if not set
        if (!session()->has('active_deacon_role')) {
            session(['active_deacon_role' => 'secretary']);
        }

        return $next($request);
    }
}
