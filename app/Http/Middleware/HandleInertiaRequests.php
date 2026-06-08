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
            // Cache homePortal in session to avoid 6-7 queries per request
            $homePortal = $request->session()->get('cached_home_portal');
            if (!$homePortal) {
                $homePortal = $this->resolveHomePortal($user);
                $request->session()->put('cached_home_portal', $homePortal);
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
            'pending_approvals_count' => $user ? \App\Models\ApprovalRequest::where('status', 'pending')
                ->when(!$user->isSuperAdmin() && $activeDeptId, fn ($q) => $q->where('department_id', $activeDeptId))
                ->count() : 0,
            'pending_reports_count'   => $user ? (
                \App\Models\DepartmentReport::where('status', 'submitted')
                    ->when(!$user->isSuperAdmin() && $activeDeptId, fn($q) => $q->where('department_id', $activeDeptId))
                    ->count() +
                \App\Models\EduReport::where('status', 'submitted')
                    ->when(!$user->isSuperAdmin() && $activeDeptId, fn($q) => $q->where('department_id', $activeDeptId))
                    ->count()
            ) : 0,
            'allAvailableDepartments' => $user ? app(\App\Services\PortalService::class)->getAllAvailableDepartmentsGrouped($user) : [],
            'flash' => [
                'message' => fn () => $request->session()->get('message'),
                'success' => fn () => $request->session()->get('success'),
                'error'   => fn () => $request->session()->get('error'),
            ],
        ];
    }

    /**
     * Resolve home portal for user (called once, cached in session).
     * Clears cache on role/dept change by calling session()->forget('cached_home_portal').
     */
    private function resolveHomePortal(\App\Models\User $user): string
    {
        if ($user->isSuperAdmin()) {
            return '/dashboard';
        }

        if ($user->hasRole('Deacon') || $user->hasRole('Secretary')) {
            return '/deacon';
        }

        $member = \App\Models\Member::where('user_id', $user->id)->value('id');

        // Check Ministry
        $hasMinistry = false;
        if ($member) {
            $ministryDeptIds = \App\Models\Department::where('block', 'ministry')->pluck('id');
            $hasMinistry = \App\Models\OrgMembership::where('member_id', $member)
                ->where('model_type', \App\Models\Department::class)
                ->whereIn('model_id', $ministryDeptIds)
                ->exists();
        }
        if (!$hasMinistry) {
            $hasMinistry = \App\Models\UserDepartmentFeature::where('user_id', $user->id)
                ->where('is_enabled', true)
                ->whereHas('department', fn ($q) => $q->where('block', 'ministry'))
                ->exists();
        }
        if ($hasMinistry) return '/ministry';

        // Check Activities
        $hasActivities = false;
        if ($member) {
            $activitiesDeptIds = \App\Models\Department::where('block', 'activities')->pluck('id');
            $hasActivities = \App\Models\OrgMembership::where('member_id', $member)
                ->where('model_type', \App\Models\Department::class)
                ->whereIn('model_id', $activitiesDeptIds)
                ->exists();
        }
        if (!$hasActivities) {
            $hasActivities = \App\Models\UserDepartmentFeature::where('user_id', $user->id)
                ->where('is_enabled', true)
                ->whereHas('department', fn ($q) => $q->where('block', 'activities'))
                ->exists();
        }
        if ($hasActivities) return '/portal';

        return '/member';
    }
}
