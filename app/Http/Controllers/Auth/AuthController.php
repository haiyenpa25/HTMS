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
        ]);
    }

    /**
     * Handle an incoming authentication request.
     */
    public function authenticate(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            $user = Auth::user();
            
            // Super Admin / Pastor → admin dashboard
            if ($user->hasRole(['Pastor', 'Super_Admin'])) {
                return redirect()->intended(route('dashboard'));
            }
            
            // Deacon role → deacon portal
            if ($user->hasRole(['Deacon', 'BTS_Admin'])) {
                return redirect()->intended(route('deacon.index'));
            }
            
            // Church membership (Thư ký HT, Thủ Quỹ HT) → deacon/leadership portal
            // Những người có OrgMembership model_type=Church (không phải Department) là lãnh đạo HT
            $member = Member::where('user_id', $user->id)->first();
            $hasChurchRole = $member && OrgMembership::where('member_id', $member->id)
                ->where('model_type', \App\Models\Church::class)
                ->exists();
            if ($hasChurchRole) {
                return redirect()->intended(route('deacon.index'));
            }
            
            // Check if user has any ministry memberships (OrgMembership in ministry block)
            $hasMinistry = false;
            if ($member) {
                $hasMinistry = OrgMembership::where('member_id', $member->id)
                    ->where('model_type', Department::class)
                    ->whereHasMorph('model', [Department::class], fn($q) => $q->where('block', 'ministry'))
                    ->exists();
            }
            // Also check MAC UserDepartmentFeature
            if (!$hasMinistry) {
                $hasMinistry = UserDepartmentFeature::where('user_id', $user->id)
                    ->where('is_enabled', true)
                    ->whereHas('department', fn($q) => $q->where('block', 'ministry'))
                    ->exists();
            }
            
            if ($hasMinistry) {
                return redirect()->intended(route('ministry.index'));
            }
            
            // Check activities (default portal)
            $hasActivities = false;
            if ($member) {
                $hasActivities = $member->departments()
                    ->where('block', 'activities')
                    ->exists();
            }
            if (!$hasActivities) {
                $hasActivities = UserDepartmentFeature::where('user_id', $user->id)
                    ->where('is_enabled', true)
                    ->whereHas('department', fn($q) => $q->where('block', 'activities'))
                    ->exists();
            }
            
            if ($hasActivities) {
                return redirect()->intended(route('portal.index'));
            }
            
            // Fallback: send to dashboard with a message (no portal access)
            return redirect()->route('dashboard');
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

