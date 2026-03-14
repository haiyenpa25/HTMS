<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Member;
use App\Models\OrgMembership;
use App\Models\UserDepartmentFeature;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;

class AuthController extends Controller
{
    /**
     * Display the login view.
     */
    public function login(): Response
    {
        return Inertia::render('Auth/Login', [
            'status' => session('status'),
            'systemDomain' => env('SYSTEM_DOMAIN', 'httlthanhmyloi.com'),
        ]);
    }

    /**
     * Handle an incoming authentication request.
     */
    public function authenticate(Request $request): RedirectResponse
    {
        $inputEmail = $request->input('email');
        $domain = $request->input('domain', env('SYSTEM_DOMAIN', 'httlthanhmyloi.com'));

        // If user only entered username (no @), append domain
        if ($inputEmail && !str_contains($inputEmail, '@')) {
            $request->merge(['email' => $inputEmail . '@' . $domain]);
        }

        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            $user = Auth::user();
            $member = Member::where('user_id', $user->id)->first();
            
            // 1. Super Admin / Pastor → admin dashboard
            if ($user->isSuperAdmin()) {
                return redirect()->intended(route('dashboard'));
            }
            
            // 2. Identify Primary Department & Block
            // Priority: leadership > ministry > activities
            $primaryDept = null;
            if ($member) {
                $primaryDept = $member->departments()
                    ->orderByRaw("CASE WHEN block = 'leadership' THEN 1 WHEN block = 'ministry' THEN 2 ELSE 3 END")
                    ->first();
            }

            // Fallback to MAC permissions if no direct membership
            if (!$primaryDept) {
                $featureDept = UserDepartmentFeature::where('user_id', $user->id)
                    ->where('is_enabled', true)
                    ->with('department')
                    ->get()
                    ->sortBy(fn($uf) => match($uf->department->block ?? '') {
                        'leadership' => 1,
                        'ministry' => 2,
                        default => 3
                    })
                    ->first();
                $primaryDept = $featureDept->department ?? null;
            }

            if ($primaryDept) {
                switch ($primaryDept->block) {
                    case 'leadership':
                        session(['active_deacon_dept_id' => $primaryDept->id]);
                        return redirect()->intended(route('deacon.index'));
                    case 'ministry':
                        session(['active_ministry_dept_id' => $primaryDept->id]);
                        return redirect()->intended(route('ministry.index'));
                    case 'activities':
                        session(['active_portal_dept_id' => $primaryDept->id]);
                        return redirect()->intended(route('portal.index'));
                }
            }
            
            // 3. Fallback: deacon portal for BTS_Admin roles even without dept
            if ($user->isSuperAdmin()) {
                return redirect()->intended(route('deacon.index'));
            }
            
            // 4. Default fallback: Member Portal
            return redirect()->route('member.portal.index');
        }

        return back()->withErrors([
            'email' => 'Thông tin đăng nhập không chính xác.',
        ])->onlyInput('email');
    }

    /**
     * Destroy an authenticated session.
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/login');
    }
}

