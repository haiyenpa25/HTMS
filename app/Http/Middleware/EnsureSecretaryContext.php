<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Member;
use App\Models\OrgRole;
use App\Models\OrgMembership;

/**
 * P2 — Cổng Thư Ký Hội Thánh (Secretary Portal)
 * Cho phép: SuperAdmin + tín hữu có org_role = 'tkhu' (Thư Ký HT) hoặc 'ptk' (Phó Thư Ký)
 * trong Ban Chấp Sự (BCS, id=1).
 */
class EnsureSecretaryContext
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        if ($user->isSuperAdmin()) {
            \Inertia\Inertia::share('isGlobalAdmin', true);
            \Inertia\Inertia::share('portalType', 'secretary');
            return $next($request);
        }

        // Lấy member gắn với user này
        $member = Member::where('user_id', $user->id)->first();

        if (!$member) {
            return redirect()->route('login')->withErrors([
                'email' => 'Tài khoản chưa được gắn với hồ sơ tín hữu. Liên hệ quản trị viên.',
            ]);
        }

        // Kiểm tra org_role: tkhu (Thư Ký HT) hoặc ptk (Phó Thư Ký)
        $secretaryRoleCodes = ['tkhu', 'ptk'];
        $secretaryRoleIds = OrgRole::whereIn('code', $secretaryRoleCodes)->pluck('id');

        $isSecretary = OrgMembership::where('member_id', $member->id)
            ->whereIn('org_role_id', $secretaryRoleIds)
            ->where('is_active', true)
            ->exists();

        if (!$isSecretary) {
            return redirect()->route('login')->withErrors([
                'email' => 'Bạn không có quyền truy cập Cổng Thư Ký. Vui lòng liên hệ quản trị viên.',
            ]);
        }

        \Inertia\Inertia::share('isGlobalAdmin', false);
        \Inertia\Inertia::share('portalType', 'secretary');
        \Inertia\Inertia::share('currentMember', [
            'id'        => $member->id,
            'full_name' => $member->full_name,
        ]);

        return $next($request);
    }
}
