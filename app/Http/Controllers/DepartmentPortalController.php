<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Feature;
use App\Models\UserDepartmentFeature;
use App\Services\FeatureAssignmentService;
use Illuminate\Http\Request;
use Inertia\Inertia;

/**
 * DepartmentPortalController — Two-Tier MAC version.
 * Level 1: System-wide feature_department mapping (FeatureAssignmentService)
 * Level 2: User-specific user_department_features mapping
 * SuperAdmin overrides everything.
 */
class DepartmentPortalController extends Controller
{
    public function index(Request $request)
    {
        $user         = $request->user();
        $isSuperAdmin = $user->isSuperAdmin();
        $service      = app(FeatureAssignmentService::class);

        $activeDeptId = session('active_portal_dept_id');

        // ── Lấy danh sách departments user có quyền vào ────────────────
        if ($isSuperAdmin) {
            $availableDepartments = Department::where('block', 'activities')
                ->select('id', 'name', 'code')->orderBy('name')->get();
        } else {
            // Lấy tất cả quyền Level 2 của user này
            $userFeatureRecords = UserDepartmentFeature::where('user_id', $user->id)
                ->where('is_enabled', true)
                ->whereHas('department', fn ($q) => $q->where('block', 'activities'))
                ->with(['feature', 'department'])
                ->get()
                ->groupBy('department_id');

            $validDeptIds = [];
            
            foreach ($userFeatureRecords as $deptId => $ufRecords) {
                $dept = $ufRecords->first()->department;
                if (!$dept) continue;
                
                $level1Map = $service->getAvailableFeaturesForDepartment($dept);
                
                $hasValidFeature = false;
                foreach ($ufRecords as $uf) {
                    if (!$uf->feature) continue;
                    $slug = $uf->feature->slug;
                    // default-allow: if no Level 1 config exists for this slug, assume allowed
                    if ($level1Map[$slug] ?? true) {
                        $hasValidFeature = true;
                        break;
                    }
                }
                
                if ($hasValidFeature) {
                    $validDeptIds[] = $deptId;
                }
            }

            $availableDepartments = Department::whereIn('id', $validDeptIds)
                ->where('block', 'activities')
                ->select('id', 'name', 'code')->orderBy('name')->get();
        }

        // Auto-set active dept
        if (!$activeDeptId && $availableDepartments->isNotEmpty()) {
            $activeDeptId = $availableDepartments->first()->id;
            session(['active_portal_dept_id' => $activeDeptId]);
        }

        $activeDepartment = $request->attributes->get('activeDepartment') ?? Department::find($activeDeptId);

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

        if (!$user->isSuperAdmin()) {
            // Check if user has membership OR feature access in this target department
            $isMember = \App\Models\Member::where('user_id', $user->id)
                ->whereHas('departments', fn($q) => $q->where('departments.id', $deptId))
                ->exists();

            $hasFeatures = false;
            if (!$isMember) {
                $service = app(FeatureAssignmentService::class);
                $level1Map = $service->getAvailableFeaturesForDepartment($dept);
                
                $userFeatures = UserDepartmentFeature::where('user_id', $user->id)
                    ->where('department_id', $deptId)
                    ->where('is_enabled', true)
                    ->with('feature')
                    ->get();
                    
                foreach ($userFeatures as $uf) {
                    if (!$uf->feature) continue;
                    $slug = $uf->feature->slug;
                    // Default allow if no Level 1 config exists
                    if ($level1Map[$slug] ?? true) {
                        $hasFeatures = true;
                        break;
                    }
                }
            }
            
            if (!$isMember && !$hasFeatures) {
                abort(403, 'Bạn không có quyền truy cập ban ngành này.');
            }
        }

        // Logic switch context và redirect
        if ($dept->block === 'activities') {
            session(['active_portal_dept_id' => $deptId]);
            return redirect()->route('portal.index');
        } elseif ($dept->block === 'ministry') {
            session(['active_ministry_dept_id' => $deptId]);
            return redirect()->route('ministry.index');
        } elseif ($dept->block === 'leadership') {
            session(['active_deacon_dept_id' => $deptId]);
            return redirect()->route('deacon.index');
        }

        return redirect()->route('dashboard');
    }
}
