<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Models\Department;
use App\Models\OrgMembership;
use Inertia\Inertia;

class DepartmentPortalController extends Controller
{
    /**
     * Entry point for the Department Portal Menu.
     */
    public function index(Request $request)
    {
        Gate::authorize('access_department_portal');

        $user = $request->user();
        
        // 1. Check if there's an active context array in session
        $activeDeptId = session('active_portal_dept_id');

        // Allow Pastor / Super Admin to see all, others filter by memberships
        $isGlobalAdmin = $user->hasRole(['Pastor', 'Super_Admin']);

        if (!$activeDeptId) {
            // Find the first department this user belongs to that is an 'activities' block
            $memberId = $user->member_id;
            
            if ($isGlobalAdmin) {
                $firstDepart = Department::where('block', 'activities')->first();
                if ($firstDepart) {
                    $activeDeptId = $firstDepart->id;
                    session(['active_portal_dept_id' => $activeDeptId]);
                }
            } else if ($memberId) {
                $firstMembership = OrgMembership::where('member_id', $memberId)
                                                ->where('model_type', Department::class)
                                                ->whereHas('department', function ($query) {
                                                    $query->where('block', 'activities');
                                                })
                                                ->first();
                if ($firstMembership) {
                    $activeDeptId = $firstMembership->model_id;
                    session(['active_portal_dept_id' => $activeDeptId]);
                }
            }
        }

        // Available departments for the user to switch between (ONLY 'activities' block)
        if ($isGlobalAdmin) {
            $availableDepartments = Department::where('block', 'activities')->select('id', 'name')->orderBy('name')->get();
        } else {
             $availableDepartments = Department::where('block', 'activities')->whereIn('id', function($query) use ($user) {
                 $query->select('model_id')
                       ->from('org_memberships')
                       ->where('model_type', Department::class)
                       ->where('member_id', $user->member_id);
             })->select('id', 'name')->orderBy('name')->get();
        }

        // Fetch current active department data and dashboard widgets
        $activeDepartment = null;
        $nextMeeting = null;
        $recentAttendance = null;
        $financeBalance = 0;

        if ($activeDeptId) {
            $activeDepartment = Department::find($activeDeptId);
            
            // 1. Next Meeting (Upcoming)
            $nextMeeting = \App\Models\Meeting::where('department_id', $activeDeptId)
                                              ->where('date', '>=', now()->toDateString())
                                              ->orderBy('date', 'asc')
                                              ->orderBy('time', 'asc')
                                              ->first();

            // 2. Recent Attendance Status (Last meeting that already happened)
            $recentAttendance = \App\Models\Meeting::where('department_id', $activeDeptId)
                                                   ->where('date', '<=', now()->toDateString())
                                                   ->orderBy('date', 'desc')
                                                   ->orderBy('time', 'desc')
                                                   ->first();
            
            // 3. Finance (TBD when models fully fleshed out, using placeholders for now)
            // $financeBalance = DepartmentFinance::where('department_id', $activeDeptId)->sum('amount');
        }

        return Inertia::render('Portal/Dashboard', [
            'activeDepartment' => $activeDepartment,
            'availableDepartments' => $availableDepartments,
            'isGlobalAdmin' => $isGlobalAdmin,
            'nextMeeting' => $nextMeeting,
            'recentAttendance' => $recentAttendance,
            'financeBalance' => $financeBalance,
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
        if ($dept->block !== 'activities') {
            abort(403, 'Ban ngành này không thuộc hệ thống Cổng Sinh Hoạt.');
        }

        // If not global admin, verify they belong to this department
        if (!$user->hasRole(['Pastor', 'Super_Admin'])) {
            $belongs = tap(OrgMembership::where('member_id', $user->member_id)
                         ->where('model_type', Department::class)
                         ->where('model_id', $deptId)
                         ->exists(), function($exists) {
                             if(!$exists) abort(403, 'Bạn không có quyền truy cập ban ngành này.');
                         });
        }

        session(['active_portal_dept_id' => $deptId]);

        return redirect()->route('portal.index');
    }
}

