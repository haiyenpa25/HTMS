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
            if ($user->isSuperAdmin()) {
                $homePortal = '/dashboard';
            } elseif ($user->isSuperAdmin()) {
                $homePortal = '/deacon';
            } else {
                // Check Ministry
                $hasMinistry = false;
                $member = \App\Models\Member::where('user_id', $user->id)->first();
                if ($member) {
                    $ministryDeptIds = \App\Models\Department::where('block', 'ministry')->pluck('id');
                    $hasMinistry = \App\Models\OrgMembership::where('member_id', $member->id)
                        ->where('model_type', \App\Models\Department::class)
                        ->whereIn('model_id', $ministryDeptIds)
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
                        $activitiesDeptIds = \App\Models\Department::where('block', 'activities')->pluck('id');
                        $hasActivities = \App\Models\OrgMembership::where('member_id', $member->id)
                            ->where('model_type', \App\Models\Department::class)
                            ->whereIn('model_id', $activitiesDeptIds)
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
        
        // Dynamically figure out which department session to check based on the URL path
        $activeDeptId = null;
        if ($request->is('ministry*')) {
            $activeDeptId = $request->session()->get('active_ministry_dept_id');
        } elseif ($request->is('portal*')) {
            $activeDeptId = $request->session()->get('active_portal_dept_id');
        } elseif ($request->is('deacon*')) {
            $activeDeptId = $request->session()->get('active_deacon_dept_id');
        } else {
            // Fallback for generic calls
            $activeDeptId = $request->session()->get('active_portal_dept_id') 
                         ?? $request->session()->get('active_ministry_dept_id') 
                         ?? $request->session()->get('active_deacon_dept_id');
        }

        $allowedFeatures = [];
        if ($user && $activeDeptId) {
            $portalService = app(\App\Services\PortalService::class);
            $allowedFeatures = $portalService->getAllowedFeaturesForDept($user, $activeDeptId);
        }

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $user ? [
                    'id' => $user->id,
                    'name' => (isset($member) && $member) ? $member->full_name : $user->name,
                    'email' => $user->email,
                    'role' => $user->getRoleNames()->first() ?? 'Guest',
                    'is_superadmin' => $user->isSuperAdmin(),
                    'member_code' => (isset($member) && $member) ? $member->member_code : null,
                    'permissions' => $user->getAllPermissions()->pluck('name'),
                    'home_portal' => $homePortal,
                    'unread_notifications' => $user->unreadNotifications()->limit(10)->get(),
                    'unread_notifications_count' => $user->unreadNotifications()->count(),
                ] : null,
                'allowed_features' => $allowedFeatures,
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
