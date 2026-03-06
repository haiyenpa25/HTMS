<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Department;
use App\Models\OrgMembership;
use Inertia\Inertia;

class DepartmentPortalController extends Controller
{
    const SUPER_ADMIN_EMAIL = 'superadmin@httlthanhmyloi.com';

    const DEFAULT_PERMISSIONS = [
        'manage_attendance' => true,
        'manage_visitation'  => true,
        'manage_members'     => true,
        'manage_assignments' => true,
        'manage_reports'     => true,
        'manage_funds'       => false,
    ];

    public function index(Request $request)
    {
        $user = $request->user();
        $isSuperAdmin = ($user->email === self::SUPER_ADMIN_EMAIL) || $user->hasRole('Super_Admin');

        $activeDeptId = session('active_portal_dept_id');

        // Available departments for switcher
        if ($isSuperAdmin) {
            $availableDepartments = Department::where('block', 'activities')
                ->select('id', 'name', 'code')->orderBy('name')->get();
        } else {
            $memberId = $user->member?->id;
            $availableDepartments = Department::where('block', 'activities')
                ->whereIn('id', OrgMembership::where('member_id', $memberId)
                    ->where('is_active', true)
                    ->where('model_type', Department::class)
                    ->pluck('model_id'))
                ->select('id', 'name', 'code')->orderBy('name')->get();
        }

        // If no active dept, pick first available
        if (!$activeDeptId && $availableDepartments->isNotEmpty()) {
            $activeDeptId = $availableDepartments->first()->id;
            session(['active_portal_dept_id' => $activeDeptId]);
        }

        $activeDepartment = $activeDeptId ? Department::find($activeDeptId) : null;

        // ── Resolve user permissions for the active department ──────────
        $userPermissions = self::DEFAULT_PERMISSIONS;

        if ($activeDeptId && !$isSuperAdmin) {
            $memberId = $user->member?->id;
            $membership = OrgMembership::where('member_id', $memberId)
                ->where('model_type', Department::class)
                ->where('model_id', $activeDeptId)
                ->where('is_active', true)
                ->first();

            if ($membership && !empty($membership->permissions)) {
                $userPermissions = array_merge(self::DEFAULT_PERMISSIONS, $membership->permissions);
            }
        }

        // ── Dashboard stats ─────────────────────────────────────────────
        $nextMeeting = null;
        $recentAttendance = null;

        if ($activeDeptId) {
            $nextMeeting = \App\Models\Meeting::where('department_id', $activeDeptId)
                ->where('date', '>=', now()->toDateString())
                ->orderBy('date')->first();

            $recentAttendance = \App\Models\Meeting::where('department_id', $activeDeptId)
                ->where('date', '<=', now()->toDateString())
                ->orderByDesc('date')->first();
        }

        return Inertia::render('Portal/Dashboard', [
            'activeDepartment'     => $activeDepartment,
            'availableDepartments' => $availableDepartments,
            'isGlobalAdmin'        => $isSuperAdmin,
            'userPermissions'      => $userPermissions,
            'nextMeeting'          => $nextMeeting,
            'recentAttendance'     => $recentAttendance,
        ]);
    }

    public function switchContext(Request $request)
    {
        $validated = $request->validate([
            'department_id' => 'required|integer|exists:departments,id'
        ]);

        $user = $request->user();
        $deptId = $validated['department_id'];
        $dept = Department::findOrFail($deptId);

        if ($dept->block !== 'activities') {
            abort(403, 'Ban ngành này không thuộc Cổng Sinh Hoạt.');
        }

        $isSuperAdmin = ($user->email === self::SUPER_ADMIN_EMAIL) || $user->hasRole('Super_Admin');

        if (!$isSuperAdmin) {
            $memberId = $user->member?->id;
            $ok = OrgMembership::where('member_id', $memberId)
                ->where('model_type', Department::class)
                ->where('model_id', $deptId)
                ->where('is_active', true)
                ->exists();
            if (!$ok) abort(403, 'Bạn không thuộc Ban Ngành này.');
        }

        session(['active_portal_dept_id' => $deptId]);
        return redirect()->route('portal.index');
    }
}
