<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Feature;
use App\Models\UserDepartmentFeature;
use Illuminate\Http\Request;
use Inertia\Inertia;

/**
 * DepartmentPortalController — MAC version.
 * Không dùng OrgMembership. Dùng user_department_features trực tiếp.
 */
class DepartmentPortalController extends Controller
{
    public function index(Request $request)
    {
        $user         = $request->user();
        $isSuperAdmin = $user->isSuperAdmin();

        $activeDeptId = session('active_portal_dept_id');

        // ── Lấy danh sách departments user có quyền vào ────────────────
        if ($isSuperAdmin) {
            $availableDepartments = Department::where('block', 'activities')
                ->select('id', 'name', 'code')->orderBy('name')->get();
        } else {
            $allowedIds = UserDepartmentFeature::where('user_id', $user->id)
                ->where('is_enabled', true)
                ->whereHas('department', fn ($q) => $q->where('block', 'activities'))
                ->pluck('department_id')
                ->unique();

            $availableDepartments = Department::whereIn('id', $allowedIds)
                ->where('block', 'activities')
                ->select('id', 'name', 'code')->orderBy('name')->get();
        }

        // Auto-set active dept
        if (!$activeDeptId && $availableDepartments->isNotEmpty()) {
            $activeDeptId = $availableDepartments->first()->id;
            session(['active_portal_dept_id' => $activeDeptId]);
        }

        $activeDepartment = $activeDeptId ? Department::find($activeDeptId) : null;

        // ── Lấy feature permissions từ user_department_features ────────
        if ($isSuperAdmin) {
            // Super Admin có tất cả features
            $userPermissions = Feature::pluck('slug')->mapWithKeys(fn ($s) => [$s => true])->toArray();
        } else {
            $enabledFeatures = UserDepartmentFeature::where('user_id', $user->id)
                ->where('department_id', $activeDeptId)
                ->where('is_enabled', true)
                ->with('feature')
                ->get()
                ->pluck('feature.slug')
                ->filter()
                ->values();

            // Map tới dạng boolean cho frontend
            $allSlugs = ['attendance', 'visitation', 'members', 'assignments', 'reports', 'finance'];
            $userPermissions = collect($allSlugs)->mapWithKeys(fn ($s) => [$s => $enabledFeatures->contains($s)])->toArray();
        }

        // ── Dashboard stats ─────────────────────────────────────────────
        $nextMeeting     = null;
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

        $user   = $request->user();
        $deptId = $validated['department_id'];
        $dept   = Department::findOrFail($deptId);

        if ($dept->block !== 'activities') {
            abort(403, 'Ban ngành này không thuộc Cổng Sinh Hoạt.');
        }

        // MAC check: user phải có ít nhất 1 feature enabled trong dept này (hoặc superadmin)
        if (!$user->isSuperAdmin()) {
            $ok = UserDepartmentFeature::where('user_id', $user->id)
                ->where('department_id', $deptId)
                ->where('is_enabled', true)
                ->exists();
            if (!$ok) {
                abort(403, 'Bạn chưa được cấp quyền trong ban ngành này.');
            }
        }

        session(['active_portal_dept_id' => $deptId]);
        return redirect()->route('portal.index');
    }
}
