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
        Gate::authorize('access_department_portal');

        $user = $request->user();
        $activeDeptId = session('active_ministry_dept_id');
        $isGlobalAdmin = $user->hasRole(['Pastor', 'Super_Admin']);

        if (!$activeDeptId) {
            $memberId = $user->member_id;
            
            if ($isGlobalAdmin) {
                $firstDepart = Department::where('block', 'ministry')->first();
                if ($firstDepart) {
                    $activeDeptId = $firstDepart->id;
                    session(['active_ministry_dept_id' => $activeDeptId]);
                }
            } else if ($memberId) {
                $firstMembership = OrgMembership::where('member_id', $memberId)
                                                ->where('model_type', Department::class)
                                                ->whereHas('department', function ($query) {
                                                    $query->where('block', 'ministry');
                                                })
                                                ->first();
                if ($firstMembership) {
                    $activeDeptId = $firstMembership->model_id;
                    session(['active_ministry_dept_id' => $activeDeptId]);
                }
            }
        }

        // Available departments for the user to switch between (ONLY 'ministry' block)
        if ($isGlobalAdmin) {
            $availableDepartments = Department::where('block', 'ministry')->select('id', 'name', 'code')->orderBy('name')->get();
        } else {
             $availableDepartments = Department::where('block', 'ministry')->whereIn('id', function($query) use ($user) {
                 $query->select('model_id')
                       ->from('org_memberships')
                       ->where('model_type', Department::class)
                       ->where('member_id', $user->member_id);
             })->select('id', 'name', 'code')->orderBy('name')->get();
        }

        $activeDepartment = null;
        if ($activeDeptId) {
            $activeDepartment = Department::find($activeDeptId);
        }

        return Inertia::render('Ministry/Dashboard', [
            'activeDepartment' => $activeDepartment,
            'availableDepartments' => $availableDepartments,
            'isGlobalAdmin' => $isGlobalAdmin,
        ]);
    }

    /**
     * Switch context method via POST Request.
     */
    public function switchContext(Request $request)
    {
        Gate::authorize('access_department_portal');

        $validated = $request->validate([
            'department_id' => 'required|exists:departments,id'
        ]);

        $user = $request->user();
        $deptId = $validated['department_id'];

        $dept = Department::findOrFail($deptId);
        if ($dept->block !== 'ministry') {
            abort(403, 'Ban ngành này không thuộc hệ thống Cổng Mục vụ.');
        }

        // If not global admin, verify they belong to this department
        if (!$user->hasRole(['Pastor', 'Super_Admin'])) {
            $belongs = tap(OrgMembership::where('member_id', $user->member_id)
                         ->where('model_type', Department::class)
                         ->where('model_id', $deptId)
                         ->exists(), function($exists) {
                             if(!$exists) abort(403, 'Bạn không có quyền truy cập ban mục vụ này.');
                         });
        }

        session(['active_ministry_dept_id' => $deptId]);

        return redirect()->route('ministry.index');
    }
}

