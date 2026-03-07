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

        $activeDepartment = $activeDeptId ? Department::find($activeDeptId) : null;

        // ── Lấy feature permissions (Giao lộ Level 1 & Level 2) ────────
        $allSlugs = ['attendance', 'visitation', 'members', 'assignments', 'reports', 'finance'];

        if ($isSuperAdmin) {
            // Super Admin bypass tất cả, thấy đủ 100% features hiện có
            $userPermissions = collect($allSlugs)->mapWithKeys(fn ($s) => [$s => true])->toArray();
        } else {
            if ($activeDepartment) {
                $level1Map = $service->getAvailableFeaturesForDepartment($activeDepartment);
                
                $enabledFeaturesLevel2 = UserDepartmentFeature::where('user_id', $user->id)
                    ->where('department_id', $activeDeptId)
                    ->where('is_enabled', true)
                    ->with('feature')
                    ->get()
                    ->pluck('feature.slug')
                    ->filter()
                    ->values();

                $userPermissions = collect($allSlugs)->mapWithKeys(function ($s) use ($level1Map, $enabledFeaturesLevel2) {
                    // Level 1: If no config exists for this feature, default to ALLOW (backward compat)
                    $l1 = $level1Map[$s] ?? true;
                    $l2 = $enabledFeaturesLevel2->contains($s);
                    return [$s => ($l1 && $l2)];
                })->toArray();
            } else {
                $userPermissions = collect($allSlugs)->mapWithKeys(fn ($s) => [$s => false])->toArray();
            }
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

        if (!$user->isSuperAdmin()) {
            $service = app(FeatureAssignmentService::class);
            $level1Map = $service->getAvailableFeaturesForDepartment($dept);
            
            $userFeatures = UserDepartmentFeature::where('user_id', $user->id)
                ->where('department_id', $deptId)
                ->where('is_enabled', true)
                ->with('feature')
                ->get();
                
            $hasValidFeature = false;
            foreach ($userFeatures as $uf) {
                if (!$uf->feature) continue;
                $slug = $uf->feature->slug;
                // Default allow if no Level 1 config exists for this feature
                if ($level1Map[$slug] ?? true) {
                    $hasValidFeature = true;
                    break;
                }
            }
            
            if (!$hasValidFeature) {
                abort(403, 'Ban ngành này hiện không có tính năng nào được phép truy cập cho bạn.');
            }
        }

        session(['active_portal_dept_id' => $deptId]);
        return redirect()->route('portal.index');
    }
}
