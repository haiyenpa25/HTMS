<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Models\Department;
use App\Models\OrgMembership;
use Inertia\Inertia;

class MinistryPortalController extends Controller
{
    /**
     * Entry point for the Ministry Portal.
     */
    public function index(Request $request)
    {
        $user         = $request->user();
        $isSuperAdmin = $user->isSuperAdmin();
        $service      = app(\App\Services\FeatureAssignmentService::class);

        $activeDeptId = session('active_ministry_dept_id');

        // ── Lấy danh sách departments user có quyền vào ────────────────
        if ($isSuperAdmin) {
            $availableDepartments = Department::where('block', 'ministry')
                ->select('id', 'name', 'code')->orderBy('name')->get();
        } else {
            // Lấy tất cả quyền Level 2 của user này
            $userFeatureRecords = \App\Models\UserDepartmentFeature::where('user_id', $user->id)
                ->where('is_enabled', true)
                ->whereHas('department', fn ($q) => $q->where('block', 'ministry'))
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
                ->where('block', 'ministry')
                ->select('id', 'name', 'code')->orderBy('name')->get();
        }

        // Auto-set active dept
        if (!$activeDeptId && $availableDepartments->isNotEmpty()) {
            $activeDeptId = $availableDepartments->first()->id;
            session(['active_ministry_dept_id' => $activeDeptId]);
        }

        $activeDepartment = $request->attributes->get('activeDepartment') ?? Department::find($activeDeptId);

        return Inertia::render('Ministry/Dashboard', [
            'activeDepartment'     => $activeDepartment,
            'availableDepartments' => $availableDepartments,
            'isGlobalAdmin'        => $isSuperAdmin,
        ]);
    }

    /**
     * Switch context method via POST Request.
     */
    public function switchContext(Request $request)
    {
        $validated = $request->validate([
            'department_id' => 'required|exists:departments,id'
        ]);

        $user = $request->user();
        $deptId = $validated['department_id'];

        $dept = Department::findOrFail($deptId);
        if ($dept->block !== 'ministry') {
            abort(403, 'Ban ngành này không thuộc hệ thống Cổng Mục vụ.');
        }

        if (!$user->isSuperAdmin()) {
            $service = app(\App\Services\FeatureAssignmentService::class);
            $level1Map = $service->getAvailableFeaturesForDepartment($dept);
            
            $userFeatures = \App\Models\UserDepartmentFeature::where('user_id', $user->id)
                ->where('department_id', $deptId)
                ->where('is_enabled', true)
                ->with('feature')
                ->get();
                
            $hasValidFeature = false;
            foreach ($userFeatures as $uf) {
                if (!$uf->feature) continue;
                $slug = $uf->feature->slug;
                if ($level1Map[$slug] ?? true) {
                    $hasValidFeature = true;
                    break;
                }
            }
            
            if (!$hasValidFeature) {
                abort(403, 'Bạn không có quyền truy cập tính năng nào trong ban mục vụ này.');
            }
        }

        session(['active_ministry_dept_id' => $deptId]);

        return redirect()->route('ministry.index');
    }
}

