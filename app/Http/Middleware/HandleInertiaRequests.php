<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();
        
        $homePortal = '/dashboard'; // default
        if ($user) {
            if ($user->hasRole(['Super_Admin', 'Pastor'])) {
                $homePortal = '/dashboard';
            } elseif ($user->hasRole(['Deacon', 'BTS_Admin'])) {
                $homePortal = '/deacon';
            } else {
                // Check Ministry
                $hasMinistry = false;
                $member = \App\Models\Member::where('user_id', $user->id)->first();
                if ($member) {
                    $hasMinistry = \App\Models\OrgMembership::where('member_id', $member->id)
                        ->where('model_type', \App\Models\Department::class)
                        ->whereHas('model', fn($q) => $q->where('block', 'ministry'))
                        ->exists();
                }
                if (!$hasMinistry) {
                    $hasMinistry = \App\Models\UserDepartmentFeature::where('user_id', $user->id)
                        ->where('is_enabled', true)
                        ->whereHas('department', fn($q) => $q->where('block', 'ministry'))
                        ->exists();
                }
                
                if ($hasMinistry) {
                    $homePortal = '/ministry';
                } else {
                    // Check Activities
                    $hasActivities = false;
                    if ($member) {
                        $hasActivities = $member->departments()
                            ->where('block', 'activities')
                            ->exists();
                    }
                    if (!$hasActivities) {
                        $hasActivities = \App\Models\UserDepartmentFeature::where('user_id', $user->id)
                            ->where('is_enabled', true)
                            ->whereHas('department', fn($q) => $q->where('block', 'activities'))
                            ->exists();
                    }
                    if ($hasActivities) {
                        $homePortal = '/portal';
                    } else {
                        // Tín hữu bình thường không thuộc ban nào -> Portal Tín Hữu
                        $homePortal = '/member';
                    }
                }
            }
        }
        
        return [
            ...parent::share($request),
            'auth' => [
                'user' => $user ? [
                    'id' => $user->id,
                    'name' => (isset($member) && $member) ? $member->full_name : $user->name,
                    'email' => $user->email,
                    'role' => $user->getRoleNames()->first() ?? 'Guest',
                    'member_code' => (isset($member) && $member) ? $member->member_code : null,
                    'permissions' => $user->getAllPermissions()->pluck('name'),
                    'home_portal' => $homePortal,
                    'unread_notifications' => $user->unreadNotifications()->limit(10)->get(),
                    'unread_notifications_count' => $user->unreadNotifications()->count(),
                ] : null,
            ],
            'pending_approvals_count' => $user ? \App\Models\ApprovalRequest::where('status', 'pending')->count() : 0,
            'pending_reports_count'   => $user ? (
                \App\Models\DepartmentReport::where('status', 'submitted')->count() +
                \App\Models\EduReport::where('status', 'submitted')->count()
            ) : 0,
            'allAvailableDepartments' => $user ? app(\App\Services\PortalService::class)->getAllAvailableDepartmentsGrouped($user) : [],
            'flash' => [
                'message' => fn () => $request->session()->get('message'),
                'success' => fn () => $request->session()->get('success'),
                'error'   => fn () => $request->session()->get('error'),
            ],
        ];
    }
}
